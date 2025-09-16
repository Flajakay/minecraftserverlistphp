<?php

function view($name, $data = [])
{
    extract($data);
    
    $viewFile = __DIR__ . '/../../resources/views/' . str_replace('.', '/', $name) . '.php';
    
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        throw new Exception("View {$name} not found");
    }
}

function redirect($url = '/')
{
    header("Location: {$url}");
    exit;
}

function old($key, $default = '')
{
    return $_SESSION['old'][$key] ?? $default;
}

function session($key, $value = null)
{
    if ($value === null) {
        return $_SESSION[$key] ?? null;
    }
    
    $_SESSION[$key] = $value;
}

function flash($key, $message)
{
    $_SESSION['flash'][$key] = $message;
}

function getFlash($key)
{
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

function csrf()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token)
{
    return \App\Core\Csrf::verify($token);
}

function asset($path)
{
    $config = require __DIR__ . '/../../config/app.php';
    return $config['url'] . 'assets/' . ltrim($path, '/');
}

function url($path = '')
{
    $config = require __DIR__ . '/../../config/app.php';
    return rtrim($config['url'], '/') . '/' . ltrim($path, '/');
}

function sanitize($input)
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

function timeAgo($timestamp)
{
    // Handle null or empty timestamps
    if (empty($timestamp)) {
        return 'unknown';
    }
    
    // Convert to timestamp if it's a string
    $timestampValue = is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
    
    // Handle invalid timestamps
    if ($timestampValue === false || $timestampValue === null) {
        return 'unknown';
    }
    
    $time = time() - $timestampValue;
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2629440) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', $timestampValue);
}

function getCountries()
{
    return [
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'CA' => 'Canada',
        'AU' => 'Australia',
        'DE' => 'Germany',
        'FR' => 'France',
        'NL' => 'Netherlands',
        'SE' => 'Sweden',
        'NO' => 'Norway',
        'DK' => 'Denmark',
        'PL' => 'Poland'
    ];
}

function getCountryName($code)
{
    $countries = getCountries();
    return $countries[$code] ?? $code;
}

function lang($key, $default = null)
{
    return \App\Core\Language::get($key, $default);
}

function setting($key, $default = null)
{
    return \App\Models\Setting::getValue($key, $default);
}

function getAvailableLanguages()
{
    return \App\Core\Language::getAvailableLanguages();
}

function getCurrentLanguage()
{
    return \App\Core\Language::getCurrentLanguage();
}

function uploadFile($file, $directory, $resize = null)
{
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $path = __DIR__ . '/../../public/uploads/' . $directory;
    
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
    
    if ($resize) {
        if (resizeImage($file['tmp_name'], $path . '/' . $filename, $resize['width'], $resize['height'])) {
            return $filename;
        }
    } else {
        if (move_uploaded_file($file['tmp_name'], $path . '/' . $filename)) {
            return $filename;
        }
    }
    
    return false;
}

function resizeImage($source, $destination, $width, $height)
{
    $imageInfo = getimagesize($source);
    if (!$imageInfo) {
        return false;
    }
    
    $sourceWidth = $imageInfo[0];
    $sourceHeight = $imageInfo[1];
    $mimeType = $imageInfo['mime'];
    
    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($source);
            break;
        default:
            return false;
    }
    
    if (!$sourceImage) {
        return false;
    }
    
    $aspectRatio = $sourceWidth / $sourceHeight;
    $targetAspectRatio = $width / $height;
    
    if ($aspectRatio > $targetAspectRatio) {
        $newWidth = $width;
        $newHeight = $width / $aspectRatio;
    } else {
        $newHeight = $height;
        $newWidth = $height * $aspectRatio;
    }
    
    $targetImage = imagecreatetruecolor($width, $height);
    
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
        imagefill($targetImage, 0, 0, $transparent);
    } else {
        $white = imagecolorallocate($targetImage, 255, 255, 255);
        imagefill($targetImage, 0, 0, $white);
    }
    
    $offsetX = ($width - $newWidth) / 2;
    $offsetY = ($height - $newHeight) / 2;
    
    imagecopyresampled(
        $targetImage, $sourceImage,
        $offsetX, $offsetY, 0, 0,
        $newWidth, $newHeight, $sourceWidth, $sourceHeight
    );
    
    $result = false;
    switch ($mimeType) {
        case 'image/jpeg':
            $result = imagejpeg($targetImage, $destination, 85);
            break;
        case 'image/png':
            $result = imagepng($targetImage, $destination, 8);
            break;
        case 'image/gif':
            $result = imagegif($targetImage, $destination);
            break;
    }
    
    imagedestroy($sourceImage);
    imagedestroy($targetImage);
    
    return $result;
}

// Authentication helpers
function auth()
{
    return \App\Core\Auth::user();
}

function isLoggedIn()
{
    return \App\Core\Auth::check();
}

function isAdmin()
{
    return \App\Core\Auth::isAdmin();
}

function isOwner()
{
    return \App\Core\Auth::isOwner();
}

function rateLimitCheck($route, $method = 'GET')
{
    return \App\Core\RateLimit::getInstance()->checkRequest($route, $method);
}

function rateLimitRetryAfter($route, $method = 'GET')
{
    return \App\Core\RateLimit::getInstance()->getRetryAfter($route, $method);
}

function ensureDirectoryExists($path, $permissions = 0755)
{
    if (!is_dir($path)) {
        mkdir($path, $permissions, true);
    }
}

function seo()
{
    return \App\Core\SEO::class;
}

function setTitle($title)
{
    \App\Core\SEO::setTitle($title);
}

function setDescription($description)
{
    \App\Core\SEO::setDescription($description);
}

function setKeywords($keywords)
{
    \App\Core\SEO::setKeywords($keywords);
}

function setCanonical($url)
{
    \App\Core\SEO::setCanonical($url);
}

function setRobots($robots)
{
    \App\Core\SEO::setRobots($robots);
}

function renderMetaTags()
{
    return \App\Core\SEO::renderMetaTags();
}
