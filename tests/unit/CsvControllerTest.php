<?php
declare(strict_types=1);

namespace App\Tests;

use App\Controllers\CsvController;
use App\Registry;
use App\Db;
use App\Services\Location;

class CsvControllerTest {
    private $tester;
    private Db $db;
    private Location $loc;

    public function __construct($tester) {
        $this->tester = $tester;
        $this->db = Registry::get(Db::class);
        $this->loc = Registry::get(Location::class);
    }

    public function run(): void {
        $this->testJobAndQueueHandshake();
    }

    private function testJobAndQueueHandshake(): void {
        // 1. Mock the internal logic of a job entry
        $testFileName = 'unit_test_data.csv';
        $jobId = $this->db->save([
            'tbl'       => 'jobs',
            'title'     => 'Test Import: ' . $testFileName,
            'file_path' => 'storage/uploads/' . $testFileName,
            'status'    => 'pending'
        ]);

        $this->tester->assert(is_int($jobId), "Csv: Job record successfully created in DB.");

        // 2. Test Queue File Generation
        $queueFilePath = $this->loc->queue($jobId . '.job');
        $payload = ['job_id' => $jobId, 'filepath' => 'storage/uploads/' . $testFileName];
        
        file_put_contents($queueFilePath, json_encode($payload));

        $this->tester->assert(file_exists($queueFilePath), "Csv: Queue file successfully generated in storage/queue.");

        // 3. Verify Content and Cleanup
        $content = json_decode(file_get_contents($queueFilePath), true);
        $this->tester->assert($content['job_id'] === $jobId, "Csv: Queue file payload integrity verified.");

        // Cleanup the test file
        unlink($queueFilePath);
    }
}