<?php
namespace App\Actions;

use App\Db;
use App\Services\Logger;

class MugDecoratorAction {
    public function execute($payload) {
        $data = json_decode($payload, true);
        $db = new Db();
        
        $quantity = $data['quantity'] ?? 1;
        $club = $data['club'] ?? 'Generic';

        // 1. Check stock in hardware_scans (or merchandise_inventory if added)
        // 2. Simulate Print
        Logger::info("Printer Active: Decorating $quantity mugs for $club.");
        sleep(2); 

        return ['status' => 'completed', 'shipped' => true];
    }
}
