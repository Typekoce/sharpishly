<?php
declare(strict_types=1);

namespace App\Services;

use App\Db;
use Exception;

class CsvProcessor
{
    private Db $db;
    private int $batchSize = 500; 

    public function __construct()
    {
        // Standardizing DB access
        $this->db = new Db();
    }

    public function process(int $jobId, string $relativeFilePath): void
    {
        /**
         * FIX 1: PATH RESOLUTION
         * Using realpath and stripping redundant directory separators 
         * to prevent /var/www/html/php//var/www/... errors.
         */
        $baseDir = dirname(__DIR__, 2); 
        $cleanRelative = ltrim($relativeFilePath, '/');
        
        // Remove 'php/' if the relative path accidentally started with it
        $cleanRelative = str_replace('php/', '', $cleanRelative);
        
        $filePath = $baseDir . '/' . $cleanRelative;

        if (!file_exists($filePath)) {
            Logger::error("CSV Process Failed: File not found", ['path' => $filePath]);
            $this->updateJobStatus($jobId, 'failed');
            return;
        }

        $handle = fopen($filePath, 'r');
        $rowCount = 0;
        $batch = [];

        Logger::info("Agent starting batch interrogation for Job #$jobId");

        try {
            fgetcsv($handle); // Skip header

            while (($data = fgetcsv($handle)) !== false) {
                $rowCount++;
                
                $batch[] = [
                    'job_id'   => $jobId,
                    'column_1' => $data[0] ?? '',
                    'column_2' => $data[1] ?? '',
                    'column_3' => $data[2] ?? '',
                ];

                if (count($batch) >= $this->batchSize) {
                    $this->insertBatch($batch);
                    $batch = [];
                    
                    // Update progress without triggering 'file_path' constraints
                    $this->updateJobStatus($jobId, 'processing', $rowCount);
                }
            }

            if (!empty($batch)) {
                $this->insertBatch($batch);
            }

            $this->updateJobStatus($jobId, 'completed', $rowCount);
            Logger::info("Job #$jobId completed successfully", ['total' => $rowCount]);

        } catch (Exception $e) {
            Logger::exception($e);
            $this->updateJobStatus($jobId, 'failed');
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

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
            $params = array_merge($params, array_values($row));
        }

        // Use standard execute to bypass the array-driven 'save' logic for raw speed
        $this->db->execute($sql, $params);
    }

    /**
     * FIX 2: SQL CONSTRAINT BYPASS
     * To prevent "Field 'file_path' doesn't have a default value",
     * we first fetch the existing record to ensure the full object is sent back
     * to the save() method, fulfilling all NOT NULL requirements.
     */
    private function updateJobStatus(int $id, string $status, int $count = null): void
    {
        // 1. Get existing data to keep file_path and title intact
        $existing = $this->db->find(['tbl' => 'jobs', 'where' => ['id' => $id]]);
        
        if (empty($existing)) {
            Logger::error("Cannot update status: Job #$id not found in DB.");
            return;
        }

        $jobData = $existing[0];
        $jobData['tbl'] = 'jobs';
        $jobData['status'] = $status;
        
        if ($count !== null) {
            $jobData['processed_rows'] = $count;
        }

        $this->db->save($jobData);
    }
}