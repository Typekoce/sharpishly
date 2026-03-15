<?php
declare(strict_types=1);

/**
 * SHARPISHLY WORKER DAEMON
 * Location: /var/www/html/php/src/worker-daemon.php
 */

// 1. Initialize the application environment (Autoloader + Registry)
require_once __DIR__ . '/bootstrap.php';

use App\Registry;
use App\Services\Logger;
use App\Services\CsvProcessor;
use App\Services\Location;
use App\Db;

// Safety & Resource Constraints
$maxIterations = 1000; 
$iteration = 0;
$memoryLimit = 128 * 1024 * 1024; // 128MB

// Access services via Registry
$loc = Registry::get(Location::class);
$db  = Registry::get(Db::class);

Logger::info("Neural Factory Daemon Online", ['pid' => getmypid()], 'scheduler');

while ($iteration < $maxIterations) {
    $iteration++;

    try {
        // --- 1. SCAN QUEUE ---
        $jobFiles = glob($loc->queue('*.job'));

        foreach ($jobFiles as $jobFile) {
            $rawContent = file_get_contents($jobFile);
            $jobData = json_decode((string)$rawContent, true);
            
            if ($jobData && isset($jobData['job_id'], $jobData['filepath'])) {
                
                // Resolve absolute path via Location service
                $fileName = basename($jobData['filepath']);
                $absoluteCsvPath = $loc->uploads($fileName);

                if (!file_exists($absoluteCsvPath)) {
                    Logger::error("CSV missing on disk", ['path' => $absoluteCsvPath], 'scheduler');
                    rename($jobFile, $jobFile . '.missing');
                    continue;
                }

                Logger::info("Processing Job #{$jobData['job_id']}", ['file' => $absoluteCsvPath], 'scheduler');

                /**
                 * CsvProcessor now pulls its own Db/Location from Registry internally.
                 * No need to pass dependencies manually.
                 */
                $processor = new CsvProcessor();
                $processor->process(
                    (int)$jobData['job_id'], 
                    $absoluteCsvPath
                );

                // Cleanup job file
                unlink($jobFile);
                
            } else {
                Logger::error("Invalid job file format", ['file' => $jobFile], 'scheduler');
                rename($jobFile, $jobFile . '.bad');
            }
        }

        // --- 2. HEALTH HEARTBEAT ---
        file_put_contents($loc->queue('health.json'), json_encode([
            'status' => 'running',
            'iteration' => $iteration,
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB',
            'last_run' => date('c')
        ]));

    } catch (Throwable $e) {
        Logger::exception($e, 'scheduler');
    }

    // --- 3. RESOURCE GUARD ---
    if (memory_get_usage(true) > $memoryLimit) {
        Logger::info("Memory limit reached. Cycling daemon...", [], 'scheduler');
        break; 
    }

    sleep(5);
}

Logger::info("Daemon reaching iteration limit. Restarting...");
exit(0);