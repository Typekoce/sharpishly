<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\HomeModel;
use App\Registry;
use Exception;

class HomeController extends BaseController
{
    private HomeModel $home;

    public function __construct()
    {
        parent::__construct();
        // Models also use Registry internally for the DB
        $this->home = new HomeModel();
    }

    /**
     * Dashboard view logic
     */
    public function index(): void
    {
        $data = [
            'title'     => 'Sharpishly Dashboard',
            'dashboard' => 'Your Dashboard',
            'recent_jobs' => $this->home->csv()
        ];

        // Using a standardized view helper (Logic would move to BaseController eventually)
        echo $this->renderView('home/main', $data);
    }

    /**
     * JSON response for AJAX dashboard updates
     */
    public function csv(): void
    {
        $this->json($this->home->csv());
    }

    /**
     * Browser-triggered Migration
     */
    public function migrate(): void
    {
        try {
            // Returns the HTML report from the model
            echo $this->home->migrate();
        } catch (Exception $e) {
            http_response_code(500);
            echo "<h1>Migration Error</h1><pre>{$e->getMessage()}</pre>";
        }
    }

    /**
     * Simple View Loader
     */
    private function renderView(string $path, array $data): string
    {
        // Path alignment: php/src/Controllers/ -> php/views/
        $file = dirname(__DIR__) . "/views/$path.html";
        
        if (!file_exists($file)) {
            return "";
        }

        $content = file_get_contents($file);
        
        // Simple template replacement (Placeholder for Smarty logic)
        foreach ($data as $key => $val) {
            if (is_string($val)) {
                $content = str_replace('{$' . $key . '}', $val, $content);
            }
        }

        return $content;
    }
}