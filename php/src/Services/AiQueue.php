<?php declare(strict_types=1);

namespace App\Services;

class AiQueue {
    public static function push(string $prompt, string $provider = 'ollama'): int {
        $db = \App\Db::connect();
        $stmt = $db->prepare("INSERT INTO ai_jobs (prompt, provider, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$prompt, $provider]);
        return (int)$db->lastInsertId();
    }
}