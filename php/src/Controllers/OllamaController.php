<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Registry;
use App\Services\OllamaService;
use App\Services\Logger;
use Exception;

class OllamaController extends BaseController 
{
    private OllamaService $ollama;
    private string $rootDir = '/var/www/html/';

    public function __construct() 
    {
        parent::__construct();
        $this->ollama = Registry::get(OllamaService::class);
    }

    public function index(): void
    {
        $data = [
            'title'     => 'Ollama AI',
            'dashboard' => 'Neural Engine',
            'jobs'      => [],
        ];

        $views = [
           'header' => 'test/header',
           'main'   => 'test/test', 
           'footer' => 'test/footer'
        ];

        $this->render($data, $views);
    }

    public function response(): void
    {
        // 1. Determine the Question
        if (php_sapi_name() === 'cli') {
            global $argv;
            $question = $argv[1] ?? readline("Ask me anything: ");
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
            $question = $input['question'] ?? '';
        }

        if (empty($question)) {
            $this->json(['error' => 'Question is empty'], 400);
            return;
        }

        // 2. Dev Mode Check
        if (getenv('APP_ENV') === 'dev') {
            $this->handleOutput("[DEV MODE] Static response enabled.");
            return;
        }

        try {
            // 3. Inject Project Context
            $context = $this->gatherContext($question);
            
            $prompt = !empty($context) 
                ? "CONTEXT FROM PROJECT FILES:\n$context\n\nUSER QUESTION:\n$question" 
                : $question;

            // 4. Live AI Interrogation
            $answer = $this->ollama->ask($prompt);
            $this->handleOutput($answer);

        } catch (Exception $e) {
            Logger::error("Ollama Error: " . $e->getMessage());
            $this->json(['error' => 'AI Link Offline'], 500);
        }
    }

    /**
     * Scans the project based on keywords in the question
     */
    private function gatherContext(string $question): string
    {
        $q = strtolower($question);
        $context = "";

        // Log Context
        if (str_contains($q, 'log') || str_contains($q, 'error') || str_contains($q, 'failed')) {
            $logPath = $this->rootDir . 'storage/logs/app.log';
            $errPath = $this->rootDir . 'storage/logs/php_error.log';
            
            if (file_exists($logPath)) {
                $context .= "--- APP LOG (Last 15 lines) ---\n" . $this->getLastLines($logPath, 15) . "\n";
            }
            if (file_exists($errPath)) {
                $context .= "--- PHP ERRORS (Last 15 lines) ---\n" . $this->getLastLines($errPath, 15) . "\n";
            }
        }

        // Structure/Code Context
        if (str_contains($q, 'code') || str_contains($q, 'file') || str_contains($q, 'structure')) {
            $context .= "--- PROJECT STRUCTURE ---\n" . $this->getMap() . "\n";
        }

        return $context;
    }

    /**
     * Reads the end of a log file safely
     */
    private function getLastLines(string $file, int $n): string
    {
        $data = file($file);
        return implode("", array_slice($data, -$n));
    }

    /**
     * Maps the core src directory
     */
    private function getMap(): string
    {
        $path = $this->rootDir . 'php/src';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $map = "";
        foreach ($files as $file) {
            if ($file->isFile()) {
                $map .= str_replace($this->rootDir, '', $file->getPathname()) . "\n";
            }
        }
        return substr($map, 0, 1000); // Token safety cap
    }

    /**
     * Helper to unify CLI and Web output
     */
    private function handleOutput(string $answer): void
    {
        if (php_sapi_name() === 'cli') {
            echo "\n\033[1;32mGod Mode:\033[0m $answer\n\n";
        } else {
            $this->json([
                'status' => 'success',
                'answer' => $answer
            ]);
        }
    }
}