<?php

ob_start();

// Configure session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 1 : 0);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

session_start();

// Load Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Manually load global functions
require_once __DIR__ . '/app/Core/Helpers.php';
require_once __DIR__ . '/app/Core/JoditHelper.php';

// Initialize Language system
\App\Core\Language::initialize();

// Initialize Database connection
\App\Core\Database::connect();

// Initialize Rate Limiting logs directory
ensureDirectoryExists(__DIR__ . '/storage/logs');
