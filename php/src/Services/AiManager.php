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

    public function askWithContext(string $userQuery): string {
        // 1. Retrieve local context from our Job RAG Service
        $rag = new \App\Services\JobRagService();
        $context = $rag->getContextForPrompt($userQuery);

        // 2. Build the System Prompt
        $prompt = "You are the Sharpishly Brain. Use the following Job Data to answer: \n";
        $prompt .= "CONTEXT: $context \n\n";
        $prompt .= "USER QUESTION: $userQuery";

        // 3. Send to Groq (Fastest) or Grok (Truth)
        return $this->callGroq($prompt); 
    }    

}