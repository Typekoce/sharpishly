<?php
// Location: /var/www/html/php/src/worker-daemon.php

declare(strict_types=1);

require_once __DIR__ . '/autoload.php';

use App\Services\Logger;
use App\Services\CsvProcessor;
use App\Services\Location;
use App\Db;

// Safety & Resource Constraints
$maxIterations = 1000; 
$iteration = 0;
$memoryLimit = 128 * 1024 * 1024; // 128MB

// Initialize Centralized Path Service
$loc = new Location();

Logger::info("Neural Factory Daemon Online", ['pid' => getmypid()], 'scheduler');

while ($iteration < $maxIterations) {
    $iteration++;

    try {
        $db = new Db();

        // --- 1. SCAN QUEUE ---
        // glob() finds all .job files in the queue directory
        $jobFiles = glob($loc->queue('*.job'));

        foreach ($jobFiles as $jobFile) {
            $rawContent = file_get_contents($jobFile);
            $jobData = json_decode($rawContent, true);
            
            if ($jobData && isset($jobData['job_id'], $jobData['filepath'])) {
                
                // Use Location service to resolve the absolute path
                // This strips the incoming path of leading slashes/redundancies
                $fileName = basename($jobData['filepath']);
                $absoluteCsvPath = $loc->uploads($fileName);

                if (!file_exists($absoluteCsvPath)) {
                    Logger::error("CSV missing on disk", ['path' => $absoluteCsvPath], 'scheduler');
                    rename($jobFile, $jobFile . '.missing');
                    continue;
                }

                Logger::info("Processing Job #{$jobData['job_id']}", ['file' => $absoluteCsvPath], 'scheduler');

                // Execute the Interrogation logic
                $processor = new CsvProcessor();
                $processor->process(
                    (int)$jobData['job_id'], 
                    $absoluteCsvPath
                );

                // Cleanup: Remove job file upon successful handoff to processor
                unlink($jobFile);
                
            } else {
                Logger::error("Invalid job file format", ['file' => $jobFile], 'scheduler');
                rename($jobFile, $jobFile . '.bad');
            }
        }

        // --- 2. HEALTH HEARTBEAT ---
        // Updates every 5 seconds so the UI/Logs can confirm daemon status
        file_put_contents($loc->queue('health.json'), json_encode([
            'status' => 'running',
            'iteration' => $iteration,
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB',
            'last_run' => date('c')
        ]));

    } catch (Throwable $e) {
        // Log any engine-level crashes to the scheduler log
        Logger::exception($e, 'scheduler');
    }

    // --- 3. RESOURCE GUARD ---
    // If we leak memory, exit and let Docker/Systemd restart the process
    if (memory_get_usage(true) > $memoryLimit) {
        Logger::info("Memory limit reached. Cycling daemon...", [], 'scheduler');
        break; 
    }

    // Hibernate for 5 seconds to reduce CPU overhead on the VM
    sleep(5);
}

Logger::info("Daemon reaching iteration limit. Restarting...");
exit(0);