<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Db;
use App\Services\CSVProcessor;

// Prevent the script from timing out
set_time_limit(0);

$db = new Db();
$processor = new CSVProcessor();

Logger::info("Worker Daemon started. Monitoring for jobs...");

while (true) {
    try {
        // 1. Look for the next pending job
        $jobs = $db->find([
            'tbl'   => 'jobs',
            'where' => ['status' => 'pending'],
            'order' => ['id' => 'ASC'],
            'limit' => 1
        ]);

        if (!empty($jobs)) {
            $job = $jobs[0];
            $jobId = (int)$job['id'];

            Logger::info("Worker found Job #$jobId. Starting processing...");

            // 2. Mark as processing immediately to prevent double-picks
            $db->save([
                'tbl'    => 'jobs', 
                'id'     => $jobId, 
                'status' => 'processing'
            ]);

            // 3. Hand off to the processor
            $processor->process($jobId, $job['file_path']);

        } else {
            // No jobs? Sleep for a bit to save CPU
            sleep(2); 
        }

    } catch (Exception $e) {
        Logger::error("Worker Loop Error: " . $e->getMessage());
        sleep(5); // Wait a bit longer if there's a system/DB error
    }

    // Optional: check if a "stop" file exists to gracefully exit
    if (file_exists(__DIR__ . '/../stop_worker.txt')) {
        Logger::info("Worker stopping gracefully via stop file.");
        break;
    }
}