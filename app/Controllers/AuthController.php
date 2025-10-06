<?php

namespace App\Controllers;

use App\Models\User;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Mail;
use App\Models\Setting;
use App\Core\SEO;
use App\Core\LoginSecurity;
use Exception;

class AuthController
{
    public function showLogin()
    {
        if (isLoggedIn()) {
            redirect('/');
        }
                
        view('auth.login');
    }

    public function login()
    {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        $ip = $_SERVER['REMOTE_ADDR'];

        if (empty($username) || empty($password)) {
            flash('error', lang('please_fill_all_fields'));
            redirect('/login');
        }

        if (LoginSecurity::isLockedOut($username, 'username')) {
            $remaining = LoginSecurity::getLockoutTimeRemaining($username, 'username');
            $minutes = ceil($remaining / 60);
            flash('error', sprintf(lang('account_locked_minutes'), $minutes));
            redirect('/login');
        }

        if (LoginSecurity::isLockedOut($ip, 'ip')) {
            $remaining = LoginSecurity::getLockoutTimeRemaining($ip, 'ip');
            $minutes = ceil($remaining / 60);
            flash('error', sprintf(lang('ip_locked_minutes'), $minutes));
            redirect('/login');
        }

        $user = Auth::attempt($username, $password);
        if ($user) {
            if (!User::isActive($username)) {
                flash('error', lang('account_not_active'));
                redirect('/login');
            }

            Auth::login($user, $remember);
            flash('success', lang('welcome_back'));
            redirect('/');
        }

        LoginSecurity::recordFailedAttempt($username, 'username', $ip);
        LoginSecurity::recordFailedAttempt($ip, 'ip', $ip);

        $remaining = LoginSecurity::getRemainingAttempts($username, 'username');
        if ($remaining > 0) {
            flash('error', sprintf(lang('invalid_credentials_attempts'), $remaining));
        } else {
            flash('error', lang('invalid_credentials_locked'));
        }

        redirect('/login');
    }

    public function showRegister()
    {
        if (isLoggedIn()) {
            redirect('/');
        }
                
        view('auth.register');
    }

    public function register()
    {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $name = sanitize($_POST['name'] ?? '');

        $errors = [];

        if (strlen($username) < 3 || strlen($username) > 32) {
            $errors[] = lang('username_length_validation');
        }

        if (User::findByUsername($username)) {
            $errors[] = lang('username_exists');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = lang('invalid_email');
        }

        if (User::findByEmail($email)) {
            $errors[] = lang('email_used');
        }

        if (strlen($password) < 6) {
            $errors[] = lang('password_too_short');
        }

        if (strlen($name) < 2 || strlen($name) > 32) {
            $errors[] = lang('name_length_validation');
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            redirect('/register');
        }

        $activationCode = bin2hex(random_bytes(16));
        
        $userId = User::create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'name' => $name,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'email_activation_code' => $activationCode
        ]);

        if (setting('email_confirmation', 0)) {
            try {
                $activationUrl = url('/activate/' . urlencode($email) . '/' . $activationCode);
                
                Mail::create()
                    ->to($email, $name)
                    ->template('activation', [
                        'name' => $name,
                        'activationUrl' => $activationUrl
                    ])
                    ->send();
                    
                flash('success', lang('registered_successfuly'));
            } catch (Exception $e) {
                flash('error', lang('email_send_failed', 'Registration successful but activation email could not be sent.'));
            }
        } else {
            Database::update('users', ['active' => 1], 'id = ?', [$userId]);
            flash('success', lang('registration_complete', 'Registration successful! You can now login.'));
        }
        
        redirect('/login');
    }

    public function logout()
    {
        Auth::logout();
        flash('success', lang('loggedout'));
        redirect('/');
    }

    public function activate($email, $code)
    {
        $email = urldecode($email);

        if (User::activate($email, $code)) {
            flash('success', lang('account_activated'));
        } else {
            flash('error', lang('invalid_activation_link'));
        }

        redirect('/login');
    }

    public function showLostPassword()
    {
        view('auth.lost-password');
    }

    public function sendResetLink()
    {
        $email = sanitize($_POST['email'] ?? '');
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', lang('invalid_email'));
            redirect('/lost-password');
        }

        $user = User::findByEmail($email);
        if (!$user) {
            flash('error', lang('email_doesnt_exist'));
            redirect('/lost-password');
        }

        $code = bin2hex(random_bytes(16));
        Database::update('users', ['lost_password_code' => $code], 'id = ?', [$user->id]);

        try {
            $resetUrl = url('/reset-password/' . urlencode($email) . '/' . $code);
            
            Mail::create()
                ->to($email, $user->name)
                ->template('reset-password', [
                    'name' => $user->name,
                    'resetUrl' => $resetUrl
                ])
                ->send();
                
            flash('success', lang('lostpassword'));
        } catch (Exception $e) {
            flash('error', lang('email_send_failed', 'Failed to send reset email. Please try again later.'));
        }
        
        redirect('/lost-password');
    }

    public function resetPassword($email, $code)
    {
        $user = Database::fetch('SELECT * FROM users WHERE email = ? AND lost_password_code = ?', [$email, $code]);
        
        if (!$user) {
            flash('error', lang('invalid_reset_link'));
            redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (strlen($password) < 6) {
                flash('error', lang('password_too_short'));
                redirect("/reset-password/{$email}/{$code}");
            }

            if ($password !== $confirmPassword) {
                flash('error', lang('passwords_doesnt_match'));
                redirect("/reset-password/{$email}/{$code}");
            }

            User::updatePassword($user->id, $password);
            Database::update('users', ['lost_password_code' => ''], 'id = ?', [$user->id]);

            flash('success', lang('password_updated'));
            redirect('/login');
        }

        view('auth.reset-password', ['email' => $email, 'code' => $code]);
    }
}
