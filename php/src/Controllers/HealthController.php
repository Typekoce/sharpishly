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
