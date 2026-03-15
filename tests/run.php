<?php
declare(strict_types=1);

/**
 * SHARPISHLY TEST RUNNER
 * Location: /tests/run.php
 */

require_once __DIR__ . '/../php/src/bootstrap.php';

class TestRunner {
    public int $passed = 0;
    public int $failed = 0;

    public function assert(bool $cond, string $msg): void {
        if ($cond) {
            echo "✅ PASS: $msg\n";
            $this->passed++;
        } else {
            echo "❌ FAIL: $msg\n";
            $this->failed++;
        }
    }

    public function report(): void {
        echo "\n------------------------------------------\n";
        echo "TOTAL RESULTS\n";
        echo "Passed: {$this->passed} | Failed: {$this->failed}\n";
        echo "------------------------------------------\n";
        
        if ($this->failed > 0) {
            exit(1); 
        }
    }
}

$tester = new TestRunner();

try {
    echo "🚀 Starting Sharpishly Unit Tests...\n\n";

    // --- Hardware & Systems ---
    echo "\n--- Hardware & Systems ---\n";
    (new \App\Tests\DevicesControllerTest($tester))->run();

    // 1. Infrastructure & Services
    echo "--- Services ---\n";
    (new \App\Tests\LocationTest($tester))->run();
    (new \App\Tests\VaultTest($tester))->run(); // Add this line

    // 2. DB Layer
    echo "\n--- Database ---\n";
    (new \App\Tests\DbTest($tester))->run();

    // 3. MVC Layer & CRM
    echo "\n--- MVC Core & CRM ---\n";
    (new \App\Tests\HomeModelTest($tester))->run();
    (new \App\Tests\TenantModelTest($tester))->run();
    (new \App\Tests\BaseControllerTest($tester))->run();
    (new \App\Tests\CrmControllerTest($tester))->run(); 

    // 4. CSV Engine
    echo "\n--- CSV Engine ---\n";
    (new \App\Tests\CsvControllerTest($tester))->run();
    (new \App\Tests\CsvProcessorTest($tester))->run(); 

    // 5. AI & Intelligence (New)
    echo "\n--- AI & Intelligence ---\n";
    (new \App\Tests\OllamaServiceTest($tester))->run();

    // 6. Multi-Model Adapters (Tomorrow's Mission)
    echo "\n--- AI Agent Adapters (Mocks) ---\n";
    // (new \App\Tests\AgentAdapterTest($tester))->run();

} catch (\Throwable $e) {
    echo "🚫 CRITICAL TEST ERROR: " . $e->getMessage() . "\n";
    echo "In " . $e->getFile() . " on line " . $e->getLine() . "\n";
    exit(1);
}

$tester->report();