<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\HomeModel;
use Exception;

class HomeController extends BaseController
{
    private HomeModel $home;

    public function __construct()
    {
        parent::__construct();
        $this->home = new HomeModel();
    }

    /**
     * Main Dashboard View
     */
    public function index(): void
    {
        $data = [
            'title'       => 'Sharpishly Dashboard',
            'dashboard'   => 'Your Dashboard',
            'recent_jobs' => $this->home->csv()
        ];

        // Standardized layout rendering via BaseController
        $this->render($data, [
            'header' => 'layouts/header',
            'main'   => 'home/main',
            'footer' => 'layouts/footer'
        ]);
    }

    /**
     * RESTORED: API Endpoint for checking active job status
     */
    public function status(): void
    {
        $activeJobs = $this->db->find([
            'tbl'   => 'jobs',
            'where' => ['status !=' => 'completed', 'status !=' => 'failed']
        ]);

        $this->json($activeJobs);
    }

    /**
     * JSON response for data table updates
     */
    public function csv(): void
    {
        $this->json($this->home->csv());
    }

    /**
     * Database Schema Migration Report
     */
    public function migrate(): void
    {
        try {
            echo $this->home->migrate();
        } catch (Exception $e) {
            http_response_code(500);
            echo "<h1>Migration Error</h1><pre>{$e->getMessage()}</pre>";
        }
    }
}