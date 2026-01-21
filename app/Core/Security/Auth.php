<?php

namespace App\Core\Security;

use App\Core\Support\CookieManager;

use App\Core\System\Mail;
use App\Core\System\Database;

/**
 * Authentication facade.
 *
 * Stores the logged-in user id in the session and optionally supports a simple
 * "remember me" flow via cookies + a DB token.
 */
class Auth
{
    public static function user()
    {
        if (!self::check()) {
            return null;
        }
        
        $userId = $_SESSION['user_id'] ?? CookieManager::get('user_id') ?? null;
        if (!$userId) {
            return null;
        }
        
        return Database::fetch('SELECT * FROM users WHERE id = ?', [$userId]);
    }

    public static function check()
    {
        // Consider a user authenticated if they have an active session OR a remember-me cookie.
        return isset($_SESSION['user_id']) || 
               (CookieManager::has('user_id') && CookieManager::has('remember_token'));
    }

    public static function login($user, $remember = false)
    {
        $_SESSION['user_id'] = $user->id;
        
        if ($remember) {
            // Store a random token in both cookie + DB so the cookie alone is not sufficient.
            $token = bin2hex(random_bytes(32));
            CookieManager::set('user_id', $user->id, 30);
            CookieManager::set('remember_token', $token, 30);
            
            Database::update('users', ['remember_token' => $token], 'id = ?', [$user->id]);
        }
    }

    public static function logout()
    {
        // Destroy session and clear remember-me cookies.
        session_destroy();
        CookieManager::delete('user_id');
        CookieManager::delete('remember_token');
    }

