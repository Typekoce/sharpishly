<?php declare(strict_types=1);

namespace App\Services;

class JobRagService {
    private string $storagePath = '/var/www/html/storage/ai/jobs_kb.json';

    public function ingestJobs(array $jobs): void {
        // Simple JSON-based vector storage for current Friday deadline
        $knowledgeBase = [];
        foreach ($jobs as $job) {
            $knowledgeBase[] = [
                'title' => $job['title'],
                'company' => $job['company'],
                'location' => $job['location'],
                'tech_stack' => $job['tech'],
                'summary' => $job['description']
            ];
        }
        file_put_contents($this->storagePath, json_encode($knowledgeBase, JSON_PRETTY_PRINT));
        Logger::log("Ingested " . count($jobs) . " jobs into RAG knowledge base.");
    }

    public function getContextForPrompt(string $query): string {
        $kb = json_decode(file_get_contents($this->storagePath), true) ?: [];
        // For Friday: Simple keyword matching for context retrieval
        $relevant = array_filter($kb, fn($job) => 
            stripos($job['tech_stack'], $query) !== false || stripos($job['title'], $query) !== false
        );
        return json_encode(array_slice($relevant, 0, 3));
    }
}