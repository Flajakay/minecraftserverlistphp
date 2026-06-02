<?php

use App\Core\Security\Auth;
use App\Core\Security\Csrf;
use App\Core\Security\RateLimit;
use App\Core\Support\Config;
use App\Core\Support\Env;
use App\Core\Support\Language;
use App\Core\Support\SEO;
use App\Models\Setting;

function view($name, $data = []): void
{
    extract($data);
    
    $viewFile = __DIR__ . '/../../../resources/views/' . str_replace('.', '/', $name) . '.php';
    
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        throw new Exception("View $name not found");
    }
}

function redirect($url = '/'): void
{
    header("Location: $url");
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

function flash($key, $message): void
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

function verifyCsrf($token): bool
{
    return Csrf::verify($token);
}

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return Env::get($key, $default);
    }
}

function asset($path): string
{
    $url = Config::get('app.url', 'http://localhost:8080/');
    return $url . 'assets/' . ltrim($path, '/');
}

function url($path = ''): string
{
    $url = Config::get('app.url', 'http://localhost:8080/');
    return rtrim($url, '/') . '/' . ltrim($path, '/');
}

function sanitize($input): array|string
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function displayHtml($input)
{
    if (empty($input)) {
        return '';
    }
    
    // If content appears HTML-escaped already, decode it for rendering.
    if (str_contains($input, '&lt;') || str_contains($input, '&gt;')) {
        return html_entity_decode($input, ENT_QUOTES, 'UTF-8');
    }
    
    // Otherwise, return as-is (caller is responsible for ensuring safety).
    return $input;
}

