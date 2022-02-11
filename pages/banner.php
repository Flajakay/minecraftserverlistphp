<?php
/* Including the core */
include '../core/init.php';

/* Get the server info */
$server = new Server('', '', $_GET['server_id']);

/* Creating the image */
$path = '../template/images/banners/';
$background = $path . 'default';
if(file_exists($path . $_GET['background']) . '.jpg') $background = $path . $_GET['background'];
$image = imagecreatefromjpeg($background . '.jpg');

/* Defining some variables */
$country_image_location = '../template/images/locations/' . $server->data->country_code . '.png';
$country_image = imagecreatefrompng($country_image_location);

$big_font = '../template/fonts/verdana.ttf';
$small_font = '../template/fonts/TrebuchetMS.ttf';

$black = imagecolorallocate($image, 0, 0, 0);
$green = imagecolorallocate($image, 0, 255,0);
$red = imagecolorallocate($image, 255, 0, 0);

/* Change the default colors if there is a $_GET variable */
if(isset($_GET['border_color'])) {
	$border_color = HexToRGB($_GET['border_color']);
	$border_color = imagecolorallocate($image, $border_color['r'], $border_color['g'], $border_color['b']);
} else $border_color = imagecolorallocate($image, 0, 0, 0);

if(isset($_GET['text_color'])) {
	$text_color = HexToRGB($_GET['text_color']);
	$text_color = imagecolorallocate($image, $text_color['r'], $text_color['g'], $text_color['b']);
} else $text_color = imagecolorallocate($image, 0, 0, 0);

/* Start displaying text on the image */
pretty_text_ttf($image, 14, 0, 8, 22, $text_color, $big_font, $server->data->name);

/* Server address + port */
pretty_text_ttf($image, 12, 0, 50, 47, $text_color, $small_font, $server->data->address . ':' . $server->data->connection_port);

/* Online or not icon */
imagefilledellipse($image, 16, 42, 12, 12, (($server->data->status) ? $green : $red));

/* Country Icon */
imagecopy($image, $country_image, 30, 38, 0, 0, 16, 11);

/* Online players if any */
if($server->data->status) pretty_text_ttf($image, 14, 0, 380, 47, $text_color, $small_font, $server->data->online_players . '/' . $server->data->maximum_online_players);

/* Border of the iamge */
$x = 0;
$y = 0;
$w = imagesx($image) - 1;
$h = imagesy($image) - 1;
imageline($image, $x,$y,$x,$y+$h,$border_color);
imageline($image, $x,$y,$x+$w,$y,$border_color); 
imageline($image, $x+$w,$y,$x+$w,$y+$h,$border_color);
imageline($image, $x,$y+$h,$x+$w,$y+$h,$border_color);

/* Setting the type of the output to an image */
header('Content-type: image/png');

/* Create the actual image */
imagepng($image);

/* Free the resources */
imagedestroy($image);

/* Function to add black border to the text */
function pretty_text_ttf($image, $fontsize, $angle, $x, $y, $color, $font, $string, $outline = false) {
	global $image;
	$black  = imagecolorallocate($image, 0, 0, 0);

	/* Black border */
	if($outline){
		imagettftext($image, $fontsize, $angle, $x - 1, $y - 1, $black, $font, $string);
		imagettftext($image, $fontsize, $angle, $x - 1, $y, $black, $font, $string);
		imagettftext($image, $fontsize, $angle, $x - 1, $y + 1, $black, $font, $string);
		imagettftext($image, $fontsize, $angle, $x, $y - 1, $black, $font, $string);
		imagettftext($image, $fontsize, $angle, $x, $y + 1, $black, $font, $string);
		imagettftext($image, $fontsize, $angle, $x + 1, $y - 1, $black, $font, $string);
		imagettftext($image, $fontsize, $angle, $x + 1, $y, $black, $font, $string);
		imagettftext($image, $fontsize, $angle, $x + 1, $y + 1, $black, $font, $string);
	}

	/* The actual text */
	imagettftext($image, $fontsize, $angle, $x, $y, $color, $font, $string);
}

/* Function to convert hex color codes to RGB */
function HexToRGB($hex) {
	$hex = str_replace("#", "", $hex);
	$color = array();

	if(strlen($hex) == 3) {
		$color['r'] = hexdec(substr($hex, 0, 1) . $r);
		$color['g'] = hexdec(substr($hex, 1, 1) . $g);
		$color['b'] = hexdec(substr($hex, 2, 1) . $b);
	}
	else if(strlen($hex) == 6) {
		$color['r'] = hexdec(substr($hex, 0, 2));
		$color['g'] = hexdec(substr($hex, 2, 2));
		$color['b'] = hexdec(substr($hex, 4, 2));
	}
	return $color;
}
?>