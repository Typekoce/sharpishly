<?php
declare(strict_types=1);

namespace App\Tests;

use App\Controllers\HomeController;

class HomeControllerTest {
    private $tester;

    public function __construct($tester) {
        $this->tester = $tester;
    }

    public function run(): void {
        $this->testCsvJsonResponse();
        $this->testMigrateOutput();
    }

    /**
     * Verifies the csv() method returns valid JSON
     */
    private function testCsvJsonResponse(): void {
        $controller = new HomeController();

        ob_start();
        try {
            $controller->csv();
        } catch (\Exception $e) {
            // Catching 'die' or 'exit' if it occurs in older code
        }
        $output = ob_get_clean();

        $data = json_decode($output, true);
        $this->tester->assert(
            json_last_error() === JSON_ERROR_NONE,
            "HomeController: csv() output is valid JSON."
        );
    }

    /**
     * Verifies the migrate() method triggers the migration report
     */
    private function testMigrateOutput(): void {
        $controller = new HomeController();

        ob_start();
        $controller->migrate();
        $output = ob_get_clean();

        $this->tester->assert(
            str_contains($output, 'Migration Report'),
            "HomeController: migrate() renders the migration report."
        );
    }
}