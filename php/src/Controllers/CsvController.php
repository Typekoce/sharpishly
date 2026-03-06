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
            if (!isset($_FILES['csv_file'])) {
                throw new Exception("No file uploaded.");
            }

            $file = $_FILES['csv_file'];
            $filename = time() . '_' . $file['name'];
            $relativeStoragePath = 'uploads/' . $filename;
            
            // MOVE UP 2 LEVELS: from php/src/Controllers to php/
            $phpRoot = dirname(__DIR__, 2); 
            $absolutePath = $phpRoot . "/" . $relativeStoragePath;
            
            // Ensure the directory actually exists before moving
            if (!is_dir($phpRoot . "/uploads")) {
                mkdir($phpRoot . "/uploads", 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $absolutePath)) {
                
                // 1. Register the Job in MySQL
                $jobId = $this->db->save([
                    'tbl' => 'jobs',
                    'title' => 'Import: ' . $file['name'],
                    'status' => 'pending',
                    'processed_rows' => 0
                ]);

                // 2. Drop the .job file for the Worker
                // Again, move up to the project root for /storage
                // Project root is 3 levels up from Controllers
                $projectRoot = dirname(__DIR__, 3); 
                $queuePath = $projectRoot . '/storage/queue/' . $jobId . '.job';

                file_put_contents($queuePath, json_encode([
                    'job_id'   => $jobId,
                    'type'     => 'CSV_IMPORT',
                    'filepath' => $relativeStoragePath,
                    'filename' => $file['name']
                ]));

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'job_id' => $jobId]);
            } else {
                 throw new Exception("Failed to move uploaded file to $absolutePath");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        die();
    }
}