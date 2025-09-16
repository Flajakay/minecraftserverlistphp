<?php

use App\Core\Router;

if (!file_exists(__DIR__ . '/config/app.php') || filesize(__DIR__ . '/config/app.php') < 100) {
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
    $uri = substr($uri, 0, $pos);
}

$method = $_SERVER['REQUEST_METHOD'];

$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/') {
    $uri = str_replace($basePath, '', $uri);
}

$uri = '/' . ltrim($uri, '/');

$router->dispatch($method, $uri);