function formatBytes($bytes, $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

function timeAgo($timestamp): string
{
    // Gracefully handle missing/invalid timestamps from legacy data.
    if (empty($timestamp)) {
        return 'unknown';
    }
    
    // Accept unix timestamps or date strings.
    $timestampValue = is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
    
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

function getCountries(): array
{

    return array("AF" => "Afghanistan", "AL" => "Albania", "DZ" => "Algeria", "AS" => "American Samoa", "AD" => "Andorra", "AO" => "Angola", "AI" => "Anguilla", "AQ" => "Antarctica", "AG" => "Antigua and Barbuda", "AR" => "Argentina", "AM" => "Armenia", "AW" => "Aruba", "AU" => "Australia", "AT" => "Austria", "AZ" => "Azerbaijan", "AX" => "Åland Islands", "BS" => "Bahamas", "BH" => "Bahrain", "BD" => "Bangladesh", "BB" => "Barbados", "BY" => "Belarus", "BE" => "Belgium", "BZ" => "Belize", "BJ" => "Benin", "BM" => "Bermuda", "BT" => "Bhutan", "BO" => "Bolivia", "BA" => "Bosnia and Herzegovina", "BW" => "Botswana", "BV" => "Bouvet Island", "BR" => "Brazil", "BQ" => "British Antarctic Territory", "IO" => "British Indian Ocean Territory", "VG" => "British Virgin Islands", "BN" => "Brunei", "BG" => "Bulgaria", "BF" => "Burkina Faso", "BI" => "Burundi", "KH" => "Cambodia", "CM" => "Cameroon", "CA" => "Canada", "CV" => "Cape Verde", "KY" => "Cayman Islands", "CF" => "Central African Republic", "TD" => "Chad", "CL" => "Chile", "CN" => "China", "CX" => "Christmas Island", "CC" => "Cocos [Keeling] Islands", "CO" => "Colombia", "KM" => "Comoros", "CG" => "Congo - Brazzaville", "CD" => "Congo - Kinshasa", "CK" => "Cook Islands", "CR" => "Costa Rica", "HR" => "Croatia", "CU" => "Cuba", "CY" => "Cyprus", "CZ" => "Czech Republic", "CI" => "Côte d’Ivoire", "DK" => "Denmark", "DJ" => "Djibouti", "DM" => "Dominica", "DO" => "Dominican Republic", "EC" => "Ecuador", "EG" => "Egypt", "SV" => "El Salvador", "GQ" => "Equatorial Guinea", "ER" => "Eritrea", "EE" => "Estonia", "ET" => "Ethiopia", "FK" => "Falkland Islands", "FO" => "Faroe Islands", "FJ" => "Fiji", "FI" => "Finland", "FR" => "France", "GF" => "French Guiana", "PF" => "French Polynesia", "TF" => "French Southern Territories", "GA" => "Gabon", "GM" => "Gambia", "GE" => "Georgia", "DE" => "Germany", "GH" => "Ghana", "GI" => "Gibraltar", "GR" => "Greece", "GL" => "Greenland", "GD" => "Grenada", "GP" => "Guadeloupe", "GU" => "Guam", "GT" => "Guatemala", "GN" => "Guinea", "GW" => "Guinea-Bissau", "GY" => "Guyana", "HT" => "Haiti", "HM" => "Heard Island and McDonald Islands", "HN" => "Honduras", "HK" => "Hong Kong SAR China", "HU" => "Hungary", "IS" => "Iceland", "IN" => "India", "ID" => "Indonesia", "IR" => "Iran", "IQ" => "Iraq", "IE" => "Ireland", "IL" => "Israel", "IT" => "Italy", "JM" => "Jamaica", "JP" => "Japan", "JO" => "Jordan", "KZ" => "Kazakhstan", "KE" => "Kenya", "KI" => "Kiribati", "KW" => "Kuwait", "KG" => "Kyrgyzstan", "LA" => "Laos", "LV" => "Latvia", "LB" => "Lebanon", "LS" => "Lesotho", "LR" => "Liberia", "LY" => "Libya", "LI" => "Liechtenstein", "LT" => "Lithuania", "LU" => "Luxembourg", "MO" => "Macau SAR China", "MK" => "Macedonia", "MG" => "Madagascar", "MW" => "Malawi", "MY" => "Malaysia", "MV" => "Maldives", "ML" => "Mali", "MT" => "Malta", "MH" => "Marshall Islands", "MQ" => "Martinique", "MR" => "Mauritania", "MU" => "Mauritius", "YT" => "Mayotte", "MX" => "Mexico", "FM" => "Micronesia", "MD" => "Moldova", "MC" => "Monaco", "MN" => "Mongolia", "ME" => "Montenegro", "MS" => "Montserrat", "MA" => "Morocco", "MZ" => "Mozambique", "MM" => "Myanmar [Burma]", "NA" => "Namibia", "NR" => "Nauru", "NP" => "Nepal", "NL" => "Netherlands", "AN" => "Netherlands Antilles", "NC" => "New Caledonia", "NZ" => "New Zealand", "NI" => "Nicaragua", "NE" => "Niger", "NG" => "Nigeria", "NU" => "Niue", "NF" => "Norfolk Island", "KP" => "North Korea", "MP" => "Northern Mariana Islands", "NO" => "Norway", "OM" => "Oman", "PK" => "Pakistan", "PW" => "Palau", "PS" => "Palestinian Territories", "PA" => "Panama", "PG" => "Papua New Guinea", "PY" => "Paraguay", "PE" => "Peru", "PH" => "Philippines", "PN" => "Pitcairn Islands", "PL" => "Poland", "PT" => "Portugal", "PR" => "Puerto Rico", "QA" => "Qatar", "RO" => "Romania", "RU" => "Russia", "RW" => "Rwanda", "RE" => "R?ion", "SH" => "Saint Helena", "KN" => "Saint Kitts and Nevis", "LC" => "Saint Lucia", "PM" => "Saint Pierre and Miquelon", "VC" => "Saint Vincent and the Grenadines", "WS" => "Samoa", "SM" => "San Marino", "SA" => "Saudi Arabia", "SN" => "Senegal", "RS" => "Serbia", "CS" => "Serbia and Montenegro", "SC" => "Seychelles", "SL" => "Sierra Leone", "SG" => "Singapore", "SK" => "Slovakia", "SI" => "Slovenia", "SB" => "Solomon Islands", "SO" => "Somalia", "ZA" => "South Africa", "GS" => "South Georgia and the South Sandwich Islands", "KR" => "South Korea", "ES" => "Spain", "LK" => "Sri Lanka", "SD" => "Sudan", "SR" => "Suriname", "SJ" => "Svalbard and Jan Mayen", "SZ" => "Swaziland", "SE" => "Sweden", "CH" => "Switzerland", "SY" => "Syria", "ST" => "S?Tom?nd Pr?ipe", "TW" => "Taiwan", "TJ" => "Tajikistan", "TZ" => "Tanzania", "TH" => "Thailand", "TL" => "Timor-Leste", "TG" => "Togo", "TK" => "Tokelau", "TO" => "Tonga", "TT" => "Trinidad and Tobago", "TN" => "Tunisia", "TR" => "Turkey", "TM" => "Turkmenistan", "TC" => "Turks and Caicos Islands", "TV" => "Tuvalu", "UM" => "U.S. Minor Outlying Islands", "VI" => "U.S. Virgin Islands", "UG" => "Uganda", "UA" => "Ukraine", "SU" => "Union of Soviet Socialist Republics", "AE" => "United Arab Emirates", "GB" => "United Kingdom", "US" => "United States", "UY" => "Uruguay", "UZ" => "Uzbekistan", "VU" => "Vanuatu", "VA" => "Vatican City", "VE" => "Venezuela", "VN" => "Vietnam", "WF" => "Wallis and Futuna", "EH" => "Western Sahara", "YE" => "Yemen", "ZM" => "Zambia", "ZW" => "Zimbabwe");
}

function getCountryName($code)
{
    $countries = getCountries();
    return $countries[$code] ?? $code;
}

function lang($key, $default = null): string
{
    return Language::get($key, $default);
}

function setting($key, $default = null)
{
    return Setting::getValue($key, $default);
}

function getAvailableLanguages(): array
{
    return Language::getAvailableLanguages();
}

function getCurrentLanguage(): string
{
    return Language::getCurrentLanguage();
}

function uploadFile($file, $directory, $resize = null): false|string
{
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Content-based MIME type validation
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }

    // Verify it's actually an image
    if (!getimagesize($file['tmp_name'])) {
        return false;
    }
    
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    // Whitelist extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($extension), $allowedExtensions)) {
        return false;
    }

    $filename = uniqid() . '.' . $extension;
    $path = __DIR__ . '/../../../public/uploads/' . $directory;
    
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


function validatePort($port, $default = 25565)
{
    $port = (int) $port;
    if ($port < 1 || $port > 65535) {
        return $default;
    }
    return $port;
}


function resizeImage($source, $destination, $width, $height): bool
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

function auth()
{
    return Auth::user();
}

function isLoggedIn(): bool
{
    return (bool) Auth::user();
}

function isAdmin(): bool
{
    return Auth::isAdmin();
}

function isOwner(): bool
{
    return Auth::isOwner();
}

function rateLimitCheck($route, $method = 'GET'): bool
{
    return RateLimit::getInstance()->checkRequest($route, $method);
}

function rateLimitRetryAfter($route, $method = 'GET')
{
    return RateLimit::getInstance()->getRetryAfter($route, $method);
}

function ensureDirectoryExists($path, $permissions = 0755): void
{
    if (!is_dir($path)) {
        mkdir($path, $permissions, true);
    }
}

function seo(): string
{
    return SEO::class;
}

function setTitle($title): void
{
    SEO::setTitle($title);
}

function setDescription($description): void
{
    SEO::setDescription($description);
}

function setKeywords($keywords): void
{
    SEO::setKeywords($keywords);
}

function setCanonical($url): void
{
    SEO::setCanonical($url);
}

function setRobots($robots): void
{
    SEO::setRobots($robots);
}

function renderMetaTags(): string
{
    return SEO::renderMetaTags();
}

function renderFaviconTags(): string
{
    $settings = Setting::get();
    if (empty($settings->favicon_source)) {
        return '';
    }

    $version = (int)($settings->favicon_version ?? 1);
    $assetVersion = static fn ($path) => htmlspecialchars(asset($path) . '?v=' . $version, ENT_QUOTES, 'UTF-8');
    $urlVersion = static fn ($path) => htmlspecialchars(url($path) . '?v=' . $version, ENT_QUOTES, 'UTF-8');

    return implode("\n", [
        '<link rel="icon" href="' . $urlVersion('/favicon.ico') . '" sizes="any">',
        '<link rel="icon" type="image/png" sizes="16x16" href="' . $assetVersion('favicons/favicon-16x16.png') . '">',
        '<link rel="icon" type="image/png" sizes="32x32" href="' . $assetVersion('favicons/favicon-32x32.png') . '">',
        '<link rel="apple-touch-icon" sizes="180x180" href="' . $assetVersion('favicons/apple-touch-icon.png') . '">',
        '<link rel="manifest" href="' . $urlVersion('/site.webmanifest') . '">',
    ]);
}

/**
 * Jodit Editor Integration Helpers
 */

function joditAssets(): void
{
    static $included = false;
    if ($included) return;
    $included = true;
    
    echo '<script src="' . asset('js/jodit-helper.js') . '"></script>' . "\n";
    echo '<script>' . "\n";
    echo 'document.addEventListener("DOMContentLoaded", function() {' . "\n";
    echo '    // Set editor language from PHP.' . "\n";
    echo '    window.joditHelper.setLanguage("' . lang('_jodit_code', 'en') . '");' . "\n";
    echo '});' . "\n";
    echo '</script>' . "\n";
}

function joditInit($selector, $type = 'page', $options = [], $placeholder = null): string
{
    $placeholderText = $placeholder ?: lang('content_placeholder', 'Write your content here...');
    
    $jsOptions = '';
    if (!empty($options)) {
        $jsOptions = ', ' . json_encode($options);
    }

    $initMethod = match ($type) {
        'blog' => 'initBlogEditor',
        'page' => 'initPageEditor',
        default => 'init',
    };
    
    return "window.joditHelper.{$initMethod}('{$selector}', '{$placeholderText}'{$jsOptions});";
}

function joditValidation($selector, $minLength = 10, $errorMessage = null): string
{
    $errorMsg = $errorMessage ?: lang('blog_content_required', 'Content must be at least 10 characters long');
    
    return "
    const validation = window.joditHelper.validateContent('{$selector}', {$minLength});
    if (!validation.valid) {
        e.preventDefault();
        alert('{$errorMsg}');
        return false;
    }";
}

function joditScript($editors = [], $onReady = ''): string
{
    $script = '<script>' . "\n";
    $script .= 'document.addEventListener("DOMContentLoaded", async function() {' . "\n";
    
    foreach ($editors as $config) {
        $selector = isset($config['selector']) ? $config['selector'] : '';
        $type = $config['type'] ?? 'page';
        $options = $config['options'] ?? [];
        $placeholder = $config['placeholder'] ?? null;
        $variable = $config['variable'] ?? null;
        
        $initCode = joditInit($selector, $type, $options, $placeholder);
        
        if ($variable) {
            $script .= "    const {$variable} = await {$initCode}\n";
        } else {
            $script .= "    await {$initCode}\n";
        }
    }
    
    if ($onReady) {
        $script .= "\n    " . $onReady . "\n";
    }
    
    $script .= '});' . "\n";
    $script .= '</script>' . "\n";
    
    return $script;
}

/**
 * Validates if a host/IP is safe for outbound connections (prevents SSRF).
 * Blocks private/internal IP ranges.
 */
function resolveSafeHostIp($host): ?string
{
    $host = is_string($host) ? trim($host) : '';
    if ($host === '') {
        return null;
    }

    if (strpbrk($host, " \t\r\n/\\@#?") !== false) {
        return null;
    }

    $reservedRanges = [
        '127.0.0.0/8',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '169.254.0.0/16',
        '0.0.0.0/8',
        '100.64.0.0/10',
        '192.0.0.0/24',
        '192.0.2.0/24',
        '198.18.0.0/15',
        '198.51.100.0/24',
        '203.0.113.0/24',
        '224.0.0.0/4',
        '240.0.0.0/4',
        '::1/128',
        'fc00::/7',
        'fe80::/10',
    ];

    $ips = [];

    if (filter_var($host, FILTER_VALIDATE_IP)) {
        $ips[] = $host;
    } else {
        $records = @dns_get_record($host, DNS_A | DNS_AAAA);
        if (!is_array($records) || empty($records)) {
            return null;
        }

        foreach ($records as $record) {
            if (!is_array($record) || empty($record['type'])) {
                continue;
            }

            if ($record['type'] === 'A' && !empty($record['ip']) && filter_var($record['ip'], FILTER_VALIDATE_IP)) {
                $ips[] = $record['ip'];
                continue;
            }

            if ($record['type'] === 'AAAA' && !empty($record['ipv6']) && filter_var($record['ipv6'], FILTER_VALIDATE_IP)) {
                $ips[] = $record['ipv6'];
                continue;
            }
        }
    }

    $ips = array_values(array_unique($ips));
    if (empty($ips)) {
        return null;
    }

    foreach ($ips as $ip) {
        foreach ($reservedRanges as $range) {
            if (ipInInRange($ip, $range)) {
                return null;
            }
        }
    }

    foreach ($ips as $ip) {
        if (!str_contains($ip, ':')) {
            return $ip;
        }
    }

    return $ips[0];
}

function isSafeHost($host): bool
{
    return resolveSafeHostIp($host) !== null;
}

/**
 * Check if an IP address is within a specified CIDR range.
 */
function ipInInRange($ip, $range): bool
{
    if (str_contains($range, '/')) {
        [$subnet, $bits] = explode('/', $range, 2);
        $bits = (int) $bits;

        $ipIsV6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        $subnetIsV6 = filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;

        if ($ipIsV6 !== $subnetIsV6) {
            return false;
        }

        if ($subnetIsV6) {
            if ($bits < 0 || $bits > 128) {
                return false;
            }

            $ipBinary = inet_pton($ip);
            $subnetBinary = inet_pton($subnet);
            if ($ipBinary === false || $subnetBinary === false) {
                return false;
            }

            $bytesFull = intdiv($bits, 8);
            $bitsLeft = $bits % 8;

            for ($i = 0; $i < $bytesFull; $i++) {
                if ($ipBinary[$i] !== $subnetBinary[$i]) {
                    return false;
                }
            }

            if ($bitsLeft > 0) {
                $mask = ~(0xff >> $bitsLeft);
                if ((ord($ipBinary[$bytesFull]) & $mask) !== (ord($subnetBinary[$bytesFull]) & $mask)) {
                    return false;
                }
            }

            return true;
        }

        if ($bits < 0 || $bits > 32) {
            return false;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        if ($bits === 0) {
            return true;
        }

        $mask = -1 << (32 - $bits);
        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
    
    return $ip === $range;
}
