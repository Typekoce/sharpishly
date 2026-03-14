<?php
declare(strict_types=1);

namespace App\Tests;

use App\Registry;
use App\Models\HomeModel;
use App\Db;

class HomeModelTest {
    private $tester;
    private HomeModel $model;
    private Db $db;

    public function __construct($tester) {
        $this->tester = $tester;
        // Ensure Registry is primed before model instantiation
        $this->db = Registry::get(Db::class);
        $this->model = new HomeModel();
    }

    public function run(): void {
        $this->testMigrationOrchestration();
        $this->testCsvDataStructure();
    }

    /**
     * Verifies that the migrate() method returns a success report
     * and actually creates the physical tables in the DB.
     */
    private function testMigrationOrchestration(): void {
        $report = $this->model->migrate();
        
        $this->tester->assert(
            str_contains($report, 'Migration completed successfully'),
            "HomeModel: Migration report indicates success."
        );

        // Physical check: Did the merchandise_inventory table actually land?
        $result = $this->db->query("SHOW TABLES LIKE 'merchandise_inventory'");
        $this->tester->assert(
            count($result) > 0,
            "HomeModel: Table 'merchandise_inventory' physically exists after migration."
        );
    }

    /**
     * Verifies the csv() method returns the expected array structure
     */
    private function testCsvDataStructure(): void {
        $data = $this->model->csv();
        
        $this->tester->assert(
            is_array($data),
            "HomeModel: csv() returns an array."
        );

        // Since migrate() seeds a job, we should have at least one record
        if (count($data) > 0) {
            $firstRow = $data[0];
            $this->tester->assert(
                isset($firstRow['file_path']) && isset($firstRow['status']),
                "HomeModel: CSV data contains expected columns (file_path, status)."
            );
        }
    }
}