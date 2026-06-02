<?php

namespace App\Core\Security;

use App\Core\Support\CookieManager;
use App\Core\Security\RateLimit;
use App\Core\System\Mail;
use App\Models\User;
use Exception;

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
        // Check session first (fastest)
        if (isset($_SESSION['user_id'])) {
            $user = User::find($_SESSION['user_id']);
            if ($user) {
                return $user;
            }
            // User in session does not exist in DB (deleted?), clear session
            session_unset();
            session_destroy();
        }

        // Check cookies (slower, requires DB)
        $userId = CookieManager::get('user_id');
        $token = CookieManager::get('remember_token');

        if ($userId && $token) {
            $user = User::find($userId);
            // Verify token. Use hash_equals to prevent timing attacks.
            // Ensure stored token is not empty to prevent bypass against users with no token.
            if ($user && !empty($user->remember_token) && hash_equals($user->remember_token, $token)) {
                // Token is valid, restore session
                $_SESSION['user_id'] = $user->id;
                session_regenerate_id(true);
                return $user;
            } else {
                // Invalid or stale token, clear cookies
                self::logout();
            }
        }
        
        return null;
    }

    public static function login($user, $remember = false): void
    {
        $_SESSION['user_id'] = $user->id;
        
        if ($remember) {
            // Store a random token in both cookie + DB so the cookie alone is not sufficient.
            $token = bin2hex(random_bytes(32));
            CookieManager::set('user_id', $user->id);
            CookieManager::set('remember_token', $token);
            
            User::update($user->id, ['remember_token' => $token]);
        }
    }

    public static function logout(): void
    {
        // Destroy session and clear remember-me cookies.
        session_destroy();
        CookieManager::delete('user_id');
        CookieManager::delete('remember_token');
    }

    public static function attemptLogin($username, $password, $ip = null, $remember = false): array
    {
        $ip = $ip ?? RateLimit::getInstance()->getClientIp();

        // Validate input
        $validationError = self::validateLoginInput($username, $password);
        if ($validationError) {
            return $validationError;
        }

        // Check lockouts
        $lockoutError = self::checkLockouts($username, $ip);
        if ($lockoutError) {
            return $lockoutError;
        }

        // Attempt authentication
        $user = self::attempt($username, $password, $ip);
        if (!$user) {
            return self::handleFailedLogin($username, $ip);
        }

        // Check account status
        if (!$user->active) {
            return [
                'success' => false,
                'error' => 'account_inactive',
                'message' => lang('account_not_active')
            ];
        }

        // Successful login
        self::login($user, $remember);
        
        return [
            'success' => true,
            'user' => $user,
            'message' => lang('welcome_back')
        ];
    }

    private static function validateLoginInput($username, $password): ?array
    {
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'error' => 'empty_fields',
                'message' => lang('please_fill_all_fields')
            ];
        }
        
        return null;
    }

    private static function checkLockouts($username, $ip): ?array
    {
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

        return null;
    }

    private static function handleFailedLogin($username, $ip): array
    {
        LoginSecurity::recordFailedAttempt($username, 'username', $ip);
        LoginSecurity::recordFailedAttempt($ip, 'ip', $ip);
        
        $remaining = LoginSecurity::getRemainingAttempts($username, 'username');
        
        $message = $remaining > 0 
            ? sprintf(lang('invalid_credentials_attempts'), $remaining)
            : lang('invalid_credentials_locked');
        
        return [
            'success' => false,
            'error' => 'invalid_credentials',
            'message' => $message,
            'remaining' => $remaining
        ];
    }

    public static function attempt($username, $password, $ip = null)
    {
        // Use RateLimit's IP detection if not provided explicitly
        if ($ip === null) {
            $rateLimit = RateLimit::getInstance();
            $ip = $rateLimit->getClientIp();
        }

        $user = User::findByUsername($username);

        if ($user && self::verifyPassword($password, $user->password)) {
            // Successful login resets lockouts for both username and IP.
            LoginSecurity::clearFailedAttempts($username, 'username');
            LoginSecurity::clearFailedAttempts($ip, 'ip');
            return $user;
        }

        return false;
    }

    public static function hashPassword($password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    private static function verifyPassword($password, $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && $user->type > 0;
    }

    public static function isOwner(): bool
    {
        $user = self::user();
        return $user && $user->type > 1;
    }

    public static function validateUsername($username): ?string
    {
        if (strlen($username) < 3 || strlen($username) > 32) {
            return lang('username_length_validation');
        }

        if (User::findByUsername($username)) {
            return lang('username_exists');
        }

        return null;
    }

    public static function validateEmail($email): ?string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return lang('invalid_email');
        }

        if (User::findByEmail($email)) {
            return lang('email_used');
        }

        return null;
    }

    public static function validatePasswordStrength($password): ?string
    {
        if (strlen($password) < 6) {
            return lang('password_too_short');
        }

        return null;
    }

    public static function validateRegistrationData($username, $email, $password, $name): array
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

    public static function register($username, $email, $password, $name, $ip = null): array
    {
        // Use RateLimit's IP detection if not provided explicitly
        if ($ip === null) {
            $rateLimit = RateLimit::getInstance();
            $ip = $rateLimit->getClientIpAddress();
        }

        $errors = self::validateRegistrationData($username, $email, $password, $name);
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'userId' => null
            ];
        }

        $activationCode = bin2hex(random_bytes(16));
        
        $userId = User::create([
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
            } catch (Exception) {
                $emailError = lang('email_send_failed', 'Registration successful but activation email could not be sent.');
            }
        } else {
            User::update($userId, ['active' => 1]);
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

    public static function activateAccount($email, $code): bool
    {
        return User::activate($email, $code) > 0;
    }

    public static function initiatePasswordReset($email): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'error' => lang('invalid_email'),
                'user' => null
            ];
        }

        $user = User::findByEmail($email);
        if (!$user) {
            return [
                'success' => false,
                'error' => lang('email_doesnt_exist'),
                'user' => null
            ];
        }

        $code = bin2hex(random_bytes(16));
        User::update($user->id, ['lost_password_code' => $code]);

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
        } catch (Exception) {
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
        return User::findByResetCode($email, $code);
    }

    public static function resetPassword($email, $code, $newPassword, $confirmPassword): array
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
        User::update($user->id, [
            'password' => $hashedPassword,
            'lost_password_code' => ''
        ]);

        return [
            'success' => true,
            'error' => null
        ];
    }
}
