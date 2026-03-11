<?php
namespace App\Services;

class AiManager {
    public function ask(string $prompt, string $provider = 'ollama'): string {
        return match($provider) {
            'chatgpt' => $this->callChatGPT($prompt),
            'grok'    => $this->callGrok($prompt),
            default   => (new \App\Models\OllamaModel())->generate($prompt),
        };
    }

    private function callChatGPT($prompt) {
        // endpoint: https://api.openai.com/v1/chat/completions
        // model: gpt-4-turbo
        return "[ChatGPT Response for: $prompt]";
    }

    private function callGrok($prompt) {
        // endpoint: https://api.x.ai/v1/chat/completions
        // model: grok-beta
        return "[Grok Response for: $prompt]";
    }
}