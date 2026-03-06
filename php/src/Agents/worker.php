<?php
require_once __DIR__ . '/../bootstrap.php'; // Load DB, Vault, and Autoloader
use App\Services\CSVProcessor;
use App\Services\Logger;

echo "🤖 Master Worker Online...\n";

while (true) {
    // 1. The "STOP" Guard (Preserved from your original)
    if (file_exists(__DIR__ . '/../../../storage/queue/STOP')) { 
        sleep(2); 
        continue; 
    }

    $jobs = glob(__DIR__ . '/../../../storage/queue/*.job');

    foreach ($jobs as $f) {
        $job = json_decode(file_get_contents($f), true);
        $type = $job['type'] ?? 'UNKNOWN';

        Logger::info("Agent received task: $type");

        // 2. Specialized Processing
        switch ($type) {
            case 'CSV_IMPORT':
                // Real Processing - Uses your restored CSVProcessor logic
                $processor = new CSVProcessor();
                $processor->process((int)$job['job_id'], $job['filepath']);
                $result = "CSV Interrogation Complete.";
                break;

            case 'SCOUT_TRENDS':
                // Keeping your original reasoning logic
                $result = scoutWeb($type); 
                break;

            default:
                $result = "Unknown task type ignored.";
        }

        // 3. Cleanup
        unlink($f); 
        Logger::info("Task finished: $result");
    }
    sleep(1);
}