<?php
declare(strict_types=1);

namespace App\Models;

use App\Registry;
use App\Db;
use Exception;

class HomeModel
{
    private Db $db;

    public function __construct()
    {
        // Pulling the shared DB instance from the Registry
        $this->db = Registry::get(Db::class);
    }

    /**
     * Fetch recent jobs for the dashboard
     */
    public function csv(): array
    {
        return $this->db->find([
            'tbl'    => 'jobs',
            'fields' => ['id', 'title', 'status', 'processed_rows', 'total_rows', 'updated_at'],
            'order'  => ['id' => 'DESC'],
            'limit'  => 5
        ]);
    }

    /**
     * Orchestrates the full system migration
     */
    public function migrate(): string
    {
        $report = "<h2>Sharpishly Migration Report</h2><pre>\n";

        try {


            /**
             * Using the Db abstraction to ensure the table structure exists.
             * We pass the schema array to the Db service.
             */
            $this->db->createTable('properties', [
                'id'          => 'INT AUTO_INCREMENT PRIMARY KEY',
                'address'     => 'VARCHAR(255) NOT NULL',
                'rent'        => 'DECIMAL(10, 2) NOT NULL',
                'status'      => "ENUM('Paid', 'Overdue', 'Pending') DEFAULT 'Pending'",
                'tenant_name' => 'VARCHAR(100)',
                'updated_at'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
            ]);
            $report .= "[OK] Table 'properties' ready\n";


            // Seed check using abstraction
            $existing = $this->db->find(['tbl' => 'properties', 'limit' => 1]);

            if (empty($existing)) {
                $this->db->save([
                    'tbl'     => 'properties',
                    'address' => '221B Baker St',
                    'rent'    => 1200.00,
                    'status'  => 'Paid'
                ]);

                $report .= "[OK] Table 'properties seeded' ready\n";

            }


            // 1. Merchandise Inventory
            $this->db->createTable('merchandise_inventory', [
                'id'           => 'INT AUTO_INCREMENT PRIMARY KEY',
                'item_name'    => 'VARCHAR(255) NOT NULL',
                'stock_count'  => 'INT DEFAULT 0',
                'unit_price'   => 'DECIMAL(10,2)',
                'updated_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ]);
            $report .= "[OK] Table 'merchandise_inventory' ready\n";

            // 2. Orders System
            $this->db->createTable('orders', [
                'id'           => 'INT AUTO_INCREMENT PRIMARY KEY',
                'order_type'   => "ENUM('B2C', 'B2B') DEFAULT 'B2C'",
                'club_logo'    => 'VARCHAR(100)',
                'quantity'     => 'INT DEFAULT 1',
                'total_price'  => 'DECIMAL(10,2)',
                'status'       => "ENUM('pending', 'paid', 'shipped') DEFAULT 'pending'",
                'created_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            ]);
            $report .= "[OK] Table 'orders' ready\n";

            // 3. Hardware Scans
            $this->db->createTable('hardware_scans', [
                'id'           => 'BIGINT AUTO_INCREMENT PRIMARY KEY',
                'scan_type'    => 'VARCHAR(50) DEFAULT "full"',
                'usb_count'    => 'INT DEFAULT 0',
                'cpu_info'     => 'VARCHAR(255)',
                'memory_info'  => 'JSON',
                'network_info' => 'JSON',
                'raw_data'     => 'JSON',
                'created_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            ]);
            $report .= "[OK] Table 'hardware_scans' ready\n";

            // 4. CRM Infrastructure (Tenants)
            $this->db->createTable('tenants', [
                'id'         => 'INT AUTO_INCREMENT PRIMARY KEY',
                'name'       => 'VARCHAR(255) NOT NULL',
                'status'     => "ENUM('active', 'inactive', 'suspended') DEFAULT 'active'",
                'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ]);
            $report .= "[OK] Table 'tenants' ready\n";

            // 5. Workflow Infrastructure (Social, Users, Jobs)
            $standardFields = [
                'id'             => 'INT AUTO_INCREMENT PRIMARY KEY',
                'status'         => "ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending'",
                'processed_rows' => 'INT DEFAULT 0',
                'total_rows'     => 'INT DEFAULT 0',
                'created_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ];

            $this->db->createTable('social', $standardFields);
            $this->db->createTable('users', $standardFields);
            
            $jobFields = array_merge([
                'title'     => 'VARCHAR(255)', 
                'file_path' => 'VARCHAR(255) NOT NULL'
            ], $standardFields);
            
            $this->db->createTable('jobs', $jobFields);
            $report .= "[OK] Workflow tables ready\n";

            // PATCH: Add 'note' to jobs (Defensive Check)
            if (!$this->db->columnExists('jobs', 'note')) {
                $this->db->alter('jobs', 'ADD COLUMN', 'note', 'TEXT NULL AFTER status');
                $report .= "[PATCH] Added 'note' column to 'jobs'\n";
            } else {
                $report .= "[SKIP] Column 'note' already exists in 'jobs'\n";
            }

            // 6. Tasks & CSV Records
            $this->db->createTable('tasks', [
                'id'          => 'BIGINT AUTO_INCREMENT PRIMARY KEY',
                'name'        => 'VARCHAR(255) NOT NULL',
                'type'        => "ENUM('cron', 'webhook', 'manual', 'file_drop') NOT NULL",
                'payload'     => 'JSON NOT NULL',
                'status'      => "ENUM('active', 'paused', 'failed') DEFAULT 'active'",
                'created_at'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
            ]);

            $this->db->createTable('csv_records', [
                'id'         => 'INT AUTO_INCREMENT PRIMARY KEY',
                'job_id'     => 'INT',
                'column_1'   => 'VARCHAR(255)',
                'column_2'   => 'VARCHAR(255)',
                'column_3'   => 'TEXT',
                'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            ]);
            $report .= "[OK] CSV Engine tables ready\n";

            // 7. Landlord Infrastructure (NEW)
            $this->db->createTable('properties', [
                'id'           => 'INT AUTO_INCREMENT PRIMARY KEY',
                'name'         => 'VARCHAR(255) NOT NULL',
                'address'      => 'TEXT NOT NULL',
                'unit_number'  => 'VARCHAR(50)',
                'monthly_rent' => 'DECIMAL(10, 2) NOT NULL',
                'status'       => "ENUM('vacant', 'occupied', 'maintenance') DEFAULT 'vacant'",
                'created_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ]);
            $report .= "[OK] Table 'properties' ready\n";

            // 8. Relational Constraints
            try {
                $this->db->alter('csv_records', 'ADD INDEX', 'idx_job_id', '(job_id)');
                $this->db->alter('csv_records', 'ADD FOREIGN KEY', 'fk_job_id', '(job_id) REFERENCES jobs(id) ON DELETE CASCADE');
                $report .= "[PATCH] Constraints applied to 'csv_records'\n";
            } catch (Exception $e) {
                $report .= "[SKIP] Constraints already exist\n";
            }

            // 9. Seeding
            $this->seedInitialData($report);

            $report .= "\n✅ Migration completed successfully.\n";
        } catch (Exception $e) {
            $report .= "[ERROR] " . htmlspecialchars($e->getMessage()) . "\n";
        }

        $report .= "</pre>";
        return $report;
    }

    /**
     * Seeds initial data if tables are empty
     */
    private function seedInitialData(string &$report): void
    {
        // Seed Tenants
        if (empty($this->db->find(['tbl' => 'tenants', 'limit' => 1]))) {
            $this->db->save([
                'tbl'    => 'tenants',
                'name'   => 'Sharpishly Global HQ',
                'status' => 'active'
            ]);
            $report .= "[SEED] Initial tenant record added\n";
        }

        // Seed Mugs
        if (empty($this->db->find(['tbl' => 'merchandise_inventory', 'limit' => 1]))) {
            $this->db->save([
                'tbl'         => 'merchandise_inventory',
                'item_name'   => 'Premium White Mug Blank',
                'stock_count' => 1000,
                'unit_price'  => 4.50
            ]);
            $report .= "[SEED] Initial mug stock added\n";
        }

        // Seed Jobs
        if (empty($this->db->find(['tbl' => 'jobs', 'limit' => 1]))) {
            $this->db->save([
                'tbl'            => 'jobs',
                'title'          => 'System Initial Test',
                'file_path'      => 'storage/uploads/seed.csv',
                'status'         => 'pending',
                'total_rows'     => 500,
                'processed_rows' => 0,
                'note'           => 'Initial system-generated seed job.'
            ]);
            $report .= "[SEED] Initial job record added\n";
        }

        // Seed Properties
        if (empty($this->db->find(['tbl' => 'properties', 'limit' => 1]))) {
            $this->db->save([
                'tbl'          => 'properties',
                'name'         => 'TARDIS View Apartments',
                'address'      => '742 Evergreen Terrace',
                'unit_number'  => 'A1',
                'monthly_rent' => 1200.00,
                'status'       => 'occupied'
            ]);
            $report .= "[SEED] Initial property record added\n";
        }
    }
}