<?php declare(strict_types=1);

namespace App\Controllers;

use App\dBug;
use Exception;

class CsvController extends BaseController {
    
    public function __construct()
    {
        // Call parent to initialize $this->db and $this->smarty
        parent::__construct();
    }

    public function index(): void
    {
        $conditions = [
            'tbl' => 'jobs',
            'order' => ['created_at' => 'DESC'],
            'LIMIT' => '0,10'
        ];

        $jobs = $this->db->find($conditions);

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
     * The Hand-off: Handles the upload and alerts the Worker Agent
     */
public function upload(): void
    {
        try {
            if (!isset($_FILES['csv_file']) || empty($_FILES['csv_file']['name'])) {
                throw new Exception("No file selected or upload error.");
            }

            $file = $_FILES['csv_file'];
            // Sanitize filename to prevent issues with spaces or special chars
            $safeName = preg_replace("/[^a-zA-Z0-0\._-]/", "_", $file['name']);
            $filename = time() . '_' . $safeName;
            $relativeStoragePath = 'uploads/' . $filename;
            
            // PATHING: From /php/src/Controllers/CsvController.php
            // Move up 2 levels to reach /php/ root
            $phpRoot = dirname(__DIR__, 2); 
            $absolutePath = $phpRoot . "/" . $relativeStoragePath;
            
            // Ensure the directory actually exists and is writable
            if (!is_dir($phpRoot . "/uploads")) {
                mkdir($phpRoot . "/uploads", 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $absolutePath)) {
                
                // 1. Register the Job in MySQL (Persistence)
                // Includes 'file_path' to satisfy NOT NULL constraints
                $jobId = $this->db->save([
                    'tbl'            => 'jobs',
                    'title'          => 'Import: ' . $file['name'],
                    'file_path'      => $relativeStoragePath, 
                    'status'         => 'pending',
                    'total_rows'     => 0,
                    'processed_rows' => 0
                ]);

                // 2. Drop the .job file for the Worker (The "Trigger")
                // Project root is usually 3 levels up from Controllers (Project/storage/queue)
                $projectRoot = dirname(__DIR__, 3); 
                $queuePath = $projectRoot . '/storage/queue/' . $jobId . '.job';

                // Ensure queue directory exists
                if (!is_dir(dirname($queuePath))) {
                    mkdir(dirname($queuePath), 0777, true);
                }

                $jobPayload = [
                    'job_id'   => $jobId,
                    'type'     => 'CSV_IMPORT',
                    'filepath' => $relativeStoragePath,
                    'filename' => $file['name']
                ];

                file_put_contents($queuePath, json_encode($jobPayload));

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'job_id' => $jobId]);
            } else {
                 throw new Exception("Failed to move uploaded file to $absolutePath. Check folder permissions.");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        die();
    }

    public function status(): void
    {
        // Find all jobs that aren't 'completed' or 'failed'
        $activeJobs = $this->db->find([
            'tbl'   => 'jobs',
            'where' => ['status !=' => 'completed', 'status !=' => 'failed']
        ]);

        header('Content-Type: application/json');
        echo json_encode($activeJobs);
        die();
    }
    
    }// end of class