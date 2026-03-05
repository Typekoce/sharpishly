<?php
require_once __DIR__ . '/src/bootstrap.php';

use App\Db;
use App\Services\CSVProcessor;

$db = new Db();
$processor = new CSVProcessor();

// Find a pending job
$job = $db->find([
    'tbl' => 'jobs',
    'where' => ['status' => 'pending'],
    'limit' => 1
]);

if (!$job) {
    die("No pending jobs found. Run /home/migrate first!\n");
}

$jobId = $job[0]['id'];
Logger::info("Manually starting test for Job #$jobId");

// Update status to processing
$db->save(['tbl' => 'jobs', 'id' => $jobId, 'status' => 'processing']);

// Run the processor logic (assuming your service has a process method)
// $processor->process($jobId, $job[0]['file_path']);

echo "Test complete. Check app.log for processing output.\n";