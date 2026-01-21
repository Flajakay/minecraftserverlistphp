<?php

namespace App\Controllers;

use App\Core\Features\Users;

class UserController
{
    public function show($username)
    {
        $viewerId = isLoggedIn() ? auth()->id : null;
        $result = Users::getProfilePageData($username, $viewerId);

        if (!$result['success']) {
            flash('error', $result['error']);
            redirect('/');
        }

        view('users.profile', [
            'user' => $result['user'],
            'servers' => $result['servers']
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

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'about' => sanitize($_POST['about'] ?? ''),
            'website' => sanitize($_POST['website'] ?? ''),
            'location' => sanitize($_POST['location'] ?? ''),
            'facebook' => sanitize($_POST['facebook'] ?? ''),
            'twitter' => sanitize($_POST['twitter'] ?? ''),
            'private' => isset($_POST['private'])
        ];

        $result = Users::updateProfile(auth()->id, $data, $_FILES);

        if (!$result['success']) {
            if (isset($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    flash('error', $error);
                }
            } else {
                flash('error', $result['error']);
            }
            redirect('/settings/profile');
        }

        if ($result['message']) {
            flash('success', $result['message']);
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

        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $result = Users::changePassword(auth()->id, $oldPassword, $newPassword, $confirmPassword);

        if (!$result['success']) {
            flash('error', $result['error']);
            redirect('/settings/password');
        }

        flash('success', $result['message']);
        redirect('/settings/password');
    }
}
