<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\HardwareModel;

/**
 * @file HardwareController.php
 * @package App\Controllers
 * @brief The Gatekeeper for Hardware Abstraction.
 *
 * This controller serves as a bridge between the PHP Orchestrator and the 
 * Python-based PyMVC node. It abstracts the complexity of OS-level device 
 * scanning into a unified JSON API.
 *
 * @section hw_flow Hardware Scanning Process
 * 1. Controller receives a scan request.
 * 2. It dispatches an internal HTTP request to the PyMVC service (Port 8083).
 * 3. The returned Python hardware map is cached in the Registry.
 * 4. Data is pushed to the Nervous System if state changes are detected.
 *
 * @note Requires the Nginx reverse proxy for /pymvc/ to be active.
 * @see NervousSystemController
 */
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