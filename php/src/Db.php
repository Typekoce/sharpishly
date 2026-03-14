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
