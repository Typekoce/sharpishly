#!/usr/bin/env bash
set -euo pipefail

# ──────────────────────────────────────────────────────────────────────────────
#   God Mode Ollama + Agent + RAG Install Script
#   Last tested: March 2026
#   Platforms: Linux native, macOS, WSL2 (Ubuntu)
# ──────────────────────────────────────────────────────────────────────────────

echo ""
echo "┌──────────────────────────────────────────────────────────────┐"
echo "│             Sharpishly / God Mode Ollama Installer           │"
echo "│           Ollama + tiny model + embeddings + RAG             │"
echo "└──────────────────────────────────────────────────────────────┘"
echo ""

# ─── Colors ────────────────────────────────────────────────────────────────
RED='\033[0;31m' GREEN='\033[0;32m' YELLOW='\033[1;33m' NC='\033[0m'

# ─── Configurable values ───────────────────────────────────────────────────
DEFAULT_MODEL="llama3.2:3b"               # very small & fast
EMBEDDING_MODEL="nomic-embed-text"        # excellent small embedding model
OLLAMA_PORT=11434

# ─── Helper functions ──────────────────────────────────────────────────────
log_info()  { echo -e "${GREEN}[INFO]${NC} $*"  >&2; }
log_warn()  { echo -e "${YELLOW}[WARN]${NC} $*" >&2; }
log_error() { echo -e "${RED}[ERROR]${NC} $*"   >&2; exit 1; }

command_exists() { command -v "$1" >/dev/null 2>&1; }

# ─── 1. Install Ollama ─────────────────────────────────────────────────────
install_ollama() {
    if command_exists ollama; then
        log_info "Ollama already installed — skipping"
        return
    fi

    log_info "Installing Ollama..."

    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS – use official app or brew
        if command_exists brew; then
            brew install ollama || log_error "brew install ollama failed"
        else
            log_warn "Homebrew not found — downloading Ollama.app manually"
            curl -L https://ollama.com/download/Ollama-darwin.zip -o /tmp/ollama.zip
            unzip /tmp/ollama.zip -d /Applications/
            rm /tmp/ollama.zip
        fi
    else
        # Linux / WSL
        curl -fsSL https://ollama.com/install.sh | sh || log_error "Ollama install script failed"
    fi

    # Start Ollama (background)
    ollama serve >/dev/null 2>&1 &
    sleep 5  # give it time to start

    if ! curl -s http://localhost:$OLLAMA_PORT >/dev/null; then
        log_error "Ollama server not responding on port $OLLAMA_PORT"
    fi

    log_info "Ollama installed and running"
}

# ─── 2. Pull models ────────────────────────────────────────────────────────
pull_models() {
    log_info "Pulling main model: $DEFAULT_MODEL"
    ollama pull "$DEFAULT_MODEL" || log_error "Failed to pull $DEFAULT_MODEL"

    log_info "Pulling embedding model: $EMBEDDING_MODEL"
    ollama pull "$EMBEDDING_MODEL" || log_error "Failed to pull $EMBEDDING_MODEL"

    log_info "Listing available models"
    ollama list
}

# ─── 3. Create minimal RAG + Agent directory structure ─────────────────────
setup_project_structure() {
    PROJECT_DIR="$(pwd)/ollama-godmode"
    mkdir -p "$PROJECT_DIR"/{documents,storage}

    cat > "$PROJECT_DIR/agent.php" << 'PHP'
<?php
// Minimal God Mode Agent using Ollama + RAG

require_once __DIR__ . '/ollama-client.php';

$ollama = new OllamaClient();

$question = $argv[1] ?? readline("Ask me anything: ");

$answer = $ollama->ragAsk($question);

echo "\n\033[1;32mGod Mode:\033[0m $answer\n\n";
PHP

    cat > "$PROJECT_DIR/ollama-client.php" << 'PHP'
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
PHP

    touch "$PROJECT_DIR/documents/example-project-info.txt"
    echo "Sharpishly is a research project combining PHP MVC, Docker, agents, RAG, and more." > "$PROJECT_DIR/documents/example-project-info.txt"

    log_info "Created minimal God Mode project structure in $PROJECT_DIR"
}

# ─── 4. Quick test ─────────────────────────────────────────────────────────
test_setup() {
    log_info "Running quick test..."

    cd "$(pwd)/ollama-godmode" || exit 1

    php agent.php "What is Sharpishly?"
    echo ""
    log_info "If you see an answer — it works!"
}

# ─── Main execution ────────────────────────────────────────────────────────
main() {
    install_ollama
    pull_models
    setup_project_structure
    test_setup

    echo ""
    echo -e "${GREEN}God Mode Ollama + RAG setup complete!${NC}"
    echo ""
    echo "Next steps:"
    echo "  cd ollama-godmode"
    echo "  php agent.php \"your question here\""
    echo ""
    echo "Add more .txt/.md files to documents/ and re-run php agent.php"
    echo ""
}

main
