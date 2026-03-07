<?php
// Simple Ollama client with basic RAG

class OllamaClient {
    private string $host = 'http://127.0.0.1:11434';
    private string $chatModel = 'llama3.2:3b';
    private string $embedModel = 'nomic-embed-text';

    public function generate(string $prompt): string {
        $payload = [
            'model'  => $this->chatModel,
            'prompt' => $prompt,
            'stream' => false
        ];

        $ch = curl_init("{$this->host}/api/generate");
        curl_setopt_array($ch, [
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);

        $resp = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($resp, true);
        return trim($data['response'] ?? 'No answer');
    }

    public function embed(string $text): array {
        $payload = ['model' => $this->embedModel, 'prompt' => $text];
        $ch = curl_init("{$this->host}/api/embeddings");
        curl_setopt_array($ch, [
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);

        $resp = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($resp, true);
        return $data['embedding'] ?? [];
    }

    public function ragAsk(string $question): string {
        // Very basic in-memory RAG — replace with real vector DB later
        $docs = glob(__DIR__ . '/documents/*.txt');
        $context = "";

        foreach ($docs as $doc) {
            $content = file_get_contents($doc);
            if (stripos($content, $question) !== false) {
                $context .= "\n\nFrom " . basename($doc) . ":\n" . substr($content, 0, 800);
            }
        }

        $prompt = "Context:\n$context\n\nQuestion: $question\n\nAnswer concisely using only the context if possible.";

        return $this->generate($prompt);
    }
}
