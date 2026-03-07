<?php
namespace App\Models;

use App\Db;

class TaskModel {
    private $db;

    public function __construct() {
        $this->db = new Db();
    }

    public function getPendingTasks(): array {
        return $this->db->find([
            'tbl'   => 'tasks',
            'where' => ['status' => 'active']
        ]);
    }

    public function updateStatus(int $id, string $status, ?string $lastRun = null): void {
        $data = ['status' => $status];
        if ($lastRun) $data['last_run'] = $lastRun;
        
        $this->db->update('tasks', $data, ['id' => $id]);
    }

    public function failTask(int $id, string $error): void {
        // Log the error into the payload or a separate log table
        $this->db->update('tasks', ['status' => 'failed'], ['id' => $id]);
    }
}