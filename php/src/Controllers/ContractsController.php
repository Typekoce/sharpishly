<?php
namespace App\Controllers;

use App\Registry;
use App\Services\VectorService;

class ContractsController {
    
    /**
     * @route /php/contracts/upload
     * @brief Handles document upload, chunking, and embedding.
     */
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
            $file = $_FILES['document'];
            $propertyId = $_POST['property_id'] ?? 0;

            // 1. Extract Text (Simplified - assume text/plain or PDF parser)
            $content = file_get_contents($file['tmp_name']);

            // 2. Chunking (Split into manageable pieces for the AI)
            $chunks = str_split($content, 2000); // Simple split for now

            $vectorService = Registry::get('vector_service');
            
            foreach ($chunks as $index => $chunk) {
                // 3. Generate Embedding via Ollama
                $embedding = $vectorService->getEmbedding($chunk);

                // 4. Insert into VectorDB (Qdrant)
                $vectorService->upsert(
                    id: uniqid(), 
                    vector: $embedding, 
                    payload: [
                        "property_id" => $propertyId,
                        "text" => $chunk,
                        "source" => $file['name'],
                        "chunk_index" => $index
                    ]
                );
            }
            echo json_encode(["status" => "Success", "message" => "Document ingested and vectorized."]);
        }
    }

    /**
     * @route /php/contracts/search
     * @brief Semantic search across all uploaded property documents.
     */
    public function search() {
        $query = $_GET['q'] ?? '';
        $vectorService = Registry::get('vector_service');

        // 1. Vectorize the search query
        $queryVector = $vectorService->getEmbedding($query);

        // 2. Perform Similarity Search in Qdrant
        $matches = $vectorService->search($queryVector, limit: 3);

        // 3. Output results for the HUD
        echo json_encode($matches);
    }
}