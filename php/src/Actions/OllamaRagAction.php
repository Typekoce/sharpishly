<?php
namespace App\Actions;

class OllamaRagAction {
    public function handle(array $payload): void {
        echo "🤖 Querying Ollama with prompt: {$payload['prompt']}\n";
    }
}