<?php
// Location: /var/www/html/php/worker-daemon.php

// 1. Correct relative path to the autoloader
require_once __DIR__ . '/autoload.php';

// 2. Import using the 'App' namespace defined in your autoload.php
use App\Services\Logger;
use App\Db; // Per tree: php/src/Db.php maps to App\Db

Logger::info("Scheduler Worker started", ['pid' => getmypid()], 'scheduler');

while (true) {
    try {
        // 1. Cron-based tasks
        $tasks = (new Db())->find([
            'tbl'   => 'tasks',
            'where' => ['status' => 'active', 'type' => 'cron'],
        ]);

        foreach ($tasks as $task) {
            if (shouldRunNow($task['schedule'], $task['last_run'])) {
                dispatchTask($task);
            }
        }

        // 2. File-based queue (your existing CSV + video jobs)
        processFileQueue();

        // Update health file
        file_put_contents(__DIR__ . '/../storage/queue/health.json', json_encode([
            'status' => 'running',
            'last_run' => date('c')
        ]));

    } catch (Throwable $e) {
        Logger::exception($e, 'scheduler');
    }

    sleep(5);
}

// Helper functions (add at bottom)
function shouldRunNow(string $cronExpr, $lastRun): bool { /* use cron-expression library or simple parser */ }
function dispatchTask(array $task): void { /* call appropriate action */ }
function processFileQueue(): void { /* your existing CSV + video logic */ }