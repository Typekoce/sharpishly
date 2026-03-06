<?php
/**
 * SHARPISHLY MASTER WORKER (DAEMON)
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Services\CSVProcessor;
use App\Services\Logger;
use App\Db;

$db = new Db();
Logger::info("🤖 Master Worker Online...");

while (true) {
    // 1. Guard
    if (file_exists(__DIR__ . '/../../../storage/queue/STOP')) { 
        sleep(5); 
        continue; 
    }

    $jobs = glob(__DIR__ . '/../../../storage/queue/*.job');

    foreach ($jobs as $f) {
        $job = json_decode(file_get_contents($f), true);
        $type = $job['type'] ?? 'UNKNOWN';
        $jobId = (int)($job['job_id'] ?? 0);

        Logger::info("Agent received task: $type (#$jobId)");

        try {
            switch ($type) {
                case 'CSV_IMPORT':
                    $absolutePath = dirname(__DIR__, 2) . '/' . $job['filepath'];
                    
                    // PRE-FLIGHT: Calculate size so UI progress bar works
                    if (file_exists($absolutePath)) {
                        $totalRows = bin_count_lines($absolutePath) - 1;
                        $db->save([
                            'tbl' => 'jobs',
                            'id' => $jobId,
                            'total_rows' => $totalRows,
                            'status' => 'processing'
                        ]);
                    }

                    $processor = new CSVProcessor();
                    $processor->process($jobId, $job['filepath']);
                    $result = "CSV Interrogation Complete.";
                    break;

                case 'SCOUT_TRENDS':
                    // Keep your original Scout logic here
                    $result = "Trends Scouted."; 
                    break;

                default:
                    $result = "Unknown task type ignored.";
            }

            unlink($f); 
            Logger::info("Task finished: $result");

        } catch (Exception $e) {
            Logger::error("Worker Error: " . $e->getMessage());
            if ($jobId) {
                $db->save(['tbl' => 'jobs', 'id' => $jobId, 'status' => 'failed']);
            }
            unlink($f); // Remove failed job to prevent infinite loops
        }
    }
    sleep(1);
}

/**
 * High-speed line count for 50k+ rows
 */
function bin_count_lines(string $file): int {
    $count = 0;
    $handle = fopen($file, "r");
    while (!feof($handle)) {
        $count += substr_count(fread($handle, 8192), "\n");
    }
    fclose($handle);
    return $count;
}