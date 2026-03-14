<?php
declare(strict_types=1);

namespace App\Tests;

use App\Services\Location;

class LocationTest {
    private $tester;
    private Location $loc;

    /**
     * Accepts the custom TestRunner instance
     */
    public function __construct($tester) {
        $this->tester = $tester;
        $this->loc = new Location();
    }

    /**
     * Executes all service-level tests for Location
     */
    public function run(): void {
        $this->testBaseDirReturnsProjectRoot();
        $this->testUploadsReturnsCorrectPath();
        $this->testQueuePathHandlesLeadingSlash();
        $this->testRelativeStripsStoragePath();
        $this->testStorageHelperPath();
    }

    private function testBaseDirReturnsProjectRoot(): void {
        $expected = '/var/www/html/';
        $this->tester->assert(
            $this->loc->baseDir() === $expected,
            "Location: baseDir() resolves project root correctly"
        );
    }

    private function testUploadsReturnsCorrectPath(): void {
        $file = 'test.csv';
        $expected = '/var/www/html/storage/uploads/test.csv';
        $this->tester->assert(
            $this->loc->uploads($file) === $expected,
            "Location: uploads() resolves correct storage path"
        );
    }

    private function testQueuePathHandlesLeadingSlash(): void {
        $file = '/job_123.json';
        $expected = '/var/www/html/storage/queue/job_123.json';
        $this->tester->assert(
            $this->loc->queue($file) === $expected,
            "Location: queue() handles leading slashes via ltrim"
        );
    }

    private function testRelativeStripsStoragePath(): void {
        $absolute = '/var/www/html/storage/uploads/user_1/data.csv';
        $expected = 'uploads/user_1/data.csv';
        $this->tester->assert(
            $this->loc->relative($absolute) === $expected,
            "Location: relative() correctly strips the storage base path"
        );
    }

    private function testStorageHelperPath(): void {
        $expected = '/var/www/html/storage/custom/path.txt';
        $this->tester->assert(
            $this->loc->storage('custom/path.txt') === $expected,
            "Location: storage() helper correctly maps generic paths"
        );
    }
}