<?php
declare(strict_types=1);

namespace App;

use App\Services\Location;
use App\Services\Logger;
use App\Db;

/**
 * 1. MANUAL SEED LOADING
 * We must manually load the Location service because the autoloader 
 * relies on it to find the base directory of the project.
 */
$locationFile = __DIR__ . '/Services/Location.php';
if (file_exists($locationFile)) {
    require_once $locationFile;
} else {
    die("❌ Critical Failure: Location service not found at $locationFile");
}

/**
 * 2. REGISTRY SERVICE
 * Centralized container for shared class instances (Singleton pattern).
 */
class Registry {
    private static array $instances = [];
    
    /**
     * Retrieves or creates a shared instance of a class.
     */
    public static function get(string $class, ...$args) {
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class(...$args);
        }
        return self::$instances[$class];
    }

    /**
     * Injects an existing instance (useful for testing/mocking).
     */
    public static function set(string $class, object $instance): void {
        self::$instances[$class] = $instance;
    }
}

/**
 * 3. INITIALIZE CORE PATHS
 */
$loc  = Registry::get(Location::class);
$base = $loc->baseDir(); // Standardized project root (e.g., /var/www/html/)

/**
 * 4. DYNAMIC AUTOLOADER
 * Maps namespaces to physical directories based on the project root.
 */
spl_autoload_register(function ($class) use ($base) {
    // A. Unit Test Mapping (App\Tests\ -> /tests/unit/)
    if (strpos($class, 'App\Tests\\') === 0) {
        $relativeClass = str_replace('App\Tests\\', '', $class);
        $file = $base . 'tests/unit/' . str_replace('\\', '/', $relativeClass) . '.php';
    } 
    // B. Application Mapping (App\ -> /php/src/)
    elseif (strpos($class, 'App\\') === 0) {
        $relativeClass = str_replace('App\\', '', $class);
        $file = $base . 'php/src/' . str_replace('\\', '/', $relativeClass) . '.php';
    } else {
        return;
    }

    if (file_exists($file)) {
        require_once $file;
    }
});

/**
 * 5. CORE SERVICE INITIALIZATION
 * Pre-warm the shared database connection and logger.
 */
try {
    Registry::get(Db::class);
    Logger::info("System bootstrap completed successfully.");
} catch (\Exception $e) {
    // If DB fails during bootstrap, we still want the system to load, 
    // but we log the error.
    error_log("Bootstrap Warning: " . $e->getMessage());
}