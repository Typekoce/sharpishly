<?php
// Location: /var/www/html/php/src/worker-daemon.php

declare(strict_types=1);

require_once __DIR__ . '/autoload.php';

use App\Services\Logger;
use App\Services\CsvProcessor;
use App\Db;

// Safety Constraints
$maxIterations = 1000; 
$iteration = 0;
$memoryLimit = 128 * 1024 * 1024; // 128MB
// USE ABSOLUTE PATHS to match the CsvController
$baseDir = '/var/www/html/storage/';
$queueDir = $baseDir . 'queue/';

Logger::info("Neural Factory Daemon Online", ['pid' => getmypid()], 'scheduler');

while ($iteration < $maxIterations) {
    $iteration++;

    try {
        $db = new Db();

        // --- 1. FILE-BASED QUEUE ---
        $jobFiles = glob($queueDir . '*.job');

        foreach ($jobFiles as $jobFile) {
            $rawContent = file_get_contents($jobFile);
            $jobData = json_decode($rawContent, true);
            
            if ($jobData && isset($jobData['job_id'], $jobData['filepath'])) {
                // Prepend baseDir because filepath is stored as 'uploads/filename.csv'
                $absoluteCsvPath = $baseDir . $jobData['filepath'];

                if (!file_exists($absoluteCsvPath)) {
                    Logger::error("CSV missing on disk", ['path' => $absoluteCsvPath]);
                    rename($jobFile, $jobFile . '.missing');
                    continue;
                }

                Logger::info("Processing Job #{$jobData['job_id']}", ['file' => $absoluteCsvPath]);

                $processor = new CsvProcessor();
                // Pass the absolute path to the processor
                $processor->process(
                    (int)$jobData['job_id'], 
                    $absoluteCsvPath
                );

                unlink($jobFile);
            } else {
                Logger::error("Invalid job file format", ['file' => $jobFile]);
                rename($jobFile, $jobFile . '.bad');
            }
        }

        // --- 2. HEALTH HEARTBEAT ---
        file_put_contents($queueDir . 'health.json', json_encode([
            'status' => 'running',
            'iteration' => $iteration,
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB',
            'last_run' => date('c')
        ]));

    } catch (Throwable $e) {
        Logger::exception($e, 'scheduler');
    }

    if (memory_get_usage(true) > $memoryLimit) {
        Logger::info("Memory limit reached. Cycling daemon...", [], 'scheduler');
        break; 
    }

    sleep(5);
}

Logger::info("Daemon cycling...");
exit(0);