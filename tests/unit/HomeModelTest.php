<?php
declare(strict_types=1);

namespace App\Tests;

use App\Models\HomeModel;
use App\Db;
use App\Registry;

class HomeModelTest
{
    private $tester;
    private Db $db;

    public function __construct($tester)
    {
        $this->tester = $tester;
        $this->db = Registry::get(Db::class);
    }

    public function run(): void
    {
        $this->testMigrationCreatesTenants();
        $this->testCsvMethodStructure();
    }

    /**
     * Verifies the migration creates and seeds the tenants table
     */
    private function testMigrationCreatesTenants(): void
    {
        $model = new HomeModel();
        $model->migrate(); // Act

        // 1. Verify table existence via data retrieval
        // Since raw SQL is forbidden, we use find() to probe the table
        try {
            $tenants = $this->db->find(['tbl' => 'tenants', 'limit' => 1]);
            $this->tester->assert(is_array($tenants), "HomeModel: 'tenants' table exists after migration.");
            
            // 2. Verify Seeding
            $foundSeed = false;
            if (!empty($tenants) && $tenants[0]['name'] === 'Sharpishly Global HQ') {
                $foundSeed = true;
            }
            $this->tester->assert($foundSeed, "HomeModel: 'tenants' table seeded with default HQ record.");
            
        } catch (\Exception $e) {
            $this->tester->assert(false, "HomeModel: Migration failed to create or seed 'tenants' table.");
        }
    }

    /**
     * Verifies the dashboard CSV data retrieval
     */
    private function testCsvMethodStructure(): void
    {
        $model = new HomeModel();
        $results = $model->csv();

        $this->tester->assert(is_array($results), "HomeModel: csv() returns an array of recent jobs.");
        
        if (!empty($results)) {
            $this->tester->assert(isset($results[0]['title']), "HomeModel: CSV results contain expected 'title' field.");
        }
    }
}