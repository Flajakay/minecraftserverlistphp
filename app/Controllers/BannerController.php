<?php

namespace App\Controllers;

use App\Models\Server;

class BannerController
{
    public function generate()
    {
        $serverId = $_GET['server_id'] ?? 0;
        $background = $_GET['background'] ?? 'default';
        $textColor = $_GET['text_color'] ?? '000000';
        $borderColor = $_GET['border_color'] ?? '000000';

        $server = Server::find($serverId);
        if (!$server) {
            http_response_code(404);
            exit('Server not found');
        }

        $bannerPath = dirname(__DIR__, 2) . '/public/assets/banners/';
        $backgroundFile = $bannerPath . $background . '.jpg';
        
        if (!file_exists($backgroundFile)) {
            $backgroundFile = $bannerPath . 'default.jpg';
        }

        if (!file_exists($backgroundFile)) {
            http_response_code(404);
            exit('Background not found');
        }

        $image = imagecreatefromjpeg($backgroundFile);
        if (!$image) {
            http_response_code(500);
            exit('Failed to create image');
        }

        $countryImagePath = dirname(__DIR__, 2) . '/public/assets/flags/' . $server->country . '.png';
        $countryImage = null;
        if (file_exists($countryImagePath)) {
            $countryImage = imagecreatefrompng($countryImagePath);
        }

        $fontPath = dirname(__DIR__, 2) . '/public/assets/fonts/';
        $bigFont = $fontPath . 'verdana.ttf';
        $smallFont = $fontPath . 'arial.ttf';

        if (!file_exists($bigFont)) {
            $bigFont = null;
        }
        if (!file_exists($smallFont)) {
            $smallFont = null;
        }

        $black = imagecolorallocate($image, 0, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $red = imagecolorallocate($image, 255, 0, 0);

        $textColorRgb = $this->hexToRgb($textColor);
        $textColorAllocated = imagecolorallocate($image, $textColorRgb['r'], $textColorRgb['g'], $textColorRgb['b']);

        $borderColorRgb = $this->hexToRgb($borderColor);
        $borderColorAllocated = imagecolorallocate($image, $borderColorRgb['r'], $borderColorRgb['g'], $borderColorRgb['b']);

        if ($bigFont) {
            imagettftext($image, 14, 0, 8, 22, $textColorAllocated, $bigFont, $server->name);
            imagettftext($image, 12, 0, 50, 47, $textColorAllocated, $smallFont ?: $bigFont, $server->address . ':' . $server->port);
        } else {
            imagestring($image, 5, 8, 8, $server->name, $textColorAllocated);
            imagestring($image, 3, 50, 30, $server->address . ':' . $server->port, $textColorAllocated);
        }

        imagefilledellipse($image, 16, 42, 12, 12, $server->status ? $green : $red);

        if ($countryImage) {
            imagecopy($image, $countryImage, 30, 38, 0, 0, 16, 11);
            imagedestroy($countryImage);
        }

        if ($server->status && $smallFont) {
            imagettftext($image, 14, 0, 380, 47, $textColorAllocated, $smallFont, $server->players . '/' . $server->max_players);
        } elseif ($server->status) {
            imagestring($image, 3, 380, 30, $server->players . '/' . $server->max_players, $textColorAllocated);
        }

        $width = imagesx($image) - 1;
        $height = imagesy($image) - 1;
        imageline($image, 0, 0, 0, $height, $borderColorAllocated);
        imageline($image, 0, 0, $width, 0, $borderColorAllocated);
        imageline($image, $width, 0, $width, $height, $borderColorAllocated);
        imageline($image, 0, $height, $width, $height, $borderColorAllocated);

        header('Content-Type: image/png');
        header('Cache-Control: max-age=3600');
        imagepng($image);
        imagedestroy($image);
    }

    private function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } elseif (strlen($hex) == 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        } else {
            $r = $g = $b = 0;
        }

        return ['r' => $r, 'g' => $g, 'b' => $b];
    }
}
