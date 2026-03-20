<?php
namespace App\Actions;

use App\Services\AI\RAGAgent; // Assuming this is your namespace

class OllamaRagAction {
    public function execute(array $payload): array {
        try {
            $rag = new RAGAgent();
            // Optional: Pass context or limit from payload if needed
            $result = $rag->ask($payload['prompt']);
            
            return [
                'success' => true, 
                'output'  => $result,
                'tokens'  => $result['total_duration'] ?? 0 // Useful for logging
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false, 
                'error'   => $e->getMessage()
            ];
        }
    }
}