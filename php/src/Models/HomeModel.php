<?php declare(strict_types=1);

namespace App\Models;

use App\Db;
use Exception;

class HomeModel
{
    private Db $db;

    public function __construct()
    {
        $this->db = new Db();
    }

    public function csv(){

        $conditions = [
            'tbl'    => 'jobs',
            'fields' => ['id', 'status', 'processed_rows', 'total_rows', 'updated_at'],
            'order'  => ['id' => 'DESC'],
            'limit'  => 5
        ];

        $result = $this->db->find($conditions);

        // echo "<pre>" . print_r($result) . "</pre>";

        return $result;
    }
/**
     * Run migrations: create necessary tables if they don't exist + optional seeding
     * * @return string HTML-formatted report
     */
    public function migrate(): string
    {
        $report = "<h2>Migration Report</h2><pre>\n";

        try {
            // --- Table: social ---
            $this->createTable('social', [
                'id'             => 'INT AUTO_INCREMENT PRIMARY KEY',
                'status'         => "ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending'",
                'processed_rows' => 'INT DEFAULT 0',
                'created_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ]);
            $report .= "[OK] Table 'social' processed\n";

            // --- Table: users ---
            $this->createTable('users', [
                'id'             => 'INT AUTO_INCREMENT PRIMARY KEY',
                'status'         => "ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending'",
                'processed_rows' => 'INT DEFAULT 0',
                'created_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ]);
            $report .= "[OK] Table 'users' processed\n";

            // --- Table: jobs ---
            $this->createTable('jobs', [
                'id'             => 'INT AUTO_INCREMENT PRIMARY KEY',
                'file_path'      => 'VARCHAR(255) NOT NULL',
                'status'         => "ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending'",
                'total_rows'     => 'INT DEFAULT 0',
                'processed_rows' => 'INT DEFAULT 0',
                'created_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ]);
            $report .= "[OK] Table 'jobs' processed\n";

            // PATCH: Add 'title' column if missing
            try {
                $this->db->alter('jobs', 'ADD COLUMN', 'title', 'VARCHAR(255) AFTER id');
                $report .= "[PATCH] Added 'title' column to 'jobs'\n";
            } catch (Exception $e) {
                $report .= "[SKIP] 'title' column already exists\n";
            }

            // --- Table: tasks ---
            $this->createTable('tasks', [
                'id'          => 'BIGINT AUTO_INCREMENT PRIMARY KEY',
                'name'        => 'VARCHAR(255) NOT NULL',
                'type'        => "ENUM('cron', 'webhook', 'manual', 'file_drop') NOT NULL",
                'schedule'    => 'VARCHAR(100) NULL',
                'payload'     => 'JSON NOT NULL',
                'action_type' => 'VARCHAR(50) NOT NULL',
                'status'      => "ENUM('active', 'paused', 'failed') DEFAULT 'active'",
                'last_run'    => 'TIMESTAMP NULL',
                'next_run'    => 'TIMESTAMP NULL',
                'created_at'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
            ]);
            $report .= "[OK] Table 'tasks' processed\n";

            // --- Table: csv_records ---
            $this->createTable('csv_records', [
                'id'         => 'INT AUTO_INCREMENT PRIMARY KEY',
                'job_id'     => 'INT',
                'column_1'   => 'VARCHAR(255)',
                'column_2'   => 'VARCHAR(255)',
                'column_3'   => 'TEXT',
                'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            ]);
            $report .= "[OK] Table 'csv_records' processed\n";

            // PATCH: Add Index for performance
            try {
                $this->db->alter('csv_records', 'ADD INDEX', 'idx_job_id', '(job_id)');
                $report .= "[PATCH] Index 'idx_job_id' added to 'csv_records'\n";
            } catch (Exception $e) {
                $report .= "[SKIP] Index 'idx_job_id' already exists\n";
            }

            // PATCH: Add Foreign Key for data integrity
            try {
                $this->db->alter('csv_records', 'ADD FOREIGN KEY', 'fk_job_id', '(job_id) REFERENCES jobs(id) ON DELETE CASCADE ON UPDATE CASCADE');
                $report .= "[PATCH] Foreign key 'fk_job_id' added to 'csv_records'\n";
            } catch (Exception $e) {
                $report .= "[SKIP] Foreign key 'fk_job_id' already exists\n";
            }

            // --- Seeding ---
            $count = $this->db->find(['tbl' => 'jobs', 'limit' => 1]);
            if (empty($count)) {
                $this->db->save([
                    'tbl'           => 'jobs',
                    'title'         => 'Initial Seed Job',
                    'file_path'     => 'php/uploads/initial-' . date('YmdHis') . '.csv',
                    'status'        => 'pending',
                    'total_rows'    => 50000,
                    'processed_rows'=> 0,
                ]);
                $report .= "[SEED] Added 1 initial job record\n";
            } else {
                $report .= "[SKIP] jobs table already has data\n";
            }

            $report .= "\nMigration completed successfully.\n";
        } catch (Exception $e) {
            $report .= "[ERROR] " . htmlspecialchars($e->getMessage()) . "\n";
        }

        $report .= "</pre>";
        return $report;
    }
    
    private function createTable(string $table, array $columns, array $options = []): void
    {
        if (empty($columns)) {
            throw new Exception("No columns defined for table '$table'");
        }

        $colDefs = [];
        foreach ($columns as $name => $def) {
            $colDefs[] = $this->db->escapeIdentifier($name) . ' ' . $def;
        }

        $engine  = $options['engine']  ?? 'InnoDB';
        $charset = $options['charset'] ?? 'utf8mb4';
        $collate = $options['collate'] ?? 'utf8mb4_unicode_ci';

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->db->escapeIdentifier($table) . " (
            " . implode(",\n            ", $colDefs) . "
        ) ENGINE=$engine DEFAULT CHARSET=$charset COLLATE=$collate;";

        // FIXED: use public property directly instead of non-existent getPdo()
        $this->db->pdo->exec($sql);
    }

    // Placeholder – to be implemented later
    public function trackEmailOpen(string $emailId, string $recipient): bool
    {
        // Future implementation:
        // 1. Log open event to database (table email_opens)
        // 2. Update opened_at timestamp
        // 3. Possibly increment open count

        return true; // stub
    }
}