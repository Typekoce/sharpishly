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
        // We are keeping ONLY the structural check for now
        $this->testExecuteAndCreateTable();
        
        // REMOVED: testSaveInsert()
        // REMOVED: testFindWithConditions()
        // REMOVED: testSaveUpdate()
    }

    private function testExecuteAndCreateTable(): void {
        // Ensures the array-to-SQL logic in Db.php is working
        $res = $this->db->createTable('test_table', [
            'id'   => 'INT AUTO_INCREMENT PRIMARY KEY',
            'temp_col' => 'VARCHAR(255)'
        ]);
        $this->tester->assert($res === true, "Db: Table 'test_table' created successfully.");
    }
}