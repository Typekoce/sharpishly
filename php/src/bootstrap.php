<?php declare(strict_types=1); // ← no newline or space before declare!

require_once __DIR__ . '/autoload.php';

// Load environment variables (if you use .env)
if (file_exists(__DIR__ . '/../../.env')) {
    foreach (parse_ini_file(__DIR__ . '/../../.env', false) as $k => $v) {
        putenv("$k=$v");
    }
}

// Register global exception handler
set_exception_handler(function (Throwable $e) {
    \App\Services\Logger::exception($e);
    http_response_code(500);
    echo "<h1>500 Internal Server Error</h1>";
    if (getenv('APP_DEBUG') === 'true') {
        echo "<pre>" . htmlspecialchars($e->__toString()) . "</pre>";
    } else {
        echo "<p>Something went wrong. Please try again later.</p>";
    }
    exit;
});

// Optional: error handler for non-fatal errors
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false; // respect @ operator
    }
    \App\Services\Logger::error("PHP Error: $message", [
        'severity' => $severity,
        'file'     => $file,
        'line'     => $line,
    ]);
    return true; // don't execute PHP's default handler
}, E_ALL);

// Make Logger available globally (alias)
class_alias(\App\Services\Logger::class, 'Logger');

// Optional debug line (remove in production)
if (getenv('APP_DEBUG') === 'true') {
    echo "Bootstrap loaded at " . date('c') . "\n";
}

Logger::info("Bootstrap loaded successfully", ['env' => getenv('APP_ENV') ?: 'unknown ' . date('Y-m-d h:m:s')]);