<?php
declare(strict_types=1);

namespace App\Tests;

use App\Registry;
use App\Db;

class DbTest {
    private $tester;
    private $db;

    public function __construct($tester) {
        $this->tester = $tester;
        $this->db = Registry::get(Db::class);
    }

    public function run(): void {
        $this->testCreateTable();
        $this->testSaveInsert();
        $this->testSaveUpdate();
    }

    private function testCreateTable(): void {
        $this->db->createTable('test_table', "id INT AUTO_INCREMENT PRIMARY KEY, val VARCHAR(50)");
        $result = $this->db->query("SHOW TABLES LIKE 'test_table'");
        $this->tester->assert(count($result) > 0, "Db: Table 'test_table' created.");
    }

    private function testSaveInsert(): void {
        $id = $this->db->save(['tbl' => 'test_table', 'val' => 'Original Value']);
        $this->tester->assert(is_numeric($id), "Db: Save (Insert) returned numeric ID.");
        
        $row = $this->db->find(['tbl' => 'test_table', 'where' => ['id' => $id]]);
        $this->tester->assert($row[0]['val'] === 'Original Value', "Db: Data verified after Insert.");
    }

    private function testSaveUpdate(): void {
        // We find the last inserted row
        $rows = $this->db->find(['tbl' => 'test_table', 'limit' => 1, 'order' => ['id' => 'DESC']]);
        $id = $rows[0]['id'];

        // Perform Update
        $this->db->save(['tbl' => 'test_table', 'id' => $id, 'val' => 'Updated Value']);
        
        // Verify Update
        $updatedRow = $this->db->find(['tbl' => 'test_table', 'where' => ['id' => $id]]);
        $this->tester->assert($updatedRow[0]['val'] === 'Updated Value', "Db: Save (Update) successfully modified the record.");
        
        // Final Cleanup
        $this->db->query("DROP TABLE test_table");
    }
}