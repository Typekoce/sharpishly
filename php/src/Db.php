<?php
declare(strict_types=1);

namespace App;

use PDO;
use Exception;
use App\Services\Logger;

class Db {
    private PDO $pdo;

    public function __construct()
    {
        $host = 'sharpishly-db';
        $db   = 'sharpishly';
        $user = 'user';
        $pass = 'pass';
        $dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // Logs to storage/logs/app.log via Registry-managed Logger
            Logger::error("Database Connection Failed: " . $e->getMessage());
            throw new Exception("Database connection error.");
        }
    }

    /**
     * Creates a table if it doesn't exist.
     */
    public function createTable(string $table, string $definition): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `$table` ($definition) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->pdo->exec($sql);
        Logger::info("Table verified/created", ['table' => $table]);
    }

    /**
     * Handles schema updates (ADD COLUMN, INDEX, etc.)
     */
    public function alter(string $table, string $action, string $name, string $definition = ''): void
    {
        $sql = "ALTER TABLE `$table` $action `$name` $definition";
        $this->pdo->exec($sql);
        Logger::info("Schema change executed", ['sql' => $sql]);
    }

    /**
     * Primary Persistence: Automatically handles INSERT vs UPDATE
     */
    public function save(array $data): int|string
    {
        $table = $data['tbl'];
        unset($data['tbl']);

        if (isset($data['id'])) {
            // UPDATE EXISTING
            $id = $data['id'];
            unset($data['id']);
            $fields = implode(' = ?, ', array_keys($data)) . ' = ?';
            $sql = "UPDATE `$table` SET $fields WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([...array_values($data), $id]);
            return $id;
        } else {
            // INSERT NEW
            $columns = implode('`, `', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO `$table` (`$columns`) VALUES ($placeholders)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($data));
            return $this->pdo->lastInsertId();
        }
    }

    /**
     * Standard Query Execution (for custom SQL)
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Shorthand Finder with Filter/Sort/Limit logic
     */
    public function find(array $params): array
    {
        $table = $params['tbl'];
        $whereSql = '';
        $values = [];

        if (isset($params['where'])) {
            $conditions = [];
            foreach ($params['where'] as $col => $val) {
                $conditions[] = "`$col` = ?";
                $values[] = $val;
            }
            $whereSql = 'WHERE ' . implode(' AND ', $conditions);
        }

        $order = isset($params['order']) ? "ORDER BY " . key($params['order']) . " " . current($params['order']) : "";
        $limit = isset($params['limit']) ? "LIMIT " . $params['limit'] : "";

        return $this->query("SELECT * FROM `$table` $whereSql $order $limit", $values);
    }
}