    public static function attemptLogin($username, $password, $ip, $remember = false)
    {
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'error' => 'empty_fields',
                'message' => lang('please_fill_all_fields')
            ];
        }

        if (LoginSecurity::isLockedOut($username, 'username')) {
            $remaining = LoginSecurity::getLockoutTimeRemaining($username, 'username');
            $minutes = ceil($remaining / 60);
            return [
                'success' => false,
                'error' => 'username_locked',
                'message' => sprintf(lang('account_locked_minutes'), $minutes),
                'minutes' => $minutes
            ];
        }

        if (LoginSecurity::isLockedOut($ip, 'ip')) {
            $remaining = LoginSecurity::getLockoutTimeRemaining($ip, 'ip');
            $minutes = ceil($remaining / 60);
            return [
                'success' => false,
                'error' => 'ip_locked',
                'message' => sprintf(lang('ip_locked_minutes'), $minutes),
                'minutes' => $minutes
            ];
        }

        $user = self::attempt($username, $password);
        
        if (!$user) {
            LoginSecurity::recordFailedAttempt($username, 'username', $ip);
            LoginSecurity::recordFailedAttempt($ip, 'ip', $ip);
            
            $remaining = LoginSecurity::getRemainingAttempts($username, 'username');
            
            if ($remaining > 0) {
                $message = sprintf(lang('invalid_credentials_attempts'), $remaining);
            } else {
                $message = lang('invalid_credentials_locked');
            }
            
            return [
                'success' => false,
                'error' => 'invalid_credentials',
                'message' => $message,
                'remaining' => $remaining
            ];
        }

        if (!$user->active) {
            return [
                'success' => false,
                'error' => 'account_inactive',
                'message' => lang('account_not_active')
            ];
        }

        self::login($user, $remember);
        
        return [
            'success' => true,
            'user' => $user,
            'message' => lang('welcome_back')
        ];
    }

    public static function attempt($username, $password)
    {
        $user = Database::fetch('SELECT * FROM users WHERE username = ?', [$username]);

        if ($user && self::verifyPassword($password, $user->password)) {
            // Successful login resets lockouts for both username and IP.
            LoginSecurity::clearFailedAttempts($username, 'username');
            LoginSecurity::clearFailedAttempts($_SERVER['REMOTE_ADDR'], 'ip');
            return $user;
        }

        return false;
    }

    public static function hashPassword($password, $username = null)
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    private static function verifyPassword($password, $hash, $username = null)
    {
        return password_verify($password, $hash);
    }

    public static function isAdmin()
    {
        $user = self::user();
        return $user && $user->type > 0;
    }

    public static function isOwner()
    {
        $user = self::user();
        return $user && $user->type > 1;
    }

    public static function validateUsername($username)
    {
        if (strlen($username) < 3 || strlen($username) > 32) {
            return lang('username_length_validation');
        }

        $existingUser = Database::fetch('SELECT id FROM users WHERE username = ?', [$username]);
        if ($existingUser) {
            return lang('username_exists');
        }

        return null;
    }

    public static function validateEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return lang('invalid_email');
        }

        $existingUser = Database::fetch('SELECT id FROM users WHERE email = ?', [$email]);
        if ($existingUser) {
            return lang('email_used');
        }

        return null;
    }

    public static function validatePasswordStrength($password)
    {
        if (strlen($password) < 6) {
            return lang('password_too_short');
        }

        return null;
    }

    public static function validateRegistrationData($username, $email, $password, $name)
    {
        $errors = [];

        $usernameError = self::validateUsername($username);
        if ($usernameError) {
            $errors[] = $usernameError;
        }

        $emailError = self::validateEmail($email);
        if ($emailError) {
            $errors[] = $emailError;
        }

        $passwordError = self::validatePasswordStrength($password);
        if ($passwordError) {
            $errors[] = $passwordError;
        }

        if (strlen($name) < 2 || strlen($name) > 32) {
            $errors[] = lang('name_length_validation');
        }

        return $errors;
    }

    public static function register($username, $email, $password, $name, $ip)
    {
        $errors = self::validateRegistrationData($username, $email, $password, $name);
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'userId' => null
            ];
        }

        $activationCode = bin2hex(random_bytes(16));
        
        $userId = Database::insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => self::hashPassword($password),
            'name' => $name,
            'ip' => $ip,
            'email_activation_code' => $activationCode,
            'created_at' => date('Y-m-d H:i:s'),
            'active' => 0,
            'type' => 0
        ]);

        $emailSent = false;
        $emailError = null;

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
                    
                $emailSent = true;
            } catch (\Exception $e) {
                $emailError = lang('email_send_failed', 'Registration successful but activation email could not be sent.');
            }
        } else {
            Database::update('users', ['active' => 1], 'id = ?', [$userId]);
        }

        return [
            'success' => true,
            'errors' => [],
            'userId' => $userId,
            'emailSent' => $emailSent,
            'emailError' => $emailError,
            'requiresActivation' => setting('email_confirmation', 0)
        ];
    }

    public static function activateAccount($email, $code)
    {
        $updated = Database::update('users', 
            ['active' => 1, 'email_activation_code' => ''], 
            'email = ? AND email_activation_code = ?', 
            [$email, $code]
        );

        return $updated > 0;
    }

    public static function initiatePasswordReset($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'error' => lang('invalid_email'),
                'user' => null
            ];
        }

        $user = Database::fetch('SELECT * FROM users WHERE email = ?', [$email]);
        if (!$user) {
            return [
                'success' => false,
                'error' => lang('email_doesnt_exist'),
                'user' => null
            ];
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
                
            return [
                'success' => true,
                'error' => null,
                'user' => $user
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => lang('email_send_failed', 'Failed to send reset email. Please try again later.'),
                'user' => $user
            ];
        }
    }

    /**
     * Validate that a password reset link is valid.
     * Returns user object if valid, null otherwise.
     */
    public static function validateResetLink($email, $code)
    {
        return Database::fetch('SELECT * FROM users WHERE email = ? AND lost_password_code = ?', [$email, $code]);
    }

    public static function resetPassword($email, $code, $newPassword, $confirmPassword)
    {
        $user = self::validateResetLink($email, $code);
        
        if (!$user) {
            return [
                'success' => false,
                'error' => lang('invalid_reset_link')
            ];
        }

        $passwordError = self::validatePasswordStrength($newPassword);
        if ($passwordError) {
            return [
                'success' => false,
                'error' => $passwordError
            ];
        }

        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'error' => lang('passwords_doesnt_match')
            ];
        }

        $hashedPassword = self::hashPassword($newPassword);
        Database::update('users', ['password' => $hashedPassword], 'id = ?', [$user->id]);
        Database::update('users', ['lost_password_code' => ''], 'id = ?', [$user->id]);

        return [
            'success' => true,
            'error' => null
        ];
    }
}
