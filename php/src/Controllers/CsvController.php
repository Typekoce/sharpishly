<?php
declare(strict_types=1);
//Location: php/src/Controllers/CsvController.php
namespace App\Controllers;

use App\Services\Logger;
use App\dBug;
use Exception;

class CsvController extends BaseController {
    
    public function __construct() {
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
            'limit' => 10 // Fixed: '0,10' string can sometimes confuse simple limit parsers
        ];

        $jobs = $this->db->find($conditions);

        // Debug output only if requested or in dev mode
        if (class_exists('App\dBug') && isset($_GET['debug'])) {
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
            $absTarget = $this->loc->uploads($fileName);

            if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $absTarget)) {
                
                // 1. Database Entry: Track the job
                $jobId = $this->db->save([
                    'tbl'       => 'jobs',
                    'title'     => 'Import: ' . $fileName,
                    'file_path' => $this->loc->relative($absTarget), 
                    'status'    => 'pending'
                ]);

                // 2. Queue Trigger
                $payload = [
                    'job_id'   => $jobId, 
                    'filepath' => $this->loc->relative($absTarget)
                ];
                
                // Ensure queue directory exists via Location service
                file_put_contents($this->loc->queue($jobId . '.job'), json_encode($payload));

                // 3. Register Task (Aligned with tasks table schema)
                $this->db->save([
                    'tbl'         => 'tasks',
                    'name'        => 'Full App Code Review',
                    'type'        => 'cron',
                    'action_type' => 'review', // Added to satisfy potential NOT NULL constraints
                    'payload'     => json_encode(['action' => 'review', 'job_id' => $jobId]),
                    'status'      => 'active'
                ]);

                Logger::info("File queued for processing", ['job_id' => $jobId, 'file' => $fileName]);
                
                // Switch to json response for HUD compatibility
                $this->json(['status' => 'success', 'message' => 'File queued successfully', 'job_id' => $jobId]);

            } else {
                throw new Exception("Failed to move uploaded file to storage.");
            }
        } catch (Exception $e) {
            Logger::error($e->getMessage());
            $this->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API Endpoint for checking active job status
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
     * API Endpoint for records
     */    public function records(): void 
    {
        $records = $this->db->find([
            'tbl' => 'csv_records',
            'limit' => 50,
            'order' => ['id' => 'DESC']
        ]);

        $this->json(['status' => 'success', 'data' => $records]);
    }
}