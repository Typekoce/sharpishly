<?php
namespace App\Services;

class CSVProcessor {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function run(int $jobId, string $filePath): void {
        $handle = fopen($filePath, "r");
        if (!$handle) return;

        // Mark as processing
        $this->updateStatus($jobId, 'processing');

        $batch = [];
        $batchSize = 1000;
        $totalProcessed = 0;

        while (($row = fgetcsv($handle)) !== FALSE) {
            $batch[] = $row;
            $totalProcessed++;

            if (count($batch) >= $batchSize) {
                $this->insertBatch($jobId, $batch);
                $this->updateProgress($jobId, $totalProcessed);
                $batch = [];
            }
        }

        if (!empty($batch)) $this->insertBatch($jobId, $batch);
        $this->updateStatus($jobId, 'completed', $totalProcessed);
        fclose($handle);
    }

    private function insertBatch($jobId, $rows) {
        $this->db->beginTransaction();
        $stmt = $this->db->prepare("INSERT INTO csv_records (job_id, column_1, column_2) VALUES (?, ?, ?)");
        foreach ($rows as $row) {
            $stmt->execute([$jobId, $row[0] ?? '', $row[1] ?? '']);
        }
        $this->db->commit();
    }

    private function updateStatus($id, $status, $total = 0) {
        $sql = "UPDATE jobs SET status = ?, total_rows = ? WHERE id = ?";
        $this->db->prepare($sql)->execute([$status, $total, $id]);
    }

    private function updateProgress($id, $processed) {
        $sql = "UPDATE jobs SET processed_rows = ? WHERE id = ?";
        $this->db->prepare($sql)->execute([$processed, $id]);
    }
}