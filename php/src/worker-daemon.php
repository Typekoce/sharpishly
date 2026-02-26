<?php
require_once __DIR__ . '/autoload.php';

use App\Services\CSVProcessor;

// Simple loop to keep the container active
while (true) {
    try {
        $pdo = new PDO("mysql:host=db;dbname=sharpishly", "user", "pass");
        
        // Find a job that hasn't started yet
        $stmt = $pdo->query("SELECT * FROM jobs WHERE status = 'pending' LIMIT 1");
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($job) {
            $processor = new CSVProcessor($pdo);
            $processor->run($job['id'], $job['file_path']);
        }
    } catch (Exception $e) {
        error_log("Worker Error: " . $e->getMessage());
    }

    sleep(5); // Wait 5 seconds before checking again to save CPU
}