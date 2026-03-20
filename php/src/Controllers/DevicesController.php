<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Registry;
use Exception;

class DevicesController extends BaseController
{
    public function index(): void
    {
        try {
            $data = [
                'module'    => 'Devices',
                'status'    => 'operational',
                'timestamp' => time(),
                'devices'   => [
                    'usb'     => $this->getUsbDevices(),
                    'network' => $this->getNetworkDevices(),
                ]
            ];

            $this->json($data);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Failed to load hardware data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Probes for USB devices via system shell
     */
    private function getUsbDevices(): array
    {
        // 'lsusb' provides a clean list of connected hardware
        $output = [];
        exec('lsusb', $output);
        
        return array_map('trim', $output);
    }    

    /**
     * Probes local network for active IPs (Arp scan)
     */
    private function getNetworkDevices(): array
    {
        $output = [];
        // 'arp -a' shows the address resolution table
        exec('arp -a', $output);
        
        return array_map('trim', $output);
    }

    public function action(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $this->json(['status' => 'error', 'message' => 'Invalid payload.'], 400);
            return;
        }

        $this->json([
            'status'  => 'success',
            'message' => 'Device command received.'
        ]);
    }
}