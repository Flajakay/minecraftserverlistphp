<?php

use App\Core\Support\Language;
use App\Core\System\Database;

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

// Manually load global helper functions
require_once __DIR__ . '/app/Core/Support/Helpers.php';

// Initialize Language system
Language::initialize();

// Initialize Database connection
Database::connect();

// Initialize Rate Limiting and logs directory
ensureDirectoryExists(__DIR__ . '/storage/logs');
