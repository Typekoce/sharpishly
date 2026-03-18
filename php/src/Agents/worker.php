<?php
declare(strict_types=1);

/**
 * SHARPISHLY WORKER DAEMON
 * Location: php/src/Agents/worker.php
 */

require_once __DIR__ . '/../bootstrap.php';

/**
 * @file worker.php
 * @package App\Agents
 * @brief Background Processing Daemon.
 *
 * This script runs as a persistent background process (Daemon). It monitors
 * the database for pending jobs (CSV uploads, AI tasks) and executes 
 * them asynchronously from the web request.
 *
 * @note This agent communicates with the UI by writing JSON signals to 
 * the 'events.json' queue, which are then picked up by the NervousSystemController.
 */
use App\Registry;
use App\Services\Logger;
use App\Services\CsvProcessor;
use App\Services\Location;

$loc = Registry::get(Location::class);
$iteration = 0;
$memoryLimit = 128 * 1024 * 1024; 

Logger::info("Neural Factory Daemon Online", ['pid' => getmypid()], 'scheduler');

while (true) {
    $iteration++;

    try {
        // --- 1. STOP SIGNAL CHECK ---
        if (file_exists($loc->queue('STOP'))) {
            Logger::info("Stop signal detected. Exiting worker.");
            unlink($loc->queue('STOP'));
            break;
        }

        // --- 2. SCAN QUEUE ---
        $jobFiles = glob($loc->queue('*.job'));

        foreach ($jobFiles as $jobFile) {
            $jobData = json_decode((string)file_get_contents($jobFile), true);
            
            if ($jobData && isset($jobData['job_id'], $jobData['filepath'])) {
                
                // BROADCAST TO HUD via SSE
                file_put_contents($loc->queue('events.json'), json_encode([
                    'event' => 'PROGRESS',
                    'val' => 10,
                    'msg' => "Worker Engaged: Job #{$jobData['job_id']}"
                ]));

                $processor = new CsvProcessor();
                $processor->process((int)$jobData['job_id'], $jobData['filepath']);

                unlink($jobFile);
                
                file_put_contents($loc->queue('events.json'), json_encode([
                    'event' => 'PROGRESS',
                    'val' => 100,
                    'msg' => "Job #{$jobData['job_id']} Integrated Successfully"
                ]));
            }
        }

        // --- 3. HEARTBEAT ---
        file_put_contents($loc->queue('health.json'), json_encode([
            'status' => 'active',
            'uptime_cycles' => $iteration,
            'ts' => time()
        ]));

    } catch (Throwable $e) {
        Logger::exception($e, 'scheduler');
    }

    if (memory_get_usage(true) > $memoryLimit) break; 

    sleep(2); // Faster polling for snappier HUD updates
}

exit(0);