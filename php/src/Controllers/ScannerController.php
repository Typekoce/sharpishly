<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\BaseModel;

class ScannerController {
    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function index() {
        // This is the API endpoint the JS will fetch
        header('Content-Type: application/json');

        // Execute lsusb to get raw hardware data
        // We use '2>&1' to catch errors in the output string
        $rawOutput = shell_exec('lsusb 2>&1');
        
        if (!$rawOutput) {
            echo json_encode(['status' => 'error', 'message' => 'No USB devices found or permission denied.']);
            return;
        }

        // Clean up the output into an array of devices
        $devices = array_filter(explode("\n", trim($rawOutput)));

        echo json_encode([
            'status' => 'online',
            'timestamp' => date('Y-m-d H:i:s'),
            'device_count' => count($devices),
            'devices' => $devices
        ]);
    }
}