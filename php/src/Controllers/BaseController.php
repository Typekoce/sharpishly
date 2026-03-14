<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Registry;
use App\Db;

abstract class BaseController {
    protected Db $db;
    public function __construct() {
        $this->db = Registry::get(Db::class);
    }
    protected function json(mixed $data, int $code = 200): void {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data);
        exit;
    }
}
