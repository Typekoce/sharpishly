<?php
declare(strict_types=1);

namespace App\Tests;

use App\Db;

class DbTest {
    private $tester;
    private Db $db;

    public function __construct($tester) {
        $this->tester = $tester;
        $this->db = new Db();
    }

    public function run(): void {
        $this->testExecuteAndCreateTable();
        $this->testSaveInsert();
        $this->testFindWithConditions();
        $this->testSaveUpdate();
    }

    private function testExecuteAndCreateTable(): void {
        // Test the array-based table creation we just fixed
        $res = $this->db->createTable('test_table', [
            'id'   => 'INT AUTO_INCREMENT PRIMARY KEY',
            'note' => 'VARCHAR(255)'
        ]);
        $this->tester->assert($res === true, "Db: Table 'test_table' created using array definition.");
    }

    private function testSaveInsert(): void {
        $id = $this->db->save([
            'tbl'  => 'test_table',
            'note' => 'Unit Test Entry'
        ]);
        $this->tester->assert(is_int($id) && $id > 0, "Db: Save (Insert) returned numeric ID: $id");
    }

    private function testFindWithConditions(): void {
        $results = $this->db->find([
            'tbl'   => 'test_table',
            'where' => ['note' => 'Unit Test Entry'],
            'limit' => 1
        ]);
        
        $pass = (!empty($results) && $results[0]['note'] === 'Unit Test Entry');
        $this->tester->assert($pass, "Db: find() retrieved data correctly via conditions array.");
    }

    private function testSaveUpdate(): void {
        // Upsert logic (ON DUPLICATE KEY UPDATE)
        $id = $this->db->save([
            'tbl'  => 'test_table',
            'id'   => 1,
            'note' => 'Updated Note'
        ]);
        
        $results = $this->db->find(['tbl' => 'test_table', 'where' => ['id' => 1]]);
        $this->tester->assert($results[0]['note'] === 'Updated Note', "Db: Save (Update) successfully modified the record.");
    }
}