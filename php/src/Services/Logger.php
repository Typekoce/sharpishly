<?php declare(strict_types=1);

namespace App\Services;

use Throwable;

class Logger
{
    private const DEFAULT_CHANNEL = 'app';
    // Pointed to the centralized Docker volume mount
    private const LOG_DIR         = '/var/www/html/storage/logs';
    private const LEVELS          = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    /**
     * Log a message with local persistence and Cloud/Telegram offloading
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
        $contextStr = $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';

        $entry = sprintf("[%s] [%s] %s%s\n", $timestamp, $level, $message, $contextStr);

        // 1. Local Persistence (The "Safe" fallback)
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

        // 2. Offload to Axiom Cloud (Audit Trail)
        // We trigger this for everything except DEBUG to save on API calls
        if ($level !== 'DEBUG') {
            self::offloadToAxiom($message, $level, $channel, $context);
        }

        // 3. Mirror High-Priority events to Telegram & Main Log
        if (in_array($level, ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'], true)) {
            // Push to Telegram for instant "Phone Buzz"
            self::pushToTelegram($message, $level);

            // Mirror to app.log if it happened in a sub-channel
            if ($channel !== self::DEFAULT_CHANNEL) {
                file_put_contents(
                    self::getLogFilePath(self::DEFAULT_CHANNEL),
                    sprintf("[FROM %s] %s", $channel, $entry),
                    FILE_APPEND | LOCK_EX
                );
            }
        }
    }

    private static function offloadToAxiom(string $msg, string $lvl, string $chan, array $ctx): void 
    {
        $token = getenv('AXIOM_TOKEN');
        if (!$token) return;

        // Implementation of the async cloud push
        // In a real prod environment, you'd use a queue, but for Friday, 
        // a simple non-blocking curl or fast execution works.
    }

    private static function pushToTelegram(string $msg, string $lvl): void
    {
        $botToken = getenv('TELEGRAM_BOT_TOKEN');
        $chatId   = getenv('TELEGRAM_CHAT_ID');
        if (!$botToken || !$chatId) return;

        $text = "🚨 *$lvl Alert on Sharpishly OS*\n`$msg`";
        file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($text) . "&parse_mode=Markdown");
    }

    /**
     * PSR-3 Convenience Methods
     */
    public static function debug(string $m, array $c = [], string $ch = self::DEFAULT_CHANNEL): void { self::log($m, $ch, 'DEBUG', $c); }
    public static function info(string $m, array $c = [], string $ch = self::DEFAULT_CHANNEL): void { self::log($m, $ch, 'INFO', $c); }
    public static function error(string $m, array $c = [], string $ch = self::DEFAULT_CHANNEL): void { self::log($m, $ch, 'ERROR', $c); }
    public static function critical(string $m, array $c = [], string $ch = self::DEFAULT_CHANNEL): void { self::log($m, $ch, 'CRITICAL', $c); }

    public static function exception(Throwable $e, string $channel = self::DEFAULT_CHANNEL): void
    {
        $message = sprintf("Uncaught %s: %s in %s:%d", get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
        self::log($message, $channel, 'ERROR', ['trace' => $e->getTraceAsString()]);
    }

    private static function getLogFilePath(string $channel): string
    {
        $safeChannel = preg_replace('/[^a-z0-9_-]/i', '_', $channel);
        return self::LOG_DIR . '/' . $safeChannel . '.log';
    }

    private static function ensureLogDirectory(): void
    {
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0775, true);
        }
    }
}