<?php
declare(strict_types=1);

namespace App;

require_once __DIR__ . '/src/bootstrap.php';

use App\Registry;
use App\Services\Logger;
use ReflectionMethod;
use Throwable;

class FrontController
{
    private string $controllerName = 'Home';
    private string $methodName = 'index';
    private array $params = [];

    /**
     * TIER 2: The Exception Map
     * For routes that don't follow the standard naming convention.
     */
    private array $specialRoutes = [
        'jeff_bezo' => 'Amazon',
        'csv/upload-stream' => ['Csv', 'uploadStream'],
    ];

    public function __construct()
    {
        try {
            $this->parseUrl();
            $this->logRequest();
            $this->run();
        } catch (Throwable $e) {
            Logger::exception($e);
            $this->abort(500, "Internal Server Error");
        }
    }

    private function parseUrl(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = trim($uri, '/');

        // Remove 'php/' prefix
        if (str_starts_with($path, 'php/')) {
            $path = substr($path, 4);
        } elseif ($path === 'php') {
            $path = '';
        }

        if ($path === '') return;

        // 1. Check Tier 2: Special Routes first
        if (isset($this->specialRoutes[$path])) {
            $mapping = $this->specialRoutes[$path];
            if (is_array($mapping)) {
                $this->controllerName = $mapping[0];
                $this->methodName = $mapping[1];
            } else {
                $this->controllerName = $mapping;
            }
            return;
        }

        $segments = explode('/', $path);

        // 2. Tier 1: Auto-Mapping with Underscore Support
        if (!empty($segments[0])) {
            // Converts 'nervous_system' -> 'NervousSystem'
            $this->controllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $segments[0])));
        }

        if (isset($segments[1]) && $segments[1] !== '') {
            // Converts 'upload_csv' -> 'uploadCsv' (camelCase for methods)
            $methodRaw = str_replace(' ', '', ucwords(str_replace('_', ' ', $segments[1])));
            $this->methodName = lcfirst($methodRaw);
        }

        $this->params = array_slice($segments, 2);
    }

    private function run(): void
    {
        $controllerClass = "App\\Controllers\\{$this->controllerName}Controller";

        if (!class_exists($controllerClass)) {
            $this->abort(404, "Controller '{$this->controllerName}' not found.");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $this->methodName)) {
            $this->abort(404, "Method '{$this->methodName}' not found.");
        }

        $reflection = new ReflectionMethod($controller, $this->methodName);
        if (!$reflection->isPublic()) {
            $this->abort(403, "Access Denied.");
        }

        call_user_func_array([$controller, $this->methodName], $this->params);
    }

    private function logRequest(): void
    {
        Logger::info("PHP Backend Request", [
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'controller' => $this->controllerName,
            'method' => $this->methodName
        ]);
    }

    private function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        echo $message ?: "Error $code";
        exit;
    }
}

new FrontController();