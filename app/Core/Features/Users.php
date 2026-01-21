<?php

namespace App\Core\Features;

use App\Models\User;
use App\Models\Server;
use App\Models\AuditLog;

class Users
{
    public static function canViewProfile($viewer, $profileUser)
    {
        if (!$profileUser) {
            return ['allowed' => false, 'error' => lang('user_not_found')];
        }

        // Private profiles are only visible to the profile owner
        if ($profileUser->private) {
            if (!$viewer || $viewer->id != $profileUser->id) {
                return ['allowed' => false, 'error' => lang('profile_private')];
            }
        }

        return ['allowed' => true];
    }

    public static function validateProfileData($data, $currentUser)
    {
        $errors = [];

        if (strlen($data['name'] ?? '') < 2 || strlen($data['name'] ?? '') > 32) {
            $errors[] = lang('name_length');
        }

        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors[] = lang('invalid_email');
        }

        $existingUser = User::findByEmail($data['email'] ?? '');
        if ($existingUser && $existingUser->id != $currentUser->id) {
            $errors[] = lang('email_used');
        }

        if (strlen($data['about'] ?? '') > 128) {
            $errors[] = lang('about_too_big');
        }

        return $errors;
    }

    public static function validatePasswordChange($oldPassword, $newPassword, $confirmPassword, $user)
    {
        if (!Auth::attempt($user->username, $oldPassword)) {
            return ['success' => false, 'error' => lang('incorrect_old_password')];
        }

        if (strlen($newPassword) < 6) {
            return ['success' => false, 'error' => lang('password_too_short')];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'error' => lang('passwords_doesnt_match')];
        }

        return ['success' => true];
    }

    public static function processProfileUploads($files, $currentUser)
    {
        $result = ['data' => [], 'error' => null];

        if (isset($files['avatar']) && $files['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatar = uploadFile($files['avatar'], 'avatars', ['width' => 200, 'height' => 200]);
            if ($avatar) {
                // Clean up old avatar file to save disk space
                if ($currentUser->avatar) {
                    @unlink(__DIR__ . '/../../../public/uploads/avatars/' . $currentUser->avatar);
                }
                $result['data']['avatar'] = $avatar;
            } else {
                $result['error'] = lang('incorrect_file_type');
                return $result;
            }
        }

        if (isset($files['cover']) && $files['cover']['error'] === UPLOAD_ERR_OK) {
            $cover = uploadFile($files['cover'], 'covers', ['width' => 1200, 'height' => 300]);
            if ($cover) {
                // Clean up old cover file to save disk space
                if ($currentUser->cover) {
                    @unlink(__DIR__ . '/../../../public/uploads/covers/' . $currentUser->cover);
                }
                $result['data']['cover'] = $cover;
            } else {
                $result['error'] = lang('incorrect_file_type');
                return $result;
            }
        }

        return $result;
    }

    public static function updateProfile($userId, $data, $files)
    {
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'error' => lang('user_not_found')];
        }

        $updateData = [];
        $isProfileUpdate = !empty($data['name']) || !empty($data['email']);

        if ($isProfileUpdate) {
            $errors = self::validateProfileData($data, $user);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'about' => $data['about'] ?? '',
                'website' => $data['website'] ?? '',
                'location' => $data['location'] ?? '',
                'facebook' => $data['facebook'] ?? '',
                'twitter' => $data['twitter'] ?? '',
                'private' => isset($data['private']) ? 1 : 0
            ];
        }

        $uploadResult = self::processProfileUploads($files, $user);
        if ($uploadResult['error']) {
            return ['success' => false, 'error' => $uploadResult['error']];
        }

        $updateData = array_merge($updateData, $uploadResult['data']);

        if (!empty($updateData)) {
            User::update($userId, $updateData);
            return ['success' => true, 'message' => lang('profile_updated')];
        }

        return ['success' => true, 'message' => null];
    }

    public static function changePassword($userId, $oldPassword, $newPassword, $confirmPassword)
    {
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'error' => lang('user_not_found')];
        }

        $validation = self::validatePasswordChange($oldPassword, $newPassword, $confirmPassword, $user);
        if (!$validation['success']) {
            return $validation;
        }

        User::updatePassword($userId, $newPassword);

        return ['success' => true, 'message' => lang('password_updated')];
    }

    public static function getProfilePageData($username, $viewerId = null)
    {
        $profileUser = User::findByUsername($username);
        $viewer = $viewerId ? User::find($viewerId) : null;

        $check = self::canViewProfile($viewer, $profileUser);
        if (!$check['allowed']) {
            return ['success' => false, 'error' => $check['error']];
        }

        $servers = Server::getUserServers($profileUser->id);
        $isOwnProfile = $viewer && $viewer->id == $profileUser->id;

        if (!$isOwnProfile) {
            $servers = array_filter($servers, function($server) {
                return $server->private == 0;
            });
        }

        return [
            'success' => true,
            'user' => $profileUser,
            'servers' => array_values($servers),
            'is_own_profile' => $isOwnProfile
        ];
    }

    public static function updateAdmin($targetUserId, $adminId, $data)
    {
        $user = User::find($targetUserId);
        if (!$user) {
            return [
                'success' => false,
                'error' => 'User not found'
            ];
        }

        $currentUser = User::find($adminId);
        // Prevent admins from accidentally changing their own permissions/status
        if ($user->id == $currentUser->id) {
            return [
                'success' => false,
                'error' => lang('status_yourself')
            ];
        }

        $updateData = [
            'username' => sanitize($data['username'] ?? ''),
            'email' => sanitize($data['email'] ?? ''),
            'name' => sanitize($data['name'] ?? ''),
            'about' => sanitize($data['about'] ?? ''),
            'website' => sanitize($data['website'] ?? ''),
            'location' => sanitize($data['location'] ?? ''),
            'type' => (int)($data['type'] ?? 0),
            'active' => isset($data['active']) ? 1 : 0,
            'private' => isset($data['private']) ? 1 : 0
        ];

        if (User::update($targetUserId, $updateData)) {
            AuditLog::log('update', 'users', $targetUserId, $adminId, 'Updated user: ' . $user->username);
            
            return [
                'success' => true,
                'message' => lang('user_updated')
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to update user'
        ];
    }

    public static function performAdminAction($targetUserId, $adminId, $action)
    {
        $user = User::find($targetUserId);
        
        if (!$user) {
            return [
                'success' => false,
                'error' => 'User not found'
            ];
        }

        $currentUser = User::find($adminId);
        // Prevent admins from deactivating/deleting themselves
        if ($user->id == $currentUser->id) {
            return [
                'success' => false,
                'error' => lang('status_yourself')
            ];
        }

        switch ($action) {
            case 'activate':
                User::update($targetUserId, ['active' => 1]);
                AuditLog::log('activate', 'users', $targetUserId, $adminId, 'Activated user: ' . $user->username);
                return ['success' => true, 'message' => lang('user_activated')];

            case 'deactivate':
                User::update($targetUserId, ['active' => 0]);
                AuditLog::log('deactivate', 'users', $targetUserId, $adminId, 'Deactivated user: ' . $user->username);
                return ['success' => true, 'message' => lang('user_deactivated')];

            case 'delete':
                if (User::delete($targetUserId)) {
                    AuditLog::log('delete', 'users', $targetUserId, $adminId, 'Deleted user: ' . $user->username);
                    return ['success' => true, 'message' => lang('user_deleted')];
                }
                return ['success' => false, 'error' => 'Failed to delete user'];

            default:
                return ['success' => false, 'error' => 'Invalid action'];
        }
    }

}
