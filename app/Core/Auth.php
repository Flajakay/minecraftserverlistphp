<?php

namespace App\Core;

use App\Core\CookieManager;
use App\Core\LoginSecurity;

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
        return isset($_SESSION['user_id']) || 
               (CookieManager::has('user_id') && CookieManager::has('remember_token'));
    }

    public static function login($user, $remember = false)
    {
        $_SESSION['user_id'] = $user->id;
        
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            CookieManager::set('user_id', $user->id, 30);
            CookieManager::set('remember_token', $token, 30);
            
            Database::update('users', ['remember_token' => $token], 'id = ?', [$user->id]);
        }
    }

    public static function logout()
    {
        session_destroy();
        CookieManager::delete('user_id');
        CookieManager::delete('remember_token');
    }

    public static function attempt($username, $password)
    {
        $user = Database::fetch('SELECT * FROM users WHERE username = ?', [$username]);

        if ($user && self::verifyPassword($password, $user->password)) {
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
}
