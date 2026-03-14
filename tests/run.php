<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/src/bootstrap.php';

class TestRunner {
    public int $passed = 0; $failed = 0;
    public function assert($cond, $msg) {
        if ($cond) { echo "✅ PASS: $msg\n"; $this->passed++; }
        else { echo "❌ FAIL: $msg\n"; $this->failed++; }
    }
}
$tester = new TestRunner();
require_once __DIR__ . '/unit/BaseControllerTest.php';
(new \App\Tests\BaseControllerTest($tester))->run();
echo "\nPassed: {$tester->passed} | Failed: {$tester->failed}\n";
