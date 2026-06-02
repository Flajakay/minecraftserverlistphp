<?php

namespace App\Core\Security;

use Stiphle\Storage\StorageInterface;
use Stiphle\Storage\LockWaitTimeoutException;

/**
 * File-based storage adapter for the Stiphle throttler.
 *
 * Implements Stiphle's StorageInterface to support rate limiting in environments
 * where in-memory extensions (like APCu) are not installed or are volatile (CLI).
 */
class FileStorage implements StorageInterface
{
    private string $storageDir;
    private int $lockWaitTimeout = 1000; // Milliseconds
    private array $lockHandles = [];

    public function __construct(?string $storageDir = null)
    {
        $this->storageDir = $storageDir ?: dirname(__DIR__, 3) . '/storage/framework/rate_limits';
        
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Set lock wait timeout in milliseconds.
     *
     * @param int $milliseconds
     */
    public function setLockWaitTimeout($milliseconds): void
    {
        $this->lockWaitTimeout = $milliseconds;
    }

    /**
     * Generate a safe, unique hashed filename for the rate limit key.
     */
    private function getSafeFilename(string $key): string
    {
        return $this->storageDir . '/' . sha1($key);
    }

    /**
     * Lock the storage for a given key.
     *
     * Uses PHP's native flock to safely prevent concurrent write race conditions.
     */
    public function lock($key): void
    {
        $lockFile = $this->getSafeFilename($key) . '.lock';
        $fp = fopen($lockFile, 'w+');
        if (!$fp) {
            throw new \RuntimeException("Unable to open rate limit lock file: {$lockFile}");
        }

        $start = microtime(true);
        // Attempt a non-blocking flock in a loop with sleep to enforce timeout
        while (!flock($fp, LOCK_EX | LOCK_NB)) {
            $passed = (microtime(true) - $start) * 1000;
            if ($passed > $this->lockWaitTimeout) {
                fclose($fp);
                throw new LockWaitTimeoutException("Rate limit lock timeout exceeded for key: {$key}");
            }
            usleep(5000); // Sleep 5ms
        }

        $this->lockHandles[$key] = $fp;
    }

    /**
     * Unlock the storage for a given key.
     */
    public function unlock($key): void
    {
        if (isset($this->lockHandles[$key])) {
            $fp = $this->lockHandles[$key];
            flock($fp, LOCK_UN);
            fclose($fp);
            unset($this->lockHandles[$key]);
            
            $lockFile = $this->getSafeFilename($key) . '.lock';
            if (file_exists($lockFile)) {
                @unlink($lockFile);
            }
        }
    }

    /**
     * Get the stored rate limit value for a key.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $dataFile = $this->getSafeFilename($key) . '.data';
        if (!file_exists($dataFile)) {
            return null;
        }

        $content = file_get_contents($dataFile);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (!$data || !isset($data['value'])) {
            return null;
        }

        return $data['value'];
    }

    /**
     * Store the rate limit value for a key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value): void
    {
        $dataFile = $this->getSafeFilename($key) . '.data';
        $data = [
            'value' => $value,
            'updated_at' => time()
        ];
        
        file_put_contents($dataFile, json_encode($data));
    }
}
