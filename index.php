<?php

use App\Core\Router;

if (!file_exists(__DIR__ . '/config/app.php') || filesize(__DIR__ . '/config/app.php') < 100) {
    // If the main config is missing or clearly incomplete (tiny file),
    // prefer redirecting to the installer when available so the user
    // can set up the app. Otherwise show a clear error to avoid
    // obscure failures later in the request lifecycle.
    if (file_exists(__DIR__ . '/install.php')) {
        header('Location: install.php');
        exit;
    } else {
        die('<h1>Configuration Error</h1><p>Please run the installation or restore the config/app.php file.</p>');
    }
}

require_once __DIR__ . '/bootstrap.php';

$router = new Router();
require __DIR__ . '/routes/web.php';

$uri = $_SERVER['REQUEST_URI'];

if (($pos = strpos($uri, '?')) !== false) {
    // Strip query string because routing only considers the path.
    $uri = substr($uri, 0, $pos);
}

$method = $_SERVER['REQUEST_METHOD'];

$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/') {
    // If the app is hosted in a subdirectory (not document root),
    // remove that base path from the requested URI so routes are
    // matched against the path relative to the app's root.
    $uri = str_replace($basePath, '', $uri);
}

$uri = '/' . ltrim($uri, '/');

$router->dispatch($method, $uri);
