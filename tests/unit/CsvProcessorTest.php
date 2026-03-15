<?php
declare(strict_types=1);

namespace App\Tests;

use App\Services\CsvProcessor;
use App\Registry;
use App\Db;
use App\Services\Location;

class CsvProcessorTest {
    private $tester;
    private Db $db;
    private Location $loc;

    public function __construct($tester) {
        $this->tester = $tester;
        // Using Registry to ensure we use the same instances as the app
        $this->db = Registry::get(Db::class);
        $this->loc = Registry::get(Location::class);
    }

    public function run(): void {
        $this->testCsvProcessingLifecycle();
    }

    private function testCsvProcessingLifecycle(): void {
        // 1. Setup: Create a mock CSV file in the uploads directory
        $testFileName = 'unit_test_interrogation.csv';
        $relativeUploadPath = 'storage/uploads/' . $testFileName;
        $absPath = $this->loc->baseDir() . $relativeUploadPath;

        $content = "id,name,description\n";
        $content .= "1,Puma,High-speed interrogation\n";
        $content .= "2,Cougar,Legacy data processing\n";
        
        // Ensure the directory exists
        if (!is_dir(dirname($absPath))) {
            mkdir(dirname($absPath), 0777, true);
        }
        file_put_contents($absPath, $content);

        // 2. Setup: Create the job record in the database
        // We include 'file_path' to satisfy the NOT NULL constraint discovered in the logs
        $jobId = $this->db->save([
            'tbl'       => 'jobs',
            'title'     => 'Unit Test: CsvProcessor',
            'file_path' => $relativeUploadPath,
            'status'    => 'pending'
        ]);

        $this->tester->assert(is_int($jobId), "CsvProcessor: Mock job created with ID $jobId.");

        // 3. Execution: Run the processor
        $processor = new CsvProcessor();
        $processor->process($jobId, $relativeUploadPath);

        // 4. Verification: Check Job Metadata
        $jobResult = $this->db->find([
            'tbl'   => 'jobs', 
            'where' => ['id' => $jobId]
        ]);
        
        $job = $jobResult[0] ?? [];
        
        $this->tester->assert(
            ($job['status'] ?? '') === 'completed', 
            "CsvProcessor: Job status correctly transitioned to 'completed'."
        );
        
        $this->tester->assert(
            (int)($job['processed_rows'] ?? 0) === 2, 
            "CsvProcessor: Row count (2) correctly recorded in jobs table."
        );

        // 5. Verification: Check Data Integrity in csv_records
        $records = $this->db->find([
            'tbl'   => 'csv_records', 
            'where' => ['job_id' => $jobId]
        ]);

        $this->tester->assert(
            count($records) === 2, 
            "CsvProcessor: Successfully inserted 2 records into csv_records table."
        );

        if (!empty($records)) {
            $this->tester->assert(
                $records[0]['column_1'] === '1' && $records[0]['column_2'] === 'Puma', 
                "CsvProcessor: Data integrity check passed for first row."
            );
        }

        // 6. Cleanup: Remove the test file
        if (file_exists($absPath)) {
            unlink($absPath);
        }
    }
}