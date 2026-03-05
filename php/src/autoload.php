<?php
// src/autoload.php

declare(strict_types=1);

/**
 * Minimal PSR-4-ish autoloader for the App namespace
 * Assumes:
 *   - src/ contains the code
 *   - namespace App → src/
 *   - namespace App\Controllers → src/Controllers/
 */

spl_autoload_register(function (string $class): void {
    // Only handle classes that start with our namespace
    $prefix = 'App\\';
    $prefixLength = strlen($prefix);

    if (strncmp($prefix, $class, $prefixLength) !== 0) {
        return;
    }

    // Get the relative class name (without App\)
    $relativeClass = substr($class, $prefixLength);

    // Replace namespace separators with directory separators
    $filePath = str_replace('\\', '/', $relativeClass);

    // Build full file path
    $fullPath = __DIR__ . '/' . $filePath . '.php';

    // If the file exists, load it
    if (file_exists($fullPath)) {
        require_once $fullPath;
    }
    // You can add else { trigger_error(...); } if you want strict mode
}, prepend: true);
