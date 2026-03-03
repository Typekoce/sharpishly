<?php

namespace App\Services;

use Throwable;

class Logger
{
    private const DEFAULT_CHANNEL = 'app';
    private const LOG_DIR         = __DIR__ . '/../../logs';
    private const LEVELS          = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    /**
     * Log a message to a channel-specific file
     *
     * @param string      $message
     * @param string      $channel   (default: 'app')
     * @param string      $level     one of the PSR-3 levels
     * @param array       $context   optional structured data (will be JSON-encoded)
     * @return void
     */
    public static function log(
        string $message,
        string $channel = self::DEFAULT_CHANNEL,
        string $level = 'INFO',
        array $context = []
    ): void {
        $level = strtoupper($level);

        if (!in_array($level, self::LEVELS, true)) {
            $level = 'INFO'; // fallback
        }

        $logFile = self::getLogFilePath($channel);

        self::ensureLogDirectory();

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';

        $entry = sprintf(
            "[%s] [%s] %s%s\n",
            $timestamp,
            $level,
            $message,
            $contextStr
        );

        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

        // Mirror critical levels to main app.log
        if (in_array($level, ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'], true) && $channel !== self::DEFAULT_CHANNEL) {
            $mainLog = self::getLogFilePath(self::DEFAULT_CHANNEL);
            $mirrored = sprintf("[FROM %s] %s", $channel, $entry);
            file_put_contents($mainLog, $mirrored, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Convenience methods (PSR-3 style)
     */
    public static function debug(string $message, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::log($message, $channel, 'DEBUG', $context);
    }

    public static function info(string $message, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::log($message, $channel, 'INFO', $context);
    }

    public static function notice(string $message, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::log($message, $channel, 'NOTICE', $context);
    }

    public static function warning(string $message, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::log($message, $channel, 'WARNING', $context);
    }

    public static function error(string $message, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::log($message, $channel, 'ERROR', $context);
    }

    public static function critical(string $message, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::log($message, $channel, 'CRITICAL', $context);
    }

    public static function alert(string $message, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::log($message, $channel, 'ALERT', $context);
    }

    public static function emergency(string $message, array $context = [], string $channel = self::DEFAULT_CHANNEL): void
    {
        self::log($message, $channel, 'EMERGENCY', $context);
    }

    /**
     * Log an exception with stack trace
     */
    public static function exception(Throwable $e, string $channel = self::DEFAULT_CHANNEL): void
    {
        $message = sprintf(
            "Uncaught %s: %s in %s:%d\nStack trace:\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        self::log($message, $channel, 'ERROR');
    }

    private static function getLogFilePath(string $channel): string
    {
        return self::LOG_DIR . '/' . preg_replace('/[^a-z0-9_-]/i', '_', $channel) . '.log';
    }

    private static function ensureLogDirectory(): void
    {
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }
    }
}