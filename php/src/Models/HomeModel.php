<?php
// src/Models/HomeModel.php

declare(strict_types=1);

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

    /**
     * Run migrations: create necessary tables if they don't exist + optional seeding
     *
     * @return string HTML-formatted report
     */
    public function migrate(): string
    {
        $report = "<h2>Migration Report</h2><pre>\n";

        try {


            // Add inside the try block of your migrate() method
            $this->createTable('video_uploads', [
                'id'                => 'BIGINT AUTO_INCREMENT PRIMARY KEY',
                'original_path'     => 'VARCHAR(512) NOT NULL',
                'original_filename' => 'VARCHAR(255) NOT NULL',
                'status'            => "ENUM('pending','processing','completed','failed') DEFAULT 'pending'",
                'created_at'        => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            ]);

            $this->createTable('social_posts', [
                'id'                => 'BIGINT AUTO_INCREMENT PRIMARY KEY',
                'video_upload_id'   => 'BIGINT NOT NULL',
                'platform'          => "ENUM('youtube','tiktok','instagram','facebook') NOT NULL",
                'custom_title'      => 'VARCHAR(255)',
                'custom_description'=> 'TEXT',
                'custom_hashtags'   => 'TEXT',
                'optimized_path'    => 'VARCHAR(512)',
                'post_url'          => 'VARCHAR(255)',
                'status'            => "ENUM('pending','posted','failed') DEFAULT 'pending'",
                'posted_at'         => 'TIMESTAMP NULL',
                'FOREIGN KEY (video_upload_id) REFERENCES video_uploads(id) ON DELETE CASCADE',
            ]);
                    
            // Table: jobs (example – add more tables as needed)
            $this->createTable('jobs', [
                'id'             => 'INT AUTO_INCREMENT PRIMARY KEY',
                'file_path'      => 'VARCHAR(255) NOT NULL',
                'status'         => "ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending'",
                'total_rows'     => 'INT DEFAULT 0',
                'processed_rows' => 'INT DEFAULT 0',
                'created_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ], [
                'engine'  => 'InnoDB',
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ]);
            $report .= "[OK] Table 'jobs' created or already exists\n";

            // Table: csv_records (example)
            $this->createTable('csv_records', [
                'id'         => 'INT AUTO_INCREMENT PRIMARY KEY',
                'job_id'     => 'INT',
                'column_1'   => 'VARCHAR(255)',
                'column_2'   => 'VARCHAR(255)',
                'column_3'   => 'TEXT',
                'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            ], [
                'engine'  => 'InnoDB',
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ]);

            // Add indexes & foreign key (run after table creation)
            $this->db->getPdo()->exec("
                ALTER TABLE csv_records
                ADD INDEX idx_job_id (job_id),
                ADD FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
                ON UPDATE CASCADE;
            ");
            $report .= "[OK] Indexes & foreign key added to csv_records\n";

            // Simple seeding example – only if table is empty
            $count = $this->db->find(['tbl' => 'jobs', 'limit' => 1]);
            if (empty($count)) {
                $this->db->save([
                    'tbl'           => 'jobs',
                    'file_path'     => 'php/uploads/initial-' . date('YmdHis') . '.csv',
                    'status'        => 'pending',
                    'total_rows'    => 50000,
                    'processed_rows'=> 0,
                ]);
                $report .= "[SEED] Added 1 initial job record\n";
            } else {
                $report .= "[SKIP] jobs table already has data → no seeding\n";
            }

            $report .= "\nMigration completed successfully.\n";
        } catch (Exception $e) {
            $report .= "[ERROR] " . htmlspecialchars($e->getMessage()) . "\n";
        }

        $report .= "</pre>";
        return $report;
    }

    /**
     * Helper: create table from array schema (moved from Db)
     */
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

        $this->db->getPdo()->exec($sql);
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