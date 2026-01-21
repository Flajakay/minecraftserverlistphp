<?php

namespace App\Controllers;

use App\Core\Security\Auth;
use App\Core\System\Database;

/**
 * Authentication controller.
 *
 * Handles HTTP layer for login/registration, email activation, logout, and password reset flows.
 */
class AuthController
{
    /**
     * Render the login page.
     */
    public function showLogin()
    {
        if (isLoggedIn()) {
            redirect('/');
        }
                
        view('auth.login');
    }

    /**
     * Attempt to authenticate a user.
     *
     * Enforces lockout both by username and by IP to reduce brute-force attempts.
     */
    public function login()
    {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        $ip = $_SERVER['REMOTE_ADDR'];

        $result = Auth::attemptLogin($username, $password, $ip, $remember);

        if (!$result['success']) {
            flash('error', $result['message']);
            redirect('/login');
        }

        flash('success', $result['message']);
        redirect('/');
    }

    /**
     * Render the registration page.
     */
    public function showRegister()
    {
        if (isLoggedIn()) {
            redirect('/');
        }
                
        view('auth.register');
    }

    /**
     * Create a new user account.
     *
     * Depending on settings, the user is either activated immediately or must confirm via email.
     */
    public function register()
    {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $name = sanitize($_POST['name'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'];

        $result = Auth::register($username, $email, $password, $name, $ip);

        if (!$result['success']) {
            foreach ($result['errors'] as $error) {
                flash('error', $error);
            }
            redirect('/register');
        }

        if ($result['emailError']) {
            flash('error', $result['emailError']);
        } else if ($result['requiresActivation']) {
            flash('success', lang('registered_successfuly'));
        } else {
            flash('success', lang('registration_complete', 'Registration successful! You can now login.'));
        }
        
        redirect('/login');
    }

    /**
     * Logout the current user and clear session/cookies.
     */
    public function logout()
    {
        Auth::logout();
        flash('success', lang('loggedout'));
        redirect('/');
    }

    /**
     * Activate a user account using an emailed activation link.
     */
    public function activate($email, $code)
    {
        $email = urldecode($email);

        if (Auth::activateAccount($email, $code)) {
            flash('success', lang('account_activated'));
        } else {
            flash('error', lang('invalid_activation_link'));
        }

        redirect('/login');
    }

    /**
     * Render the "lost password" page.
     */
    public function showLostPassword()
    {
        view('auth.lost-password');
    }

    /**
     * Email a password reset link.
     */
    public function sendResetLink()
    {
        $email = sanitize($_POST['email'] ?? '');
        
        $result = Auth::initiatePasswordReset($email);

        if (!$result['success']) {
            flash('error', $result['error']);
            redirect('/lost-password');
        }

        flash('success', lang('lostpassword'));
        redirect('/lost-password');
    }

    /**
     * Render and handle the reset password form.
     */
    public function resetPassword($email, $code)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $result = Auth::resetPassword($email, $code, $password, $confirmPassword);

            if (!$result['success']) {
                flash('error', $result['error']);
                redirect("/reset-password/{$email}/{$code}");
            }

            flash('success', lang('password_updated'));
            redirect('/login');
        }

        $user = Database::fetch('SELECT * FROM users WHERE email = ? AND lost_password_code = ?', [$email, $code]);
        
        if (!$user) {
            flash('error', lang('invalid_reset_link'));
            redirect('/login');
        }

        view('auth.reset-password', ['email' => $email, 'code' => $code]);
    }
}
