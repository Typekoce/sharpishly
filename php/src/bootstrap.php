<?php
declare(strict_types=1);
namespace App;

use App\Services\Location;
use App\Services\Logger;
use App\Db;

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

class Registry {
    private static array $instances = [];
    public static function get(string $class, ...$args) {
        if (!isset(self::$instances[$class])) self::$instances[$class] = new $class(...$args);
        return self::$instances[$class];
    }
}

// Init Shared Instances
Registry::get(Location::class);
Registry::get(Db::class);
