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

    public function pulse() {
        header('Content-Type: application/json');
        
        $stats = [
            'status' => 'alive',
            'load' => sys_getloadavg()[0],
            'memory_peak' => (memory_get_peak_usage(true) / 1024 / 1024) . ' MB',
            'storage_writable' => is_writable('/var/www/html/storage'),
            'timestamp' => time()
        ];
        
        echo json_encode($stats);
    }

}
