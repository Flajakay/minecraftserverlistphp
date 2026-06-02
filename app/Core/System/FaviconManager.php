<?php

namespace App\Core\System;

class FaviconManager
{
    private const MAX_SIZE = 2097152;
    private const MIN_DIMENSION = 512;
    private const OUTPUTS = [
        'favicon-16x16.png' => [16, 16],
        'favicon-32x32.png' => [32, 32],
        'apple-touch-icon.png' => [180, 180],
        'android-chrome-192x192.png' => [192, 192],
        'android-chrome-512x512.png' => [512, 512],
    ];

    private string $publicDir;
    private string $faviconDir;

    public function __construct(?string $publicDir = null)
    {
        $this->publicDir = $publicDir ?: dirname(__DIR__, 3) . '/public';
        $this->faviconDir = $this->publicDir . '/assets/favicons';
    }

    public function processUpload(array $file, string $siteName): array
    {
        $validation = $this->validateUpload($file);
        if (!$validation['success']) {
            return $validation;
        }
        if (!empty($validation['skipped'])) {
            return $validation;
        }

        ensureDirectoryExists($this->faviconDir);

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $sourceFilename = 'favicon-source.' . ($extension === 'jpeg' ? 'jpg' : $extension);
        $tmpDir = $this->faviconDir . '/.tmp-' . bin2hex(random_bytes(8));

        if (!mkdir($tmpDir, 0755, true) && !is_dir($tmpDir)) {
            return ['success' => false, 'error' => lang('favicon_generation_failed')];
        }

        $tmpSource = $tmpDir . '/' . $sourceFilename;

        try {
            if (!move_uploaded_file($file['tmp_name'], $tmpSource)) {
                return ['success' => false, 'error' => lang('favicon_upload_failed')];
            }

            foreach (self::OUTPUTS as $filename => [$width, $height]) {
                if (!$this->resizeToPng($tmpSource, $tmpDir . '/' . $filename, $width, $height)) {
                    return ['success' => false, 'error' => lang('favicon_generation_failed')];
                }
            }

            if (!$this->writeIco($tmpDir . '/favicon-32x32.png', $tmpDir . '/favicon.ico')) {
                return ['success' => false, 'error' => lang('favicon_generation_failed')];
            }

            if (!$this->writeManifest($tmpDir . '/site.webmanifest', $siteName)) {
                return ['success' => false, 'error' => lang('favicon_generation_failed')];
            }

            $files = array_merge(array_keys(self::OUTPUTS), ['favicon.ico', 'site.webmanifest', $sourceFilename]);
            foreach ($files as $filename) {
                if (!rename($tmpDir . '/' . $filename, $this->faviconDir . '/' . $filename)) {
                    return ['success' => false, 'error' => lang('favicon_generation_failed')];
                }
            }

            return [
                'success' => true,
                'source' => $sourceFilename,
            ];
        } finally {
            $this->deleteDirectory($tmpDir);
        }
    }

    private function validateUpload(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['success' => true, 'skipped' => true];
        }

        if (($file['error'] ?? null) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => lang('favicon_upload_failed')];
        }

        if (($file['size'] ?? 0) > self::MAX_SIZE) {
            return ['success' => false, 'error' => lang('favicon_too_large')];
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'], true)) {
            return ['success' => false, 'error' => lang('favicon_invalid_type')];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'], true)) {
            return ['success' => false, 'error' => lang('favicon_invalid_type')];
        }

        $imageInfo = getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            return ['success' => false, 'error' => lang('favicon_invalid_type')];
        }

        if ($imageInfo[0] !== $imageInfo[1]) {
            return ['success' => false, 'error' => lang('favicon_not_square')];
        }

        if ($imageInfo[0] < self::MIN_DIMENSION || $imageInfo[1] < self::MIN_DIMENSION) {
            return ['success' => false, 'error' => lang('favicon_too_small')];
        }

        return ['success' => true];
    }

    private function resizeToPng(string $source, string $destination, int $width, int $height): bool
    {
        $sourceImage = $this->createImage($source);
        if (!$sourceImage) {
            return false;
        }

        $targetImage = imagecreatetruecolor($width, $height);
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        imagefill($targetImage, 0, 0, imagecolorallocatealpha($targetImage, 255, 255, 255, 127));

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        $result = imagepng($targetImage, $destination, 8);

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return $result;
    }

    private function createImage(string $path)
    {
        $imageInfo = getimagesize($path);
        if (!$imageInfo) {
            return false;
        }

        return match ($imageInfo['mime']) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/gif' => imagecreatefromgif($path),
            default => false,
        };
    }

    private function writeIco(string $pngPath, string $icoPath): bool
    {
        $pngData = file_get_contents($pngPath);
        if ($pngData === false) {
            return false;
        }

        $header = pack('vvv', 0, 1, 1);
        $directory = pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($pngData), 22);

        return file_put_contents($icoPath, $header . $directory . $pngData) !== false;
    }

    private function writeManifest(string $path, string $siteName): bool
    {
        $manifest = [
            'name' => $siteName,
            'short_name' => $siteName,
            'icons' => [
                [
                    'src' => '/assets/favicons/android-chrome-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => '/assets/favicons/android-chrome-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
            'theme_color' => '#ffffff',
            'background_color' => '#ffffff',
            'display' => 'standalone',
        ];

        return file_put_contents($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (scandir($path) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $file = $path . '/' . $item;
            is_dir($file) ? $this->deleteDirectory($file) : @unlink($file);
        }

        @rmdir($path);
    }
}
