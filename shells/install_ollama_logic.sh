#!/usr/bin/env bash
set -euo pipefail

# ──────────────────────────────────────────────────────────────────────────────
#   SHARPISHLY: THE TOTAL ORCHESTRATION ENGINE (MUG FACTORY EDITION)
#   Target: B2B/B2C fulfillment, AI Funding, and Self-Healing Diagnostics
# ──────────────────────────────────────────────────────────────────────────────

log_info() { echo -e "\033[0;32m[INFO]\033[0m $*"; }

# 1. Structure Verification
log_info "Verifying Sharpishly Directory Structure..."
mkdir -p website/view/{manufacturer,decorator,client,shop,dashboard}
mkdir -p php/src/{Controllers,Services,Actions,Models,Agents}
mkdir -p php/uploads/mugs

# 2. Dependency Check (GitHub Actions / Zero-Local-Composer)
if [ ! -f "php/src/autoload.php" ] && [ ! -d "vendor" ]; then
    echo "⚠️  Note: Autoloader not found. This is expected as you use GitHub Actions for Composer."
    echo "Ensure your next Push includes the Mug Factory namespace updates."
fi

# 3. AI Bridge: Ollama Intelligence Service
log_info "Injecting Ollama Intelligence Service..."
cat > php/src/Services/OllamaService.php << 'PHP'
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
PHP

# 4. The Orchestrator: ShopController.php
log_info "Deploying ShopController (B2B/B2C Logic)..."
cat > php/src/Controllers/ShopController.php << 'PHP'
<?php
namespace App\Controllers;

use App\Models\TasksModel;
use App\Db;

class ShopController extends BaseController {
    public function placeOrder() {
        $data = json_decode(file_get_contents('php://input'), true);
        $tasks = new TasksModel();
        
        // Orchestrate background printing task
        $tasks->save([
            'tbl' => 'tasks',
            'name' => "Mug Print: " . ($data['club'] ?? 'Retail'),
            'type' => 'manual',
            'action_type' => 'mug_decorator',
            'payload' => json_encode($data),
            'status' => 'active'
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Manufacturing task queued.']);
    }
}
PHP

# 5. Background Logic: MugDecoratorAction.php
log_info "Deploying Manufacturing Action..."
cat > php/src/Actions/MugDecoratorAction.php << 'PHP'
<?php
namespace App\Actions;

use App\Db;
use App\Services\Logger;

class MugDecoratorAction {
    public function execute($payload) {
        $data = json_decode($payload, true);
        $db = new Db();
        
        $quantity = $data['quantity'] ?? 1;
        $club = $data['club'] ?? 'Generic';

        // 1. Check stock in hardware_scans (or merchandise_inventory if added)
        // 2. Simulate Print
        Logger::info("Printer Active: Decorating $quantity mugs for $club.");
        sleep(2); 

        return ['status' => 'completed', 'shipped' => true];
    }
}
PHP

# 6. Self-Healing Health Agent
log_info "Injecting Self-Healing Logic into HealthController..."
cat > php/src/Controllers/HealthController.php << 'PHP'
<?php
namespace App\Controllers;

use App\Services\OllamaService;

class HealthController extends BaseController {
    public function status() {
        $ai = new OllamaService();
        
        // Simple process check for worker-daemon.php
        $workerActive = shell_exec("pgrep -f worker-daemon.php") ? "Active" : "Stopped";
        
        if ($workerActive === "Stopped") {
            shell_exec("php /var/www/html/php/src/worker-daemon.php > /dev/null 2>&1 &");
            $workerActive = "Restarted";
        }

        $report = $ai->ask("System Status: Worker is $workerActive. Give me a 1-sentence funny factory update.");

        header('Content-Type: application/json');
        echo json_encode([
            'worker' => $workerActive,
            'ai_remark' => $report,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
PHP

log_info "God Mode Factory Installed! Proceeding to database update..."
