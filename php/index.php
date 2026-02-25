<?php
declare(strict_types=1);

namespace App;

require_once __DIR__ . '/src/autoload.php';

class FrontController
{
    private string $controllerName = 'Home';
    private string $methodName = 'index';
    private array $params = [];

    private string $logFile;

    public function __construct()
    {
        $this->logFile = __DIR__ . '/app.log';

        if (!file_exists($this->logFile)) {
            @touch($this->logFile);
            @chmod($this->logFile, 0664);
        }

        $this->parseUrl();
        $this->logRequest();
        $this->run();
    }

    private function logRequest(): void
    {
        $line = sprintf(
            "[%s] REQUEST_URI = %s\n  → parsed path = %s\n  → controller = %s\n  → method = %s\n  → params = %s\n\n",
            date('c'),
            $_SERVER['REQUEST_URI'] ?? 'unknown',
            trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/'),
            $this->controllerName,
            $this->methodName,
            json_encode($this->params)
        );
        @file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }

    private function parseUrl(): void
    {
        $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

        // Temporary /php/ prefix removal — adjust or remove later
        if (str_starts_with($path, 'php/')) {
            $path = substr($path, 4);
        } elseif ($path === 'php' || $path === 'php/') {
            $path = '';
        }

        if ($path === '') {
            return;
        }

        $segments = explode('/', $path);

        if (!empty($segments[0])) {
            $this->controllerName = ucfirst(strtolower($segments[0]));
        }

        if (isset($segments[1]) && $segments[1] !== '') {
            $this->methodName = strtolower($segments[1]);
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
            $this->abort(404, "Method '{$this->methodName}' not found in {$this->controllerName}Controller.");
        }

        $reflection = new \ReflectionMethod($controller, $this->methodName);
        if (!$reflection->isPublic()) {
            $this->abort(403, "Method '{$this->methodName}' is not public.");
        }

        call_user_func_array([$controller, $this->methodName], $this->params);
    }

    private function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        echo $message ?: "Error $code";
        if ($code === 404) {
            echo "<h1>404 - Page not found</h1>";
        }
        exit;
    }
}

new FrontController();