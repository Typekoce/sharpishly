<?php
namespace App\Controllers;

use App\Models\TasksModel;

class TaskController extends BaseController {
    
    // This handles: /php/tasks/index
    public function index() {
        $model = new TasksModel();
        $tasks = $model->getPendingTasks();
        
        // If it's an AJAX request, return JSON
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $tasks]);
            exit;
        }

        // Otherwise, render the Smarty view
        return $this->render('dashboard/tasks.html', ['tasks' => $tasks]);
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