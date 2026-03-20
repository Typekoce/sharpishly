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

    /**
     * Endpoint: /php/home/signal?type=stop
     */
    public function signal(): void
    {
        $type = $_GET['type'] ?? '';
        $allowedSignals = ['stop', 'restart', 'clear_logs'];

        if (!in_array($type, $allowedSignals)) {
            $this->json(['status' => 'error', 'message' => 'Invalid signal'], 400);
            return;
        }

        // Create the physical signal file in the queue directory
        // The Worker loop checks for file_exists($loc->queue('STOP'))
        $signalFile = $this->loc->queue(strtoupper($type));
        
        if (touch($signalFile)) {
            \App\Services\Logger::info("System Signal Issued: " . strtoupper($type));
            $this->json(['status' => 'success', 'signal' => $type]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Failed to issue signal'], 500);
        }
    }
}