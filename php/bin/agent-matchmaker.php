<?php
require 'vendor/autoload.php';

use App\Services\JobRagService;
use App\Services\AiManager;
use App\Services\Logger;

// 1. Get tenants with low balance or "Looking" status
$db = \App\Db::connect();
$tenants = $db->query("SELECT * FROM tenants WHERE balance < 0 OR status = 'searching'")->fetchAll();

$rag = new JobRagService();
$ai = new AiManager();

foreach ($tenants as $tenant) {
    // 2. Query the RAG for jobs matching the tenant's known tech stack
    $context = $rag->getContextForPrompt($tenant['tech_stack']);
    
    // 3. Ask Groq if there's a match worth notifying
    $prompt = "Tenant {$tenant['name']} knows {$tenant['tech_stack']}. Based on these jobs: $context, is there a perfect match? If yes, provide a 1-sentence alert.";
    $match = $ai->ask($prompt, 'groq');

    if (str_contains($match, 'Match Found')) {
        Logger::log("🤖 AGENT: Found opportunity for {$tenant['name']}: $match", "NOTIFY");
    }
}