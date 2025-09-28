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
	
	$list = array("AF" => "Afghanistan", "AL" => "Albania", "DZ" => "Algeria", "AS" => "American Samoa", "AD" => "Andorra", "AO" => "Angola", "AI" => "Anguilla", "AQ" => "Antarctica", "AG" => "Antigua and Barbuda", "AR" => "Argentina", "AM" => "Armenia", "AW" => "Aruba", "AU" => "Australia", "AT" => "Austria", "AZ" => "Azerbaijan", "AX" => "Åland Islands", "BS" => "Bahamas", "BH" => "Bahrain", "BD" => "Bangladesh", "BB" => "Barbados", "BY" => "Belarus", "BE" => "Belgium", "BZ" => "Belize", "BJ" => "Benin", "BM" => "Bermuda", "BT" => "Bhutan", "BO" => "Bolivia", "BA" => "Bosnia and Herzegovina", "BW" => "Botswana", "BV" => "Bouvet Island", "BR" => "Brazil", "BQ" => "British Antarctic Territory", "IO" => "British Indian Ocean Territory", "VG" => "British Virgin Islands", "BN" => "Brunei", "BG" => "Bulgaria", "BF" => "Burkina Faso", "BI" => "Burundi", "KH" => "Cambodia", "CM" => "Cameroon", "CA" => "Canada", "CV" => "Cape Verde", "KY" => "Cayman Islands", "CF" => "Central African Republic", "TD" => "Chad", "CL" => "Chile", "CN" => "China", "CX" => "Christmas Island", "CC" => "Cocos [Keeling] Islands", "CO" => "Colombia", "KM" => "Comoros", "CG" => "Congo - Brazzaville", "CD" => "Congo - Kinshasa", "CK" => "Cook Islands", "CR" => "Costa Rica", "HR" => "Croatia", "CU" => "Cuba", "CY" => "Cyprus", "CZ" => "Czech Republic", "CI" => "Côte d’Ivoire", "DK" => "Denmark", "DJ" => "Djibouti", "DM" => "Dominica", "DO" => "Dominican Republic", "EC" => "Ecuador", "EG" => "Egypt", "SV" => "El Salvador", "GQ" => "Equatorial Guinea", "ER" => "Eritrea", "EE" => "Estonia", "ET" => "Ethiopia", "FK" => "Falkland Islands", "FO" => "Faroe Islands", "FJ" => "Fiji", "FI" => "Finland", "FR" => "France", "GF" => "French Guiana", "PF" => "French Polynesia", "TF" => "French Southern Territories", "GA" => "Gabon", "GM" => "Gambia", "GE" => "Georgia", "DE" => "Germany", "GH" => "Ghana", "GI" => "Gibraltar", "GR" => "Greece", "GL" => "Greenland", "GD" => "Grenada", "GP" => "Guadeloupe", "GU" => "Guam", "GT" => "Guatemala", "GN" => "Guinea", "GW" => "Guinea-Bissau", "GY" => "Guyana", "HT" => "Haiti", "HM" => "Heard Island and McDonald Islands", "HN" => "Honduras", "HK" => "Hong Kong SAR China", "HU" => "Hungary", "IS" => "Iceland", "IN" => "India", "ID" => "Indonesia", "IR" => "Iran", "IQ" => "Iraq", "IE" => "Ireland", "IL" => "Israel", "IT" => "Italy", "JM" => "Jamaica", "JP" => "Japan", "JO" => "Jordan", "KZ" => "Kazakhstan", "KE" => "Kenya", "KI" => "Kiribati", "KW" => "Kuwait", "KG" => "Kyrgyzstan", "LA" => "Laos", "LV" => "Latvia", "LB" => "Lebanon", "LS" => "Lesotho", "LR" => "Liberia", "LY" => "Libya", "LI" => "Liechtenstein", "LT" => "Lithuania", "LU" => "Luxembourg", "MO" => "Macau SAR China", "MK" => "Macedonia", "MG" => "Madagascar", "MW" => "Malawi", "MY" => "Malaysia", "MV" => "Maldives", "ML" => "Mali", "MT" => "Malta", "MH" => "Marshall Islands", "MQ" => "Martinique", "MR" => "Mauritania", "MU" => "Mauritius", "YT" => "Mayotte", "MX" => "Mexico", "FM" => "Micronesia", "MD" => "Moldova", "MC" => "Monaco", "MN" => "Mongolia", "ME" => "Montenegro", "MS" => "Montserrat", "MA" => "Morocco", "MZ" => "Mozambique", "MM" => "Myanmar [Burma]", "NA" => "Namibia", "NR" => "Nauru", "NP" => "Nepal", "NL" => "Netherlands", "AN" => "Netherlands Antilles", "NC" => "New Caledonia", "NZ" => "New Zealand", "NI" => "Nicaragua", "NE" => "Niger", "NG" => "Nigeria", "NU" => "Niue", "NF" => "Norfolk Island", "KP" => "North Korea", "MP" => "Northern Mariana Islands", "NO" => "Norway", "OM" => "Oman", "PK" => "Pakistan", "PW" => "Palau", "PS" => "Palestinian Territories", "PA" => "Panama", "PG" => "Papua New Guinea", "PY" => "Paraguay", "PE" => "Peru", "PH" => "Philippines", "PN" => "Pitcairn Islands", "PL" => "Poland", "PT" => "Portugal", "PR" => "Puerto Rico", "QA" => "Qatar", "RO" => "Romania", "RU" => "Russia", "RW" => "Rwanda", "RE" => "R?ion", "SH" => "Saint Helena", "KN" => "Saint Kitts and Nevis", "LC" => "Saint Lucia", "PM" => "Saint Pierre and Miquelon", "VC" => "Saint Vincent and the Grenadines", "WS" => "Samoa", "SM" => "San Marino", "SA" => "Saudi Arabia", "SN" => "Senegal", "RS" => "Serbia", "CS" => "Serbia and Montenegro", "SC" => "Seychelles", "SL" => "Sierra Leone", "SG" => "Singapore", "SK" => "Slovakia", "SI" => "Slovenia", "SB" => "Solomon Islands", "SO" => "Somalia", "ZA" => "South Africa", "GS" => "South Georgia and the South Sandwich Islands", "KR" => "South Korea", "ES" => "Spain", "LK" => "Sri Lanka", "SD" => "Sudan", "SR" => "Suriname", "SJ" => "Svalbard and Jan Mayen", "SZ" => "Swaziland", "SE" => "Sweden", "CH" => "Switzerland", "SY" => "Syria", "ST" => "S?Tom?nd Pr?ipe", "TW" => "Taiwan", "TJ" => "Tajikistan", "TZ" => "Tanzania", "TH" => "Thailand", "TL" => "Timor-Leste", "TG" => "Togo", "TK" => "Tokelau", "TO" => "Tonga", "TT" => "Trinidad and Tobago", "TN" => "Tunisia", "TR" => "Turkey", "TM" => "Turkmenistan", "TC" => "Turks and Caicos Islands", "TV" => "Tuvalu", "UM" => "U.S. Minor Outlying Islands", "VI" => "U.S. Virgin Islands", "UG" => "Uganda", "UA" => "Ukraine", "SU" => "Union of Soviet Socialist Republics", "AE" => "United Arab Emirates", "GB" => "United Kingdom", "US" => "United States", "UY" => "Uruguay", "UZ" => "Uzbekistan", "VU" => "Vanuatu", "VA" => "Vatican City", "VE" => "Venezuela", "VN" => "Vietnam", "WF" => "Wallis and Futuna", "EH" => "Western Sahara", "YE" => "Yemen", "ZM" => "Zambia", "ZW" => "Zimbabwe");

	
    return $list;
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
