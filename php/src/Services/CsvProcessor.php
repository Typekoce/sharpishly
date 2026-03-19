<?php
declare(strict_types=1);

namespace App\Services;

use App\Db;
use App\Registry;
use App\Core\Location;
use App\Services\Logger;
use App\Services\VectorService;
use Exception;

/**
 * @file CsvProcessor.php
 * @package App\Services
 * @brief Neural-Enhanced Batch Interrogation Engine.
 */
class CsvProcessor
{
    private Db $db;
    private Location $loc;
    private VectorService $vector;
    private int $batchSize = 100; // Reduced batch size for neural overhead

    public function __construct()
    {
        $this->db     = new Db();
        $this->loc    = Registry::get(Location::class);
        $this->vector = Registry::get(VectorService::class);
    }

    /**
     * Orchestrates the transformation of raw CSV data into Relational + Vector records.
     */
    public function process(int $jobId, string $fileName): void
    {
        $filePath = $this->loc->getStoragePath("uploads/$fileName");

        if (!file_exists($filePath)) {
            Logger::error("CSV Process Failed: File not found", ['path' => $filePath]);
            $this->updateJobStatus($jobId, 'failed');
            return;
        }

        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle); // Capture headers for dynamic mapping
        $rowCount = 0;
        $batch = [];

        Logger::info("Agent starting neural-batch interrogation for Job #$jobId");

        try {
            while (($data = fgetcsv($handle)) !== false) {
                $rowCount++;
                $row = array_combine($headers, $data);
                
                // 1. Prepare the "Searchable String" for the AI context
                $searchableText = $this->prepareSearchableString($row);
                
                $batch[] = [
                    'job_id'          => $jobId,
                    'raw_data'        => json_encode($row),
                    'searchable_text' => $searchableText,
                    'status'          => 'pending_vector' 
                ];

                if (count($batch) >= $this->batchSize) {
                    $this->commitBatch($jobId, $batch, $rowCount);
                    $batch = [];
                }
            }

            // Final cleanup
            if (!empty($batch)) {
                $this->commitBatch($jobId, $batch, $rowCount);
            }

            $this->updateJobStatus($jobId, 'completed', $rowCount);
            $this->shoutProgress($jobId, $rowCount, "Integration successful: $rowCount rows total.");

        } catch (Exception $e) {
            Logger::error("CsvProcessor Critical Failure: " . $e->getMessage());
            $this->updateJobStatus($jobId, 'failed');
        } finally {
            if (is_resource($handle)) fclose($handle);
        }
    }

    /**
     * Commits data to MariaDB and signals the Nervous System.
     */
    private function commitBatch(int $jobId, array $batch, int $currentCount): void
    {
        // Insert into relational table 'csv_contents'
        foreach ($batch as $row) {
            $this->db->save(array_merge(['tbl' => 'csv_contents'], $row));
        }

        $this->updateJobStatus($jobId, 'processing', $currentCount);
        $this->shoutProgress($jobId, $currentCount, "Ingested $currentCount rows. Awaiting Vectorization...");
    }

    /**
     * Converts a CSV row into a descriptive sentence for the VectorDB.
     * Customize this based on your specific CSV headers.
     */
    private function prepareSearchableString(array $row): string
    {
        // Example: If CSV has 'Title', 'Location', 'Price'
        $title    = $row['Title'] ?? $row['name'] ?? 'Unknown Item';
        $location = $row['Location'] ?? $row['city'] ?? 'Unknown Location';
        $desc     = $row['Description'] ?? $row['notes'] ?? '';

        return "Property: $title located in $location. Details: $desc";
    }

    private function shoutProgress(int $jobId, int $count, string $msg): void
    {
        $eventFile = $this->loc->getStoragePath('queue/events.json');
        file_put_contents($eventFile, json_encode([
            'event'  => 'PROGRESS',
            'job_id' => $jobId,
            'val'    => $count,
            'msg'    => $msg,
            'ts'     => time()
        ]));
    }

    private function updateJobStatus(int $id, string $status, int $count = null): void
    {
        $data = ['tbl' => 'jobs', 'id' => $id, 'status' => $status];
        if ($count !== null) $data['processed_rows'] = $count;
        $this->db->save($data);
    }
}