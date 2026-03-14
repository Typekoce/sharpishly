#!/bin/bash

# 1. Create Directory Structure
mkdir -p php/src/Controllers php/src/Services tests/unit

# 2. Generate bootstrap.php (The Wiring & Registry)
cat << 'EOF' > php/src/bootstrap.php
<?php
declare(strict_types=1);
namespace App;

use App\Services\Location;
use App\Services\Logger;
use App\Db;

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

class Registry {
    private static array $instances = [];
    public static function get(string $class, ...$args) {
        if (!isset(self::$instances[$class])) self::$instances[$class] = new $class(...$args);
        return self::$instances[$class];
    }
}

// Init Shared Instances
Registry::get(Location::class);
Registry::get(Db::class);
EOF

# 3. Generate Db.php (The Vault)
cat << 'EOF' > php/src/Db.php
<?php
declare(strict_types=1);
namespace App;
use PDO;

class Db {
    private PDO $pdo;
    public function __construct() {
        $dsn = "mysql:host=sharpishly-db;dbname=sharpishly;charset=utf8mb4";
        $this->pdo = new PDO($dsn, 'user', 'pass', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    public function createTable(string $table, string $def) {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS \`$table\` ($def) ENGINE=InnoDB;");
    }
    public function query(string $sql, array $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function save(array $data) {
        $table = $data['tbl']; unset($data['tbl']);
        $cols = implode("\`, \`", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $stmt = $this->pdo->prepare("INSERT INTO \`$table\` (\`$cols\`) VALUES ($placeholders)");
        $stmt->execute(array_values($data));
        return $this->pdo->lastInsertId();
    }
}
EOF

# 4. Generate BaseController.php (The Foundation)
cat << 'EOF' > php/src/Controllers/BaseController.php
<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Registry;
use App\Db;

abstract class BaseController {
    protected Db $db;
    public function __construct() {
        $this->db = Registry::get(Db::class);
    }
    protected function json(mixed $data, int $code = 200): void {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data);
        exit;
    }
}
EOF

# 5. Generate the Test Runner
cat << 'EOF' > tests/run.php
<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/src/bootstrap.php';

class TestRunner {
    public int $passed = 0; $failed = 0;
    public function assert($cond, $msg) {
        if ($cond) { echo "✅ PASS: $msg\n"; $this->passed++; }
        else { echo "❌ FAIL: $msg\n"; $this->failed++; }
    }
}
$tester = new TestRunner();
require_once __DIR__ . '/unit/BaseControllerTest.php';
(new \App\Tests\BaseControllerTest($tester))->run();
echo "\nPassed: {$tester->passed} | Failed: {$tester->failed}\n";
EOF

# 6. Generate BaseControllerTest.php
cat << 'EOF' > tests/unit/BaseControllerTest.php
<?php
declare(strict_types=1);
namespace App\Tests;
use App\Controllers\BaseController;

class MockController extends BaseController {
    public function testJson() { $this->json(['test' => true]); }
}

class BaseControllerTest {
    private $t;
    public function __construct($t) { $this->t = $t; }
    public function run() {
        $this->t->assert(class_exists('App\Controllers\BaseController'), "BaseController class exists.");
        $controller = new MockController();
        $this->t->assert($controller instanceof \App\Controllers\BaseController, "Inheritance works.");
    }
}
EOF

chmod +x generate-core.sh
echo "🏁 reviewed files and tests generated."
