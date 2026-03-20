<?php
declare(strict_types=1);

namespace App\Agents;

use App\Controllers\HardwareController;

class HardwareScanAgent
{
    public function run(): void
    {
        $controller = new HardwareController();
        $controller->info(); // reuses the same logic and saves to DB
    }
}
