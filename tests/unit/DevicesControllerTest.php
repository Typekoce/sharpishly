<?php
declare(strict_types=1);

namespace App\Tests;

use App\Controllers\DevicesController;

class DevicesControllerTest 
{
    private $tester;

    public function __construct($tester) 
    {
        $this->tester = $tester;
    }

    public function run(): void 
    {
        $this->testIndexReturnsStructuredJson();
        $this->testActionRejectsInvalidJson();
    }

    /**
     * Verifies the hardware probe returns the expected schema
     */
    private function testIndexReturnsStructuredJson(): void 
    {
        $controller = new DevicesController();

        // Capture the output (since it uses $this->json() which echoes)
        ob_start();
        $controller->index();
        $output = ob_get_clean();

        $data = json_decode($output, true);

        $this->tester->assert(
            isset($data['module']) && $data['module'] === 'Devices',
            "Devices: Module name is correctly identified as 'Devices'."
        );

        $this->tester->assert(
            isset($data['devices']['usb']) && is_array($data['devices']['usb']),
            "Devices: USB device list is present as an array."
        );

        $this->tester->assert(
            isset($data['devices']['network']) && is_array($data['devices']['network']),
            "Devices: Network device list is present as an array."
        );
    }

    /**
     * Verifies that the action endpoint handles bad input gracefully
     */
    private function testActionRejectsInvalidJson(): void 
    {
        $controller = new DevicesController();

        ob_start();
        $controller->action(); // Calling without setting php://input
        $output = ob_get_clean();

        $data = json_decode($output, true);

        $this->tester->assert(
            isset($data['status']) && $data['status'] === 'error',
            "Devices: Action correctly returns an error status for empty/invalid payloads."
        );
    }
}