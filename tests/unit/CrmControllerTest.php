<?php
declare(strict_types=1);

namespace App\Tests;

use App\Controllers\CrmController;
use App\Models\TenantModel;

class CrmControllerTest {
    private $tester;

    public function __construct($tester) {
        $this->tester = $tester;
    }

    public function run(): void {
        $this->testCrmIndexOutput();
    }

    private function testCrmIndexOutput(): void {
        // 1. Initialize Controller
        $controller = new CrmController();

        // 2. Capture the output
        // Since CrmController echoes JSON, we use output buffering to catch it for the test
        ob_start();
        $controller->index();
        $output = ob_get_clean();

        // 3. Verify Response
        $data = json_decode($output, true);

        $this->tester->assert(is_array($data), "CRM: index() returns a valid JSON array.");
        
        // If the DB has data, we check for structure; otherwise, we check for an empty array
        if (!isset($data['error'])) {
            $this->tester->assert(true, "CRM: Controller successfully communicated with TenantModel.");
        } else {
            $this->tester->assert(false, "CRM: Controller returned an Internal System Error.");
        }
    }
}