<?php
// file: public/index.php   ← this should be your only publicly accessible PHP file

declare(strict_types=1);

namespace App;

//require_once __DIR__ . '/../vendor/autoload.php'; // if using composer
// or:
require_once __DIR__ . '/src/autoload.php';  // if manual autoloading

class FrontController
{
    private string $controllerName = 'Home';
    private string $methodName = 'index';
    private array $params = [];

    public function __construct()
    {
        $this->parseUrl();
        $this->run();
    }

    private function parseUrl(): void
    {
        // Remove query string if any
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = trim($path, '/');

        if ($path === '') {
            return; // default controller & method
        }

        $segments = explode('/', $path);

        // First segment → controller
        if (!empty($segments[0])) {
            $this->controllerName = ucfirst(strtolower($segments[0]));
        }

        // Second segment → method
        if (isset($segments[1]) && $segments[1] !== '') {
            $this->methodName = strtolower($segments[1]);
        }

        // Everything else → parameters
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

        // Check if method is public and callable
        $reflection = new \ReflectionMethod($controller, $this->methodName);
        if (!$reflection->isPublic()) {
            $this->abort(403, "Method '{$this->methodName}' is not public.");
        }

        // Call the method with parameters (if any)
        call_user_func_array(
            [$controller, $this->methodName],
            $this->params
        );
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

// Optional: very simple autoloading (if you don't use composer)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Start the application
new FrontController();