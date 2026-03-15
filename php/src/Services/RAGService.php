<?php
declare(strict_types=1);

namespace App\Services;

use App\Registry;

class RAGService {
    private OllamaService $ollama;
    private Location $loc;

    public function __construct() {
        $this->ollama = Registry::get(OllamaService::class);
        $this->loc = Registry::get(Location::class);
    }

    public function askWithContext(string $question): string {
        // Look into the storage/documents folder we defined in our Location service
        $docPath = $this->loc->baseDir() . 'storage/documents/*.txt';
        $docs = glob($docPath);
        $context = "";

        foreach ($docs as $doc) {
            $content = file_get_contents($doc);
            // Basic keyword matching for now
            if (stripos($content, $question) !== false) {
                $context .= "\n--- From " . basename($doc) . " ---\n";
                $context .= substr($content, 0, 1000) . "\n";
            }
        }

        $prompt = "Use the following context to answer the question concisely.\n\n" .
                  "Context:\n$context\n\n" .
                  "Question: $question";

        return $this->ollama->ask($prompt, "You are a God Mode Assistant with RAG capabilities.");
    }
}