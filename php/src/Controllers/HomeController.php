<?php
// src/Controllers/HomeController.php

declare(strict_types=1);

namespace App\Controllers;

use App\Db;
use App\Smarty;
use Exception;

class HomeController
{
    private Db $db;

    public function __construct()
    {
        $this->db = new Db();
    }

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

    /**
     * Migration route – creates table + seeds example data
     */
    public function migrate(): void
    {
        try {
            $table = 'jobs_test';

            $schema = [
                'tbl'            => $table,
                'id'             => 'INT AUTO_INCREMENT PRIMARY KEY',
                'file_path'      => 'VARCHAR(255) NOT NULL',
                'status'         => "ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending'",
                'total_rows'     => 'INT DEFAULT 0',
                'processed_rows' => 'INT DEFAULT 0',
                'created_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'ENGINE'         => 'InnoDB',
            ];

            $this->db->create($schema);
            echo "<p style=\"color: green;\">Table <strong>$table</strong> created (or already exists).</p>";

            // Seed one record if table is empty
            $existing = $this->db->find([
                'tbl'    => $table,
                'limit'  => 1,
                'fields' => ['id'],
            ]);

            if (empty($existing)) {
                $save = [
                    'tbl'           => $table,
                    'file_path'     => 'php/uploads/test.' . rand(10000, 99999) . '.csv',
                    'status'        => 'pending',
                    'total_rows'    => rand(50000, 500000),
                    'processed_rows'=> 0,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s'),
                ];

                $newId = $this->db->save($save);
                echo "<p style=\"color: green;\">Seeded record ID: $newId</p>";
            } else {
                echo "<p>Table already has data → skipping seed.</p>";
            }

            // Show what we have now
            $results = $this->db->find([
                'tbl'   => $table,
                'order' => ['id' => 'desc'],
                'limit' => 5,
            ]);

            echo "<h3>Last records in $table:</h3><pre>";
            print_r($results);
            echo "</pre>";

        } catch (Exception $e) {
            http_response_code(500);
            echo "<h1>Migration Error</h1>";
            echo "<pre style=\"color: red;\">" . htmlspecialchars($e->getMessage()) . "</pre>";
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