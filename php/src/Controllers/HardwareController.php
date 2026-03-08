<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\HardwareModel;

class HardwareController
{
    public function info(): void
    {
        header('Content-Type: application/json');

        $data = [
            'cpu'     => $this->getCpuInfo(),
            'memory'  => $this->getMemoryInfo(),
            'os'      => php_uname('s') . ' ' . php_uname('r'),
            'usb'     => $this->getUsbDevices(),
            'network' => $this->getNetworkInterfaces(),
            'disks'   => $this->getDisks(),
        ];

        // Save to history
        $model = new HardwareModel();
        $model->saveScan($data);

        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    private function getCpuInfo(): string { /* same as before */ }
    private function getMemoryInfo(): array { /* same as before */ }
    private function getUsbDevices(): array { /* same as before */ }
    private function getNetworkInterfaces(): array { /* same as before */ }
    private function getDisks(): array { /* same as before */ }
}
