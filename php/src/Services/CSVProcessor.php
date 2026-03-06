<?php
declare(strict_types=1);

namespace App\Services;

use App\Db;
use Exception;

class CSVProcessor
{
    private Db $db;
    private int $batchSize = 500; // Keep your high-performance batching

    public function __construct()
    {
        $this->db = New Db();
    }

    /**
     * The unified process: Memory efficient streaming + high-speed batching
     */
    public function process(int $jobId, string $relativeFilePath): void
    {
        $filePath = dirname(__DIR__, 2) . '/' . $relativeFilePath;

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
            // Skip header
            fgetcsv($handle);

            // 1. STREAM: Read line-by-line (Memory safe)
            while (($data = fgetcsv($handle)) !== false) {
                $rowCount++;
                
                $batch[] = [
                    'job_id'   => $jobId,
                    'column_1' => $data[0] ?? '',
                    'column_2' => $data[1] ?? '',
                    'column_3' => $data[2] ?? '',
                ];

                // 2. BATCH: Insert in chunks (Database fast)
                if (count($batch) >= $this->batchSize) {
                    $this->insertBatch($batch);
                    $batch = [];
                    
                    // Update the Nervous System via the jobs table
                    $this->db->save([
                        'tbl' => 'jobs',
                        'id' => $jobId,
                        'processed_rows' => $rowCount,
                        'status' => 'processing'
                    ]);
                }
            }

            // Insert remainder
            if (!empty($batch)) {
                $this->insertBatch($batch);
            }

            $this->updateJobStatus($jobId, 'completed', $rowCount);
            Logger::info("Job #$jobId completed successfully", ['total' => $rowCount]);

        } catch (Exception $e) {
            Logger::exception($e);
            $this->updateJobStatus($jobId, 'failed');
        } finally {
            fclose($handle);
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
            $params[] = $row['job_id'];
            $params[] = $row['column_1'];
            $params[] = $row['column_2'];
            $params[] = $row['column_3'];
        }

        $stmt = $this->db->pdo->prepare($sql);
        $stmt->execute($params);
    }

    private function updateJobStatus(int $id, string $status, int $count = null): void
    {
        $data = ['tbl' => 'jobs', 'id' => $id, 'status' => $status];
        if ($count !== null) $data['total_rows'] = $count;
        $this->db->save($data);
    }
}