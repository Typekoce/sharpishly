<?php
declare(strict_types=1);

namespace App;

/**
 * Location: php/index.php
 * BACKEND FRONT CONTROLLER
 */

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

    public function __construct()
    {
        try {
            $this->parseUrl();
            $this->logRequest();
            $this->run();
        } catch (Throwable $e) {
            // Logs to /storage/logs via the shared Logger instance
            Logger::exception($e);
            $this->abort(500, "Internal Server Error");
        }
    }

    private function logRequest(): void
    {
        Logger::info("PHP Backend Request", [
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'controller' => $this->controllerName,
            'method' => $this->methodName,
            'params' => $this->params
        ]);
    }

    private function handleSpecialRoutes(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        if ($uri === '/php/csv/upload-stream') {
            // Pull the single DB instance from the Registry
            $db = Registry::get(\App\Db::class); 
            
            $controller = new \App\Controllers\CsvController();
            $controller->uploadStream();
            exit;
        }
    }

    private function parseUrl(): void
    {
        $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

        // Normalize path by stripping the 'php' segment
        if (str_starts_with($path, 'php/')) {
            $path = substr($path, 4);
        } elseif ($path === 'php' || $path === 'php/') {
            $path = '';
        }

        if ($path === '') return;

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
        $this->handleSpecialRoutes();
    
        $controllerClass = "App\\Controllers\\{$this->controllerName}Controller";

        if (!class_exists($controllerClass)) {
            $this->abort(404, "Controller '{$this->controllerName}' not found.");
        }

        // Instantiated via custom autoloader defined in bootstrap
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

    private function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        echo $message ?: "Error $code";
        exit;
    }
}

new FrontController();