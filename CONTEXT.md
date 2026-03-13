# Sharpishly Debug Context
> Use this template to provide instant state awareness when a session starts.

## 🚀 1. Current Objective
- **Task:** (e.g., Implementing the Worker Agent processing loop)
- **Status:** (e.g., Stream works, but .job file isn't being read)

## 📂 2. Pathing Context
- **Controller:** `/var/www/html/php/src/Controllers/CsvController.php`
- **FrontController:** `/var/www/html/php/index.php`
- **Storage Root:** `/var/www/html/storage/` (mapped to host `./storage`)
- **Worker Script:** `/var/www/html/php/src/worker-daemon.php`

## 🛠️ 3. Environment State
- **Docker User:** `0:0 (root)`
- **PHP Version:** `8.2.30`
- **Logs Command:** `docker logs sharpishly-php --tail 20`
- **Permissions:** `storage/` is set to `777`

```

    try {
        // --- Table: merchandise_inventory (The Warehouse) ---
        $this->createTable('merchandise_inventory', [
            'id'           => 'INT AUTO_INCREMENT PRIMARY KEY',
            'item_name'    => 'VARCHAR(255) NOT NULL',
            'stock_count'  => 'INT DEFAULT 0',
            'unit_price'   => 'DECIMAL(10,2)',
            'updated_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);
        $report .= "[OK] Table 'merchandise_inventory' ready\n";

        // --- Table: orders (B2B/B2C Transactions) ---
        $this->createTable('orders', [
            'id'           => 'INT AUTO_INCREMENT PRIMARY KEY',
            'order_type'   => "ENUM('B2C', 'B2B') DEFAULT 'B2C'",
            'club_logo'    => 'VARCHAR(100)',
            'quantity'     => 'INT DEFAULT 1',
            'total_price'  => 'DECIMAL(10,2)',
            'status'       => "ENUM('pending', 'paid', 'shipped') DEFAULT 'pending'",
            'created_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        ]);
        $report .= "[OK] Table 'orders' ready\n";

        // --- Seed Initial Stock ---
        $stock = $this->db->find(['tbl' => 'merchandise_inventory', 'limit' => 1]);
        if (empty($stock)) {
            $this->db->save([
                'tbl' => 'merchandise_inventory',
                'item_name' => 'Premium White Mug Blank',
                'stock_count' => 1000,
                'unit_price' => 4.50
            ]);
            $report .= "[SEED] Initial mug stock added\n";
        }

    // [Your existing Hardware, Jobs, and Tasks tables follow here...]

            $this->createTable('hardware_scans', [
                'id'           => 'BIGINT AUTO_INCREMENT PRIMARY KEY',
                'scan_type'    => 'VARCHAR(50) DEFAULT "full"',
                'usb_count'    => 'INT DEFAULT 0',
                'cpu_info'     => 'VARCHAR(255)',
                'memory_info'  => 'JSON',
                'network_info' => 'JSON',
                'raw_data'     => 'JSON',
                'created_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            ], [
                'engine'  => 'InnoDB',
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ]);
            $report .= "[OK] Table 'hardware_scans' created or already exists\n";

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

```

## 📋 4. Recent Logs / Error Output
```text
[PASTE LAST 5-10 LINES OF app.log OR DOCKER LOGS HERE]
