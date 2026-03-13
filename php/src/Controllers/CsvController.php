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

        // new dBug($jobs);

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

    public function upload(): void
    {
        echo "<pre>";
        print_r(['FILES'=>$_FILES,'POST'=>$_POST]);
        echo "</pre>";

        // Define where you want it to go
        $destination = "/var/www/html/storage/uploads/" . $_FILES['csv_file']['name'];

        // Move it from the temp folder to your storage folder
        if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $destination)) {
            echo "✅ Success: File moved to " . $destination;
        } else {
            echo "❌ Error: Could not move file. Check permissions.";
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