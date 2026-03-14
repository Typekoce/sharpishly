<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Registry;
use App\Db;
use App\Services\Location;
use App\Smarty; // Ensure this class exists in your App namespace

abstract class BaseController {
    protected Db $db;
    protected Location $loc;
    protected Smarty $smarty;

    public function __construct() {
        // Use the shared instances from Registry
        $this->db     = Registry::get(Db::class);
        $this->loc    = Registry::get(Location::class);
        
        // Initialize Smarty
        $this->smarty = new Smarty();
    }

    protected function json(mixed $data, int $code = 200): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode($data);
        exit;
    }

    /**
     * Orchestrates Header, Main, and Footer views using Smarty.
     */
    protected function render(array $data, array $views): void {
        $output = '';
        foreach ($views as $name => $path) {
            $output .= $this->renderView($path, $data);
        }
        echo $output;
    }

    /**
     * Loads a view file and processes it via the Smarty engine.
     */
    protected function renderView(string $path, array $data): string {
        $file = dirname(__DIR__, 1) . "/views/$path.html";
        
        if (!file_exists($file)) {
            return "";
        }

        $content = file_get_contents($file);
        
        // Use the Smarty instance to render the content with the provided data
        return $this->smarty->render($content, $data);
    }
}