<?php

namespace App\Core;

use Stiphle\Throttle\LeakyBucket;
use Stiphle\Storage\Apcu;
use Exception;

class RateLimit
{
    private static $instance;
    private $throttle;
    private $config;

    private function __construct()
    {
        $this->config = require __DIR__ . '/../../config/ratelimit.php';
        
        // Simple APCu setup - perfect for single-server applications
        if (extension_loaded('apcu') && apcu_enabled()) {
            $storage = new Apcu();
            $this->throttle = new LeakyBucket();
            $this->throttle->setStorage($storage);
            
            $this->logDebug("Rate limiting initialized with APCu storage");
        } else {
            // Fallback - log the issue clearly
            $this->throttle = new LeakyBucket();
            $this->logError("APCu not available - rate limiting will NOT work. Enable APCu extension in PHP.");
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function checkRequest($route, $method = 'GET')
    {
        // Always check if we should bypass first
        if ($this->shouldBypass()) {
            $this->logDebug("Request bypassed", ['route' => $route, 'reason' => 'user_in_bypass_list']);
            return true;
        }

        $rule = $this->findMatchingRule($route);
        
        if (!$rule) {
            $this->logDebug("No matching rule found", ['route' => $route]);
            return true; 
        }

        $key = $this->generateKey($rule, $route);
        
        try {
            // Stiphle expects milliseconds for the window
            $waitTime = $this->throttle->throttle($key, $rule['limit'], $rule['window'] * 1000);
            $allowed = $waitTime === 0;
            
            $this->logDebug("Rate limit check", [
                'route' => $route,
                'key' => $key,
                'limit' => $rule['limit'],
                'window' => $rule['window'],
                'wait_time_ms' => $waitTime,
                'allowed' => $allowed
            ]);
            
            if (!$allowed && $this->config['logging']['log_violations']) {
                $this->logViolation($route, $rule, $key, $waitTime);
            }

            return $allowed;
            
        } catch (Exception $e) {
            $this->logError("Rate limiting error: " . $e->getMessage(), [
                'route' => $route, 
                'key' => $key,
                'exception' => get_class($e)
            ]);
            // Fail open - allow request if there's an error
            return true;
        }
    }

    private function shouldBypass()
    {
        $bypass = $this->config['bypass'];
        
        // Check IP bypass
        $clientIp = $this->getClientIp();
        if (in_array($clientIp, $bypass['ips'])) {
            return true;
        }

        // Check user role bypass
        if (function_exists('isLoggedIn') && isLoggedIn()) {
            $user = function_exists('auth') ? auth() : null;
            if ($user && isset($user->type) && in_array($user->type, $bypass['roles'])) {
                return true;
            }
        }

        return false;
    }

    private function findMatchingRule($route)
    {
        $rules = $this->config['rules'];
        
        // Check specific rules first (excluding 'general')
        foreach ($rules as $ruleName => $rule) {
            if ($ruleName === 'general') continue;
            
            foreach ($rule['routes'] as $pattern) {
                if ($this->routeMatches($route, $pattern)) {
                    $this->logDebug("Rule matched", [
                        'rule' => $ruleName, 
                        'pattern' => $pattern, 
                        'route' => $route
                    ]);
                    return $rule;
                }
            }
        }
        
        // Fall back to general rule
        $this->logDebug("Using general rule", ['route' => $route]);
        return $rules['general'] ?? null;
    }

    private function routeMatches($route, $pattern)
    {
        // Handle wildcard patterns
        if ($pattern === '*') {
            return true;
        }
        
        if (strpos($pattern, '*') !== false) {
            $pattern = str_replace('*', '.*', $pattern);
            return preg_match('#^' . $pattern . '$#', $route);
        }
        
        return $route === $pattern;
    }

    private function generateKey($rule, $route)
    {
        $keyType = $rule['key_type'];
        $parts = [];
        
        switch ($keyType) {
            case 'ip':
                $parts[] = 'ip:' . $this->getClientIp();
                break;
                
            case 'user':
                if (function_exists('isLoggedIn') && isLoggedIn()) {
                    $user = function_exists('auth') ? auth() : null;
                    $parts[] = 'user:' . ($user->id ?? 'unknown');
                } else {
                    $parts[] = 'ip:' . $this->getClientIp();
                }
                break;
                
            case 'combined':
                $parts[] = 'ip:' . $this->getClientIp();
                if (function_exists('isLoggedIn') && isLoggedIn()) {
                    $user = function_exists('auth') ? auth() : null;
                    $parts[] = 'user:' . ($user->id ?? 'unknown');
                }
                break;
        }
        
        $parts[] = 'route:' . $route;
        
        return implode('|', $parts);
    }

    private function getClientIp()
    {
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function logViolation($route, $rule, $key, $waitTime)
    {
        if (!$this->config['logging']['enabled']) {
            return;
        }

        $logFile = $this->config['logging']['log_file'];
        $logDir = dirname($logFile);
        
        if (function_exists('ensureDirectoryExists')) {
            ensureDirectoryExists($logDir);
        }

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'VIOLATION',
            'ip' => $this->getClientIp(),
            'user_id' => (function_exists('isLoggedIn') && isLoggedIn()) ? 
                        (function_exists('auth') ? auth()->id ?? null : null) : null,
            'route' => $route,
            'key' => $key,
            'limit' => $rule['limit'],
            'window' => $rule['window'],
            'wait_time_ms' => $waitTime,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];

        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    private function logDebug($message, $context = [])
    {
        if (!$this->config['logging']['enabled']) {
            return;
        }

        $logFile = $this->config['logging']['log_file'];
        $logDir = dirname($logFile);
        
        if (function_exists('ensureDirectoryExists')) {
            ensureDirectoryExists($logDir);
        }

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'DEBUG',
            'message' => $message,
            'context' => $context,
            'ip' => $this->getClientIp()
        ];

        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    private function logError($message, $context = [])
    {
        $logFile = $this->config['logging']['log_file'];
        $logDir = dirname($logFile);
        
        if (function_exists('ensureDirectoryExists')) {
            ensureDirectoryExists($logDir);
        }

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'ERROR',
            'message' => $message,
            'context' => $context,
            'ip' => $this->getClientIp()
        ];

        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    public function getRetryAfter($route, $method = 'GET')
    {
        $rule = $this->findMatchingRule($route);
        return $rule ? $rule['window'] : 3600;
    }

    public function handleRateLimitExceeded($route)
    {
        $config = $this->config['responses']['too_many_requests'];
        $retryAfter = $this->getRetryAfter($route);
        $minutes = ceil($retryAfter / 60);
        
        // For regular web requests, use flash message and redirect
        flash('error', $config['message'] . ' Please wait approximately ' . $minutes . ' minute(s) before trying again.');
        
        // Get referer or fallback to home
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/';
        
        // Don't redirect back to the same rate-limited route
        if (strpos($redirectUrl, $route) !== false) {
            $redirectUrl = '/';
        }
        
        redirect($redirectUrl);
    }

    public function middleware($route, $method = 'GET')
    {
        if (!$this->checkRequest($route, $method)) {
            $this->handleRateLimitExceeded($route);
        }
    }
}