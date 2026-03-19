<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Registry;
use App\Services\OllamaService;
use App\Services\VectorService;
use App\Services\Logger;
use App\Core\Location; // Handles all directory paths
use Exception;

/**
 * @file OllamaController.php
 * @package App\Controllers
 * @brief The Neural Gateway for Sharpishly.
 * Handles RAG (Retrieval-Augmented Generation) across logs and vector data.
 */
class OllamaController extends BaseController 
{
    private OllamaService $ollama;
    private VectorService $vector;
    private Location $location;

    public function __construct() 
    {
        parent::__construct();
        $this->ollama   = Registry::get(OllamaService::class);
        $this->vector   = Registry::get(VectorService::class);
        $this->location = Registry::get(Location::class);
    }

    /**
     * Renders the AI HUD Interface
     */
    public function index(): void
    {
        $data = [
            'title'     => 'Ollama AI',
            'dashboard' => 'Neural Engine',
        ];

        $views = [
           'header' => 'test/header',
           'main'   => 'test/test', 
           'footer' => 'test/footer'
        ];

        $this->render($data, $views);
    }

    /**
     * Primary AI Endpoint: Handles Question -> Search -> Augment -> Answer
     */
    public function response(): void
    {
        $question = $this->getQuestion();

        if (empty($question)) {
            $this->json(['error' => 'Question is empty'], 400);
            return;
        }

        // Dev Mode Bypass
        if (getenv('APP_ENV') === 'dev') {
            $this->handleOutput("[DEV MODE] AI Link active but bypassed.");
            return;
        }

        try {
            // 1. RETRIEVE: Get Semantic Context from VectorDB (Properties)
            $queryVector = $this->vector->getEmbedding($question);
            $semanticMatches = $this->vector->search($queryVector, 3);
            
            // 2. RETRIEVE: Get System Context (Logs/Files)
            $systemContext = $this->gatherSystemContext($question);

            // 3. AUGMENT: Construct the Hybrid Prompt
            $prompt = $this->buildSuperPrompt($question, $semanticMatches, $systemContext);

            // 4. GENERATE: Live AI Interrogation
            $answer = $this->ollama->ask($prompt);
            $this->handleOutput($answer);

        } catch (Exception $e) {
            Logger::error("Ollama Controller Error: " . $e->getMessage());
            $this->json(['error' => 'Neural Link Interrupted'], 500);
        }
    }

    /**
     * Builds the final prompt for the LLM
     */
    private function buildSuperPrompt(string $question, array $matches, string $system): string
    {
        $context = "";
        
        if (!empty($matches)) {
            $context .= "--- RELEVANT CSV DATA ---\n";
            foreach ($matches as $m) {
                // Assuming payload contains 'description' or 'row_data'
                $context .= "- " . ($m['payload']['text'] ?? 'Data found') . "\n";
            }
        }

        if (!empty($system)) {
            $context .= "\n--- SYSTEM STATUS ---\n$system";
        }

        return "You are the Sharpishly AI. Answer accurately based on the context provided.
                CONTEXT:
                $context
                
                USER QUESTION:
                $question";
    }

    /**
     * Logic for scanning logs and project maps via Location class
     */
    private function gatherSystemContext(string $question): string
    {
        $q = strtolower($question);
        $context = "";

        // Log Context
        if (str_contains($q, 'log') || str_contains($q, 'error')) {
            $logFile = $this->location->getStoragePath('logs/app.log');
            if (file_exists($logFile)) {
                $context .= "--- LATEST LOGS ---\n" . $this->getLastLines($logFile, 10) . "\n";
            }
        }

        // Structure Context
        if (str_contains($q, 'code') || str_contains($q, 'structure')) {
            $context .= "--- SRC MAP ---\n" . $this->getProjectMap() . "\n";
        }

        return $context;
    }

    private function getLastLines(string $file, int $n): string
    {
        $lines = file($file);
        return implode("", array_slice($lines, -$n));
    }

    private function getProjectMap(): string
    {
        $path = $this->location->getPhpPath('src');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $map = "";
        foreach ($files as $file) {
            if ($file->isFile()) {
                $map .= $file->getFilename() . "\n";
            }
        }
        return substr($map, 0, 500);
    }

    private function getQuestion(): string
    {
        if (php_sapi_name() === 'cli') {
            global $argv;
            return $argv[1] ?? '';
        }
        $input = json_decode(file_get_contents('php://input'), true);
        return $input['question'] ?? '';
    }

    private function handleOutput(string $answer): void
    {
        if (php_sapi_name() === 'cli') {
            echo "\n[AI]: $answer\n\n";
        } else {
            $this->json(['status' => 'success', 'answer' => $answer]);
        }
    }
}