<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Registry;
use App\Db;
use App\Services\Location;

abstract class BaseController {
    protected Db $db;
    protected Location $loc;

    public function __construct() {
        // Use the shared instances from Registry
        $this->db  = Registry::get(Db::class);
        $this->loc = Registry::get(Location::class);
    }

    protected function json(mixed $data, int $code = 200): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode($data);
        exit;
    }
}