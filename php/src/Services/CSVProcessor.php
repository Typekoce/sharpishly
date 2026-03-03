<?php
declare(strict_types=1);

namespace App\Services;

use App\Db;
use Exception;

class CSVProcessor
{
    private Db $db;
    private int $batchSize = 500;

    public function __construct()
    {
        $this->db = new Db();
    }

    public function process(int $jobId, string $relativeFilePath): void
    {
        // Resolve absolute path (adjust based on your structure)
        $filePath = dirname(__DIR__, 2) . '/' . $relativeFilePath;

        if (!file_exists($filePath)) {
            Logger::error("CSV Process Failed: File not found", ['path' => $filePath]);
            $this->updateJobStatus($jobId, 'failed');
            return;
        }

        $handle = fopen($filePath, 'r');
        $rowCount = 0;
        $batch = [];

        Logger::info("Starting batch processing for Job #$jobId", ['file' => $relativeFilePath]);

        try {
            // Optional: skip header row
            fgetcsv($handle);

            while (($data = fgetcsv($handle)) !== false) {
                $rowCount++;
                
                // Map CSV columns to database columns
                $batch[] = [
                    'job_id'   => $jobId,
                    'column_1' => $data[0] ?? '',
                    'column_2' => $data[1] ?? '',
                    'column_3' => $data[2] ?? '',
                ];

                if (count($batch) >= $this->batchSize) {
                    $this->insertBatch($batch);
                    $batch = [];
                    // Update progress in the jobs table
                    $this->db->save([
                        'tbl' => 'jobs',
                        'id' => $jobId,
                        'processed_rows' => $rowCount
                    ]);
                }
            }

            // Insert remaining records
            if (!empty($batch)) {
                $this->insertBatch($batch);
            }

            $this->updateJobStatus($jobId, 'completed', $rowCount);
            Logger::info("Job #$jobId completed successfully", ['total_rows' => $rowCount]);

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
        $colString = implode(', ', array_map([$this->db, 'escapeIdentifier'], $columns));
        
        // Build the (?, ?, ?, ?), (?, ?, ?, ?) string
        $rowPlaceholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $allPlaceholders = implode(', ', array_fill(0, count($rows), $rowPlaceholders));

        $sql = "INSERT INTO csv_records ($colString) VALUES $allPlaceholders";
        
        // Flatten the data for PDO
        $params = [];
        foreach ($rows as $row) {
            $params[] = $row['job_id'];
            $params[] = $row['column_1'];
            $params[] = $row['column_2'];
            $params[] = $row['column_3'];
        }

        try {
            $stmt = $this->db->pdo->prepare($sql); // Assuming pdo is public in Db class
            $stmt->execute($params);
        } catch (Exception $e) {
            Logger::error("Batch insert failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function updateJobStatus(int $id, string $status, int $count = null): void
    {
        $data = ['tbl' => 'jobs', 'id' => $id, 'status' => $status];
        if ($count !== null) $data['total_rows'] = $count;
        $this->db->save($data);
    }
}