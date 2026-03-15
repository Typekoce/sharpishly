<?php
declare(strict_types=1);

namespace App\Services;

use App\Db;
use App\Registry;
use App\Services\Location;
use Exception;

class CsvProcessor
{
    private Db $db;
    private Location $loc;
    private int $batchSize = 500; 

    public function __construct()
    {
        $this->db = new Db();
        // Use the verified Location service from the Registry
        $this->loc = Registry::get(Location::class);
    }

    public function process(int $jobId, string $relativeFilePath): void
    {
        /**
         * FIX: PATH RESOLUTION
         * We leverage the Location service to get the absolute base,
         * then ensure we only append the filename or the relative sub-path
         * to avoid the double-rooting seen in the logs.
         */
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
            foreach ($row as $val) {
                $params[] = $val;
            }
        }

        // Standard execute for high-performance batching
        $this->db->execute($sql, $params);
    }

    private function updateJobStatus(int $id, string $status, int $count = null): void
    {
        // Hydrate existing record to satisfy NOT NULL constraints (file_path, etc.)
        $existing = $this->db->find(['tbl' => 'jobs', 'where' => ['id' => $id]]);
        
        if (empty($existing)) {
            Logger::error("Cannot update status: Job #$id not found.");
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