<?php

use App\Core\Support\Language;
use App\Core\System\Database;
use App\Core\System\SessionHandler;

ob_start();

// Load Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Manually load global helper functions
require_once __DIR__ . '/app/Core/Support/Helpers.php';

// Initialize Database connection
Database::connect();

// Configure session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 1 : 0);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

// Set custom database session handler
$handler = new SessionHandler();
session_set_save_handler($handler, true);

session_start();

// Session Security: IP Binding
// Check if the session is bound to the current IP address to prevent session hijacking.
if (!isset($_SESSION['SECURITY_REMOTE_IP'])) {
    $_SESSION['SECURITY_REMOTE_IP'] = $_SERVER['REMOTE_ADDR'];
} elseif ($_SESSION['SECURITY_REMOTE_IP'] !== $_SERVER['REMOTE_ADDR']) {
    // If the IP address does not match the one stored in the session, 
    // invalidate the session immediately.
    session_unset();
    session_destroy();
    
    // Restart a new session for the new IP
    session_start();
    session_regenerate_id(true);
    $_SESSION['SECURITY_REMOTE_IP'] = $_SERVER['REMOTE_ADDR'];
}

// Initialize Language system
Language::initialize();

// Initialize Rate Limiting and logs directory
ensureDirectoryExists(__DIR__ . '/storage/logs');
