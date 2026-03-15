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

        // 2. Dev Mode Check: Return static response if APP_ENV is "dev"
        if (getenv('APP_ENV') === 'dev') {
            $this->handleOutput("[DEV MODE] This is a static response. Ollama container was not called.");
            return;
        }

        try {
            // 3. Live AI Interrogation
            $answer = $this->ollama->ask($question);
            $this->handleOutput($answer);
        } catch (Exception $e) {
            Logger::error("Ollama Error: " . $e->getMessage());
            $this->json(['error' => 'AI Link Offline'], 500);
        }
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