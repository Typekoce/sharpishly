<?php
declare(strict_types=1);

namespace App\Models;

use App\Db;

class HardwareModel
{
    private Db $db;

    public function __construct()
    {
        $this->db = new Db();
    }

    public function saveScan(array $data): int
    {
        return $this->db->save([
            'tbl'          => 'hardware_scans',
            'scan_type'    => $data['scan_type'] ?? 'full',
            'usb_count'    => $data['usb_count'] ?? 0,
            'cpu_info'     => $data['cpu'] ?? 'unknown',
            'memory_info'  => json_encode($data['memory'] ?? []),
            'network_info' => json_encode($data['network'] ?? []),
            'raw_data'     => json_encode($data),
        ]);
    }

    public function getRecentScans(int $limit = 10): array
    {
        return $this->db->find([
            'tbl'   => 'hardware_scans',
            'order' => ['id' => 'desc'],
            'limit' => $limit,
        ]);
    }
}
