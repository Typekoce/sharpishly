<?php
// Location: /var/www/html/php/src/Controllers/CsvController.php

namespace App\Controllers;

use App\Services\Location;
use App\Services\Logger;
use App\dBug;
use Exception;

class CsvController extends BaseController {
    
    private Location $loc;

    public function __construct() {
        parent::__construct();
        $this->loc = new Location();
    }

    /**
     * Dashboard view for CSV operations
     */
    public function index(): void
    {
        $conditions = [
            'tbl' => 'jobs',
            'order' => ['created_at' => 'DESC'],
            'LIMIT' => '0,10'
        ];

        $jobs = $this->db->find($conditions);

        // Debug output for development
        new dBug($jobs);

        $data = [
            'title'     => 'CSV Interrogation Hub',
            'dashboard' => 'Data Engine',
            'jobs'      => $jobs,
        ];

        $views = [
           'header' => 'layouts/header',
           'main'   => 'csv/upload', 
           'footer' => 'layouts/footer'
        ];

        $this->render($data, $views);
    }

    /**
     * Handles the physical file upload and worker handoff
     */
    public function upload(): void {
        try {
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Upload failed or file missing.");
            }

            $fileName = basename($_FILES['csv_file']['name']);
            // Use the Location service for consistent absolute paths
            $absTarget = $this->loc->uploads($fileName);

            if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $absTarget)) {
                
                // 1. Database Entry: Track the job
                $jobId = $this->db->save([
                    'tbl'       => 'jobs',
                    'title'     => 'Import: ' . $fileName,
                    'file_path' => $this->loc->relative($absTarget), 
                    'status'    => 'pending'
                ]);

                // 2. Queue Trigger: Drop the .job file for the worker-daemon
                $payload = [
                    'job_id'   => $jobId, 
                    'filepath' => $this->loc->relative($absTarget)
                ];
                
                file_put_contents($this->loc->queue($jobId . '.job'), json_encode($payload));

                // 3. Technical Debt: Register the Full App Code Review task
                $this->db->save([
                    'tbl'    => 'tasks',
                    'title'  => 'Full App Code Review',
                    'status' => 'active',
                    'type'   => 'cron'
                ]);

                Logger::info("File queued for processing", ['job_id' => $jobId, 'file' => $fileName]);
                echo "✅ File queued successfully. Job ID: " . $jobId;
            } else {
                throw new Exception("Failed to move uploaded file to storage.");
            }
        } catch (Exception $e) {
            Logger::error($e->getMessage());
            http_response_code(500);
            echo "❌ " . $e->getMessage();
        }
        die();
    }

    /**
     * API Endpoint for checking active job status
     */
    public function status(): void
    {
        // Find all jobs that are still being processed
        $activeJobs = $this->db->find([
            'tbl'   => 'jobs',
            'where' => ['status !=' => 'completed', 'status !=' => 'failed']
        ]);

        header('Content-Type: application/json');
        echo json_encode($activeJobs);
        die();
    }
}