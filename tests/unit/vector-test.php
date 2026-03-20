<?php
declare(strict_types=1);

/**
 * @file vector-test.php
 * @package Tests\Unit
 * @brief Neural Link Quality Gate.
 * Verifies the connection between PHP, Ollama (Embeddings), and Qdrant (VectorDB).
 */

// Adjusting path to reach the source bootstrap from tests/unit/
require_once __DIR__ . '/../../php/src/bootstrap.php';

use App\Registry;
use App\Services\VectorService;
use App\Services\Logger;

echo "🧪 [QUALITY GATE] Starting Neural Link Test...\n";
echo "----------------------------------------------\n";

try {
    // 1. Dependency Resolution
    $vectorService = Registry::get(VectorService::class);
    if (!$vectorService) {
        throw new Exception("VectorService not found in Registry.");
    }

    // 2. Test Embedding (Ollama)
    echo "  -> Requesting Embedding (Ollama)... ";
    $testText = "Modern 3-bedroom apartment in Manchester city center.";
    $vector = $vectorService->getEmbedding($testText);

    if (empty($vector)) {
        throw new Exception("Ollama returned an empty response. Check if 'nomic-embed-text' is pulled.");
    }
    
    $dim = count($vector);
    if ($dim !== 768) {
        throw new Exception("Vector dimension mismatch. Expected 768 (nomic), got $dim.");
    }
    echo "✅ [Dimension: $dim]\n";

    // 3. Test Upsert (Qdrant)
    echo "  -> Upserting Test Point (Qdrant)... ";
    $testId = 999999; // Isolated ID for testing
    $payload = [
        'text' => $testText,
        'type' => 'unit_test',
        'timestamp' => time()
    ];
    
    $success = $vectorService->upsert($testId, $vector, $payload);
    
    if (!$success) {
        throw new Exception("Qdrant refused the upsert. Is the 'properties' collection created?");
    }
    echo "✅\n";

    // 4. Test Semantic Search (Retrieval)
    echo "  -> Testing Semantic Retrieval...    ";
    // We search using the same vector we just created
    $matches = $vectorService->search($vector, 1);
    
    if (empty($matches)) {
        throw new Exception("Qdrant search returned zero results.");
    }

    $topMatchId = $matches[0]['id'] ?? null;
    if ($topMatchId !== $testId) {
        throw new Exception("Search integrity failure. Expected ID $testId, got " . ($topMatchId ?? 'NULL'));
    }
    echo "✅ [Match Verified]\n";

    echo "----------------------------------------------\n";
    echo "🚀 NEURAL LINK STABLE: System is ready for RAG.\n\n";
    exit(0);

} catch (Exception $e) {
    echo "\n❌ NEURAL LINK FAILED!\n";
    echo "   Error: " . $e->getMessage() . "\n";
    Logger::error("Vector Unit Test Failure: " . $e->getMessage());
    exit(1);
}