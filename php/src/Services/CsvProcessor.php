<?php
declare(strict_types=1);

namespace App\Services;

use App\Db;
use App\Registry;
use App\Services\Location;
use App\Services\Logger;
use Exception;

/**
 * @file CsvProcessor.php
 * @package App\Services
 * @brief High-performance Batch Interrogation Engine.
 *
 * This service handles the heavy lifting of parsing uploaded CSV files,
 * batching them into atomic SQL transactions, and notifying the 
 * NervousSystemController (SSE) of real-time progress.
 */
class CsvProcessor
{
    private Db $db;
    private Location $loc;
    private int $batchSize = 500; 

    public function __construct()
    {
        $this->db = new Db();
        $this->loc = Registry::get(Location::class);
    }

    /**
     * Orchestrates the transformation of raw CSV data into persisted records.
     * * @param int $jobId The ID from the 'jobs' table.
     * @param string $relativeFilePath The path provided by the upload controller.
     */
    public function process(int $jobId, string $relativeFilePath): void
    {
        // Path Resolution: Ensure we are looking in the correct absolute directory
        $fileName = basename($relativeFilePath);
        $filePath = $this->loc->uploads() . '/' . $fileName;

        if (!file_exists($filePath)) {
            Logger::error("CSV Process Failed: File not found", [
                'job_id' => $jobId,
                'attempted_path' => $filePath
            ]);
            $this->updateJobStatus($jobId, 'failed');
            return;
        }

        $handle = fopen($filePath, 'r');
        $rowCount = 0;
        $batch = [];

        Logger::info("Agent starting batch interrogation for Job #$jobId");

        try {
            // Skip header row
            fgetcsv($handle); 

            while (($data = fgetcsv($handle)) !== false) {
                $rowCount++;
                
                $batch[] = [
                    'job_id'   => $jobId,
                    'column_1' => $data[0] ?? '',
                    'column_2' => $data[1] ?? '',
                    'column_3' => $data[2] ?? '',
                ];

                // When the buffer hits the limit, commit to DB and "Shout" to the HUD
                if (count($batch) >= $this->batchSize) {
                    $this->insertBatch($batch);
                    $batch = [];
                    
                    // Update DB and Pulse the Nervous System
                    $this->updateJobStatus($jobId, 'processing', $rowCount);
                    $this->shoutProgress($jobId, $rowCount, "Integrating batch: $rowCount rows...");
                }
            }

            // Final cleanup for remaining rows
            if (!empty($batch)) {
                $this->insertBatch($batch);
                $this->updateJobStatus($jobId, 'completed', $rowCount);
                $this->shoutProgress($jobId, $rowCount, "Integration successful: $rowCount rows total.");
            }

            Logger::info("Job #$jobId completed successfully", ['total' => $rowCount]);

        } catch (Exception $e) {
            Logger::exception($e);
            $this->updateJobStatus($jobId, 'failed');
            $this->shoutProgress($jobId, 0, "Critical Failure: " . $e->getMessage());
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    /**
     * Executes a single, high-speed multi-row INSERT.
     */
    private function insertBatch(array $rows): void
    {
        if (empty($rows)) return;

        $columns = ['job_id', 'column_1', 'column_2', 'column_3'];
        $colString = implode(', ', $columns);
        
        $rowPlaceholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $allPlaceholders = implode(', ', array_fill(0, count($rows), $rowPlaceholders));

        $sql = "INSERT INTO csv_records ($colString) VALUES $allPlaceholders";
        
        $params = [];
        foreach ($rows as $row) {
            foreach ($row as $val) {
                $params[] = $val;
            }
        }

        $this->db->execute($sql, $params);
    }

    /**
     * Drops a JSON signal into the queue for the NervousSystemController (SSE).
     */
    private function shoutProgress(int $jobId, int $count, string $msg): void
    {
        $eventFile = $this->loc->queue('events.json');
        
        file_put_contents($eventFile, json_encode([
            'event' => 'PROGRESS',
            'job_id' => $jobId,
            'val'   => $count,
            'msg'   => $msg,
            'ts'    => time()
        ]));

        // Small delay ensures the Controller has time to consume the file before the next batch
        usleep(50000); 
    }

    /**
     * Persists the current state of the job to the database.
     */
    private function updateJobStatus(int $id, string $status, int $count = null): void
    {
        $existing = $this->db->find(['tbl' => 'jobs', 'where' => ['id' => $id]]);
        
        if (empty($existing)) return;

        $jobData = $existing[0];
        $jobData['tbl'] = 'jobs';
        $jobData['status'] = $status;
        
        if ($count !== null) {
            $jobData['processed_rows'] = $count;
        }

        $this->db->save($jobData);
    }
}