<?php
declare(strict_types=1);

namespace App;

use App\Services\Location;
use App\Services\Logger;
use App\Db;

/**
 * 1. Initialize Registry & Location Service First
 * We need Location to know where the base directory is for the autoloader.
 */
class Registry {
    private static array $instances = [];
    
    public static function get(string $class, ...$args) {
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class(...$args);
        }
        return self::$instances[$class];
    }
}

// Instantiate Location early to use its baseDir() method
$loc = Registry::get(Location::class);
$base = $loc->baseDir(); // This should return /var/www/html/

/**
 * 2. Autoloader using Location Service paths
 */
spl_autoload_register(function ($class) use ($base) {
    // Handle Test Namespace (App\Tests\...) -> /tests/unit/
    if (strpos($class, 'App\Tests\\') === 0) {
        $relativeClass = str_replace('App\Tests\\', '', $class);
        $file = $base . 'tests/unit/' . str_replace('\\', '/', $relativeClass) . '.php';
    } 
    // Handle Application Namespace (App\...) -> /php/src/
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

// 3. Init remaining Shared Instances
Registry::get(Db::class);