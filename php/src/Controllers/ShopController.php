<?php
namespace App\Controllers;

use App\Models\TasksModel;
use App\Db;

class ShopController extends BaseController {
    public function placeOrder() {
        $data = json_decode(file_get_contents('php://input'), true);
        $tasks = new TasksModel();
        
        // Orchestrate background printing task
        $tasks->save([
            'tbl' => 'tasks',
            'name' => "Mug Print: " . ($data['club'] ?? 'Retail'),
            'type' => 'manual',
            'action_type' => 'mug_decorator',
            'payload' => json_encode($data),
            'status' => 'active'
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Manufacturing task queued.']);
    }
}
