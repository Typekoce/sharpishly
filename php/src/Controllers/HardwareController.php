<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\HardwareModel;

class HardwareController extends BaseController
{
    public function info(): void
    {
        $data = [
            'cpu'     => $this->getCpuInfo(),
            'memory'  => $this->getMemoryInfo(),
            'os'      => php_uname('s') . ' ' . php_uname('r'),
            'usb'     => $this->getUsbDevices(),
            'network' => $this->getNetworkInterfaces(),
            'disks'   => $this->getDisks(),
        ];

        // Save to history using your Db abstraction in the Model
        $model = new HardwareModel();
        $model->saveScan($data);

        $this->json($data);
    }

    private function getCpuInfo(): string 
    {
        // Reading from /proc/cpuinfo (Standard Linux)
        $cpu = shell_exec("grep 'model name' /proc/cpuinfo | head -1 | cut -d ':' -f 2");
        return $cpu ? trim($cpu) : "Unknown CPU";
    }

    private function getMemoryInfo(): array 
    {
        $mem = shell_exec("free -m");
        return ['raw' => $mem ? trim($mem) : "Unavailable"];
    }

    private function getUsbDevices(): array 
    {
        // lsusb might not be in the container, so we check /sys/bus/usb/devices
        $devices = is_dir('/sys/bus/usb/devices') ? scandir('/sys/bus/usb/devices') : [];
        return array_values(array_filter($devices, fn($d) => !str_starts_with($d, '.')));
    }

    private function getNetworkInterfaces(): array 
    {
        $interfaces = shell_exec("ip -br link show");
        return $interfaces ? explode("\n", trim($interfaces)) : [];
    }

    private function getDisks(): array 
    {
        $df = shell_exec("df -h /");
        return $df ? explode("\n", trim($df)) : [];
    }
}