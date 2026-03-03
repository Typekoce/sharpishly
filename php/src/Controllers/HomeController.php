<?php
// src/Controllers/HomeController.php

declare(strict_types=1);

namespace App\Controllers;

use App\Db;
use App\Smarty;
use Exception;

class HomeController
{
    public function __construct()
    {

    }

    //@TODO: Create Email MVC
    public function index(): void
    {
        // Show dashboard with latest jobs
        $jobs = $this->db->find([
            'tbl'   => 'jobs',
            'order' => ['id' => 'desc'],
            'limit' => 10,
        ]);

        $smarty = new Smarty();

        $data = [
            'title'     => 'Sharpishly Dashboard',
            'dashboard' => 'Your Dashboard',
            'jobs'      => $jobs,
        ];

        // Simple header + main + footer composition
        $header = $this->getViewContent('layouts/header');
        $footer = $this->getViewContent('layouts/footer');
        $main   = $this->getViewContent('home/main');

        $renderedMain = $smarty->render($main, $data);

        echo $header . $renderedMain . $footer;
    }

    public function about(string $name = 'Guest'): void
    {
        echo "<h1>About page</h1>";
        echo "<p>Hello, " . htmlspecialchars($name) . "!</p>";
    }

// src/Controllers/HomeController.php (relevant part only)

public function migrate(): void
{
    try {
        $model = new \App\Models\HomeModel();
        echo $model->migrate();
    } catch (Exception $e) {
        http_response_code(500);
        echo "<h1>Migration Error</h1>";
        echo "<pre style=\"color:red;\">" . htmlspecialchars($e->getMessage()) . "</pre>";
    }
}

    /**
     * Helper: load view file content
     */
    private function getViewContent(string $path): string
    {
        $file = dirname(__DIR__, 1) . "/views/$path.html";  // adjust path if needed

        if (file_exists($file)) {
            return file_get_contents($file);
        }

        return "<!-- View not found: $path -->";
    }
}