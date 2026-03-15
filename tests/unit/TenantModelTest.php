<?php
declare(strict_types=1);

namespace App\Tests;

use App\Models\TenantModel;
use App\Registry;
use App\Db;

class TenantModelTest {
    private $tester;
    private Db $db;

    public function __construct($tester) {
        $this->tester = $tester;
        $this->db = Registry::get(Db::class);
    }

    public function run(): void {
        $this->testGetAllTenants();
    }

    private function testGetAllTenants(): void {
        $model = new TenantModel();
        
        // 1. Basic type check
        $tenants = $model->getAllTenants();
        $this->tester->assert(is_array($tenants), "TenantModel: getAllTenants() returns an array.");

        // 2. Integration check: Insert a dummy tenant and verify it exists
        $testName = "Test Corp " . time();
        $this->db->save([
            'tbl' => 'tenants',
            'name' => $testName,
            'status' => 'active'
        ]);

        $tenantsAfterInsert = $model->getAllTenants();
        $found = false;
        foreach ($tenantsAfterInsert as $tenant) {
            if (($tenant['name'] ?? '') === $testName) {
                $found = true;
                break;
            }
        }

        $this->tester->assert($found, "TenantModel: Successfully retrieved newly inserted test tenant.");
    }
}
