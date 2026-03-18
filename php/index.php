<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

use App\Registry;
use App\Services\Logger;

$host = $_SERVER['HTTP_HOST'] ?? '';
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// --- TIER 1: Subdomain Routing (The New "Outer" Shell) ---
$hostParts = explode('.', $host);
$subdomain = (count($hostParts) > 2) ? $hostParts[0] : null;

if ($subdomain && !in_array($subdomain, ['www', 'sharpishly'])) {
    // Map subdomain to Controller (e.g., docs -> DocumentsController)
    $controllerName = 'App\\Controllers\\' . ucfirst($subdomain) . 'Controller';
    
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        // Subdomains often point to an 'index' or 'main' method by default
        $controller->index();
        exit;
    }
}

// --- TIER 2: Exception/Hard-Coded Mapping ---
$exceptionMap = [
    '/jeff_bezo' => 'App\\Controllers\\AmazonController',
    '/neural'    => 'App\\Controllers\\OllamaController',
    '/docs'    => 'App\\Controllers\\DocsController'
];

if (isset($exceptionMap[$uri])) {
    $className = $exceptionMap[$uri];
    (new $className())->index();
    exit;
}

// --- TIER 3: Dynamic URI Auto-Mapping ---
$parts = explode('/', trim($uri, '/'));
$slug  = $parts[0] ?? 'home';

// Convert underscore to PascalCase (nervous_system -> NervousSystem)
$formatted = str_replace('_', '', ucwords($slug, '_'));
$class = "App\\Controllers\\{$formatted}Controller";

if (class_exists($class)) {
    $method = $parts[1] ?? 'index';
    $instance = new $class();
    
    if (method_exists($instance, $method)) {
        $instance->$method();
    } else {
        $instance->index();
    }
} else {
    // Fallback to Home
    (new \App\Controllers\HomeController())->index();
}