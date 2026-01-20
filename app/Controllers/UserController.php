<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Server;
use App\Core\Auth;
use App\Core\Database;

class UserController
{
    public function show($username)
    {
        $user = User::findByUsername($username);
        if (!$user) {
            flash('error', lang('user_not_found'));
            redirect('/');
        }

        if ($user->private && (!isLoggedIn() || auth()->id != $user->id)) {
            flash('error', lang('profile_private'));
            redirect('/');
        }

        $servers = Server::getUserServers($user->id);

        $isOwnProfile = isLoggedIn() && auth()->id == $user->id;
        if (!$isOwnProfile) {
            $servers = array_filter($servers, function($server) {
                return $server->private == 0;
            });
        }

        view('users.profile', [
            'user' => $user,
            'servers' => $servers
        ]);
    }

    public function editProfile()
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        view('users.edit-profile', ['user' => auth()]);
    }

    public function updateProfile()
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $user = auth();
        
        $isImageUpload = (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) || 
                        (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK);
        
        $isProfileUpdate = !empty($_POST['name']) || !empty($_POST['email']);
        
        $updateData = [];
        
        if ($isProfileUpdate) {
            $name = sanitize($_POST['name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $about = sanitize($_POST['about'] ?? '');
            $website = sanitize($_POST['website'] ?? '');
            $location = sanitize($_POST['location'] ?? '');
            $facebook = sanitize($_POST['facebook'] ?? '');
            $twitter = sanitize($_POST['twitter'] ?? '');
            $private = isset($_POST['private']) ? 1 : 0;

            $errors = [];

            if (strlen($name) < 2 || strlen($name) > 32) {
                $errors[] = lang('name_length');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = lang('invalid_email');
            }

            $existingUser = User::findByEmail($email);
            if ($existingUser && $existingUser->id != $user->id) {
                $errors[] = lang('email_used');
            }

            if (strlen($about) > 128) {
                $errors[] = lang('about_too_big');
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    flash('error', $error);
                }
                redirect('/settings/profile');
            }

            $updateData = [
                'name' => $name,
                'email' => $email,
                'about' => $about,
                'website' => $website,
                'location' => $location,
                'facebook' => $facebook,
                'twitter' => $twitter,
                'private' => $private
            ];
        }

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatar = uploadFile($_FILES['avatar'], 'avatars', ['width' => 200, 'height' => 200]);
            if ($avatar) {
                if ($user->avatar) {
                    @unlink(__DIR__ . '/../../public/uploads/avatars/' . $user->avatar);
                }
                $updateData['avatar'] = $avatar;
            } else {
                flash('error', lang('incorrect_file_type'));
                redirect('/settings/profile');
            }
        }

        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $cover = uploadFile($_FILES['cover'], 'covers', ['width' => 1200, 'height' => 300]);
            if ($cover) {
                if ($user->cover) {
                    @unlink(__DIR__ . '/../../public/uploads/covers/' . $user->cover);
                }
                $updateData['cover'] = $cover;
            } else {
                flash('error', lang('incorrect_file_type'));
                redirect('/settings/profile');
            }
        }

        if (!empty($updateData)) {
            Database::update('users', $updateData, 'id = ?', [$user->id]);
            flash('success', lang('profile_updated'));
        }
        
        redirect('/settings/profile');
    }

    public function changePassword()
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        view('users.change-password');
    }

    public function updatePassword()
    {
        if (!isLoggedIn()) {
            redirect('/login');
        }

        $user = auth();
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!Auth::attempt($user->username, $oldPassword)) {
            flash('error', lang('incorrect_old_password'));
            redirect('/settings/password');
        }

        if (strlen($newPassword) < 6) {
            flash('error', lang('password_too_short'));
            redirect('/settings/password');
        }

        if ($newPassword !== $confirmPassword) {
            flash('error', lang('passwords_doesnt_match'));
            redirect('/settings/password');
        }

        User::updatePassword($user->id, $newPassword);

        flash('success', lang('password_updated'));
        redirect('/settings/password');
    }
}
