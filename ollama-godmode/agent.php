<?php
// Minimal God Mode Agent using Ollama + RAG

require_once __DIR__ . '/ollama-client.php';

$ollama = new OllamaClient();

$question = $argv[1] ?? readline("Ask me anything: ");

$answer = $ollama->ragAsk($question);

echo "\n\033[1;32mGod Mode:\033[0m $answer\n\n";
