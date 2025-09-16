<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function get($uri, $action)
    {
        $this->routes['GET'][$uri] = $action;
    }

    public function post($uri, $action)
    {
        $this->routes['POST'][$uri] = $action;
    }

    public function dispatch($method, $uri)
    {
        $uri = rtrim($uri, '/') ?: '/';
        
        // Apply rate limiting before CSRF check
        // RateLimit::getInstance()->middleware($uri, $method); // TEMPORARILY DISABLED
        
        if (!\App\Core\Csrf::check($method, $uri)) {
            flash('error', 'Invalid security token');
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            header('Location: ' . $referer);
            exit;
        }
        
        foreach ($this->routes[$method] ?? [] as $route => $action) {
            if ($this->match($route, $uri, $params)) {
                return $this->call($action, $params);
            }
        }
        
        $this->notFound();
    }

    private function match($route, $uri, &$params)
    {
        $params = [];
        
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
        $pattern = "#^{$pattern}$#";
        
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            
            preg_match_all('/\{([^}]+)\}/', $route, $paramNames);
            foreach ($paramNames[1] as $i => $name) {
                $params[$name] = $matches[$i] ?? null;
            }
            return true;
        }
        
        return false;
    }

    private function call($action, $params)
    {
        if (is_string($action)) {
            [$controller, $method] = explode('@', $action);
            $controller = "App\\Controllers\\{$controller}";
            $instance = new $controller();
            return $instance->$method(...array_values($params));
        }
        
        if (is_callable($action)) {
            return $action(...array_values($params));
        }
    }

    private function notFound()
    {
        http_response_code(404);
        view('errors.404');
    }
}
