<?php

namespace App\Core;

use App\Models\User;
use App\Models\Server;

class Users
{
    public static function canViewProfile($viewer, $profileUser)
    {
        if (!$profileUser) {
            return ['allowed' => false, 'error' => lang('user_not_found')];
        }

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
                if ($currentUser->avatar) {
                    @unlink(__DIR__ . '/../../public/uploads/avatars/' . $currentUser->avatar);
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
                if ($currentUser->cover) {
                    @unlink(__DIR__ . '/../../public/uploads/covers/' . $currentUser->cover);
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
}
