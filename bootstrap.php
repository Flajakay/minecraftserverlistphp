<?php

ob_start();

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
