<?php
namespace App\Controllers;

use App\Models\TasksModel;
use App\dBug;

class TaskController extends BaseController {

    public function isAjax(){
        // Temp fix
        return true;
    }
    
    // This handles: /php/tasks/index
    public function response() {
        $model = new TasksModel();
        $tasks = $model->getPendingTasks();    
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $tasks]);
        exit;
    }

    // This handles: /php/tasks/index
    public function index() {
        $model = new TasksModel();
        $tasks = $model->getAllTasks();

        $data = [
            'title'     => 'All Tasks',
            'dashboard' => 'Data Engine',
            'jobs'      => $tasks,
        ];
        new dBug($data);
        $views = [
           'header' => 'layouts/header',
           'main'   => 'csv/upload', 
           'footer' => 'layouts/footer'
        ];

        $this->render($data, $views);

    }

    // This handles: /php/tasks/trigger
    public function trigger() {
        header('Content-Type: application/json');
        $taskId = $_GET['task_id'] ?? null;

        if ($taskId) {
            $model = new TasksModel();
            $model->updateStatus((int)$taskId, 'active', date('Y-m-d H:i:s'));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No ID']);
        }
        exit;
    }
}