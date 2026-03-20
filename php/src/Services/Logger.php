<?php declare(strict_types=1);

namespace App\Services;

use Throwable;

/**
 * Sharpishly OS - Centralized Nervous System Logger
 * Handles local persistence, Cloud Audit (Axiom), and Instant Alerts (Telegram)
 */
class Logger
{
    private const DEFAULT_CHANNEL = 'app';
    private const LOG_DIR         = '/var/www/html/storage/logs';
    private const LEVELS          = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    /**
     * Log a message with local persistence and multi-channel offloading
     */
    public static function log(
        string $message,
        string $channel = self::DEFAULT_CHANNEL,
        string $level = 'INFO',
        array $context = []
    ): void {
        $level = strtoupper($level);
        if (!in_array($level, self::LEVELS, true)) {
            $level = 'INFO';
        }

        self::ensureLogDirectory();
        $logFile = self::getLogFilePath($channel);

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR) : '';

        // Standardized Log Entry Format
        $entry = sprintf("[%s] [%s] %s%s\n", $timestamp, $level, $message, $contextStr);

        // 1. Local Persistence (Atomic write with LOCK_EX to prevent corruption)
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

        // 2. Offload to Axiom Cloud (Audit Trail)
        if ($level !== 'DEBUG') {
            self::offloadToAxiom($message, $level, $channel, $context);
        }

        // 3. Mirror High-Priority events to Telegram & Global app.log
        if (in_array($level, ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'], true)) {
            self::pushToTelegram($message, $level);

            if ($channel !== self::DEFAULT_CHANNEL) {
                @file_put_contents(
                    self::getLogFilePath(self::DEFAULT_CHANNEL),
                    sprintf("[FROM %s] %s", strtoupper($channel), $entry),
                    FILE_APPEND | LOCK_EX
                );
            }
        }
    }

    /**
     * Offload data to Axiom for long-term log management
     */
    private static function offloadToAxiom(string $msg, string $lvl, string $chan, array $ctx): void 
    {
        $dataset = getenv('AXIOM_DATASET') ?: 'sharpishly-logs';
        $token = getenv('AXIOM_TOKEN');
        if (!$token) return;

        $url = "https://api.axiom.co/v1/datasets/$dataset/ingest";
        $data = json_encode([[
            '_time' => time(),
            'severity' => $lvl,
            'channel' => $chan,
            'message' => $msg,
            'context' => $ctx
        ]]);

        // Non-blocking cURL for performance
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); // 2 second cap
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Push high-priority alerts to Telegram Bot
     */
    private static function pushToTelegram(string $msg, string $lvl): void
    {
        $botToken = getenv('TELEGRAM_BOT_TOKEN');
        $chatId   = getenv('TELEGRAM_CHAT_ID');
        if (!$botToken || !$chatId) return;

        $text = "🚨 *Sharpishly Alert: $lvl*\n`$msg`";
        $url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($text) . "&parse_mode=Markdown";
        
        // Use stream context to ensure a timeout (Don't hang the app for a bot)
        $ctx = stream_context_create(['http' => ['timeout' => 2, 'ignore_errors' => true]]);
        @file_get_contents($url, false, $ctx);
    }

    /**
     * PSR-3 Shorthand methods
     */
    public static function debug(string $m, array $c = [], string $ch = self::DEFAULT_CHANNEL): void { self::log($m, $ch, 'DEBUG', $c); }
    public static function info(string $m, array $c = [], string $ch = self::DEFAULT_CHANNEL): void { self::log($m, $ch, 'INFO', $c); }
    public static function notice(string $m, array $c = [], string $ch = self::DEFAULT_CHANNEL): void { self::log($m, $ch, 'NOTICE', $c); }
    public static function warning(string $m, array $c = [], string $ch = self::DEFAULT_CHANNEL): void { self::log($m, $ch, 'WARNING', $c); }
    public static function error(string $m, array $c = [], string $ch = self::DEFAULT_CHANNEL): void { self::log($m, $ch, 'ERROR', $c); }
    public static function critical(string $m, array $c = [], string $ch = self::DEFAULT_CHANNEL): void { self::log($m, $ch, 'CRITICAL', $c); }

    /**
     * Log an exception with full stack trace context
     */
    public static function exception(Throwable $e, string $channel = self::DEFAULT_CHANNEL): void
    {
        $message = sprintf("Uncaught %s: %s", get_class($e), $e->getMessage());
        self::log($message, $channel, 'ERROR', [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => substr($e->getTraceAsString(), 0, 1000) // Truncate trace for log readability
        ]);
    }

    private static function getLogFilePath(string $channel): string
    {
        $safeChannel = preg_replace('/[^a-z0-9_-]/i', '_', strtolower($channel));
        return self::LOG_DIR . '/' . $safeChannel . '.log';
    }

    private static function ensureLogDirectory(): void
    {
        if (!is_dir(self::LOG_DIR)) {
            @mkdir(self::LOG_DIR, 0775, true);
        }
    }
}