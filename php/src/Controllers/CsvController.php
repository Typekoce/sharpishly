<?php
// Location: /var/www/html/php/src/Controllers/CsvController.php

namespace App\Controllers;

use App\Services\Logger;
use App\dBug;
use Exception;

class CsvController extends BaseController {
    
    /**
     * NOTE: We removed 'private Location $loc' because it is already 
     * defined as 'protected' in BaseController. 
     * This fixes the Fatal Error on line 11.
     */

    public function __construct() {
        // parent::__construct() initializes $this->db and $this->loc automatically
        parent::__construct();
    }

    /**
     * Dashboard view for CSV operations
     */
    public function index(): void
    {
        $conditions = [
            'tbl' => 'jobs',
            'order' => ['created_at' => 'DESC'],
            'limit' => '0,10' // Note: SQL is case-insensitive, but consistency helps
        ];

        $jobs = $this->db->find($conditions);

        // Debug output for development
        if (class_exists('App\dBug')) {
            new dBug($jobs);
        }

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

        // Ensure BaseController has a render method; if not, you can call renderView here.
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
            
            // $this->loc is inherited from BaseController
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
                    'name'   => 'Full App Code Review', // Changed 'title' to 'name' to match tasks table schema
                    'status' => 'active',
                    'type'   => 'cron',
                    'payload' => json_encode(['action' => 'review']) // Tasks table requires payload NOT NULL
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