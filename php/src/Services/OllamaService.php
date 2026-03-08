<?php
namespace App\Services;

class OllamaService {
    private string $host = 'http://host.docker.internal:11434';
    private string $model = 'llama3.2:3b';

    public function ask(string $prompt, string $system = "You are a Business Intelligence Agent."): string {
        $payload = [
            'model' => $this->model,
            'prompt' => $prompt,
            'system' => $system,
            'stream' => false
        ];

        $ch = curl_init("{$this->host}/api/generate");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);

        $response = curl_exec($ch);
        $data = json_decode((string)$response, true);
        curl_close($ch);

        return $data['response'] ?? "Neural Link Offline: Check Ollama Status";
    }
}
