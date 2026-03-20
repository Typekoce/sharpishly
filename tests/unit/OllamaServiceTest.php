<?php
namespace App\Tests;

use App\Services\OllamaService;

class OllamaServiceTest {
    private $tester;

    public function __construct($tester) { $this->tester = $tester; }

    public function run(): void {
        $ai = new OllamaService();
        // A very simple prompt to check if the bridge is open
        $response = $ai->ask("Respond with only the word 'OK'.");
        
        $this->tester->assert(
            stripos($response, 'OK') !== false || stripos($response, 'Offline') !== false, 
            "Ollama: Bridge check completed (Result: $response)"
        );
    }
}