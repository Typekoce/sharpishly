<?php
// src/Db.php

declare(strict_types=1);

namespace App;   // ← root namespace of your app (not Controllers)

use PDO;
use PDOException;
use InvalidArgumentException;

class Db
{
    public ?PDO $pdo = null;

    public function __construct()
    {

        $host     = getenv('DB_HOST');
        $dbname   = getenv('DB_NAME');
        $user     = getenv('DB_USER');
        $password = getenv('DB_PASS');
        $charset  = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

        try {
            $this->pdo = new PDO(
                $dsn,
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            throw new PDOException("Connection failed: " . $e->getMessage());
        }
    }

    public function find(array $conditions): array
    {
        if (empty($conditions['tbl'])) {
            throw new InvalidArgumentException("Table name ('tbl') is required");
        }

        $table  = $this->escapeIdentifier($conditions['tbl']);
        $fields = $conditions['fields'] ?? '*';
        $where  = $conditions['where']  ?? [];
        $order  = $conditions['order']  ?? [];
        $limit  = $conditions['limit']  ?? null;
        $offset = $conditions['offset'] ?? null;

        $select = is_array($fields)
            ? implode(', ', array_map([$this, 'escapeIdentifier'], $fields))
            : '*';

        $whereSql = '';
        $params   = [];
        if ($where) {
            $clauses = [];
            foreach ($where as $key => $value) {
                if (preg_match('/^(.*?)\s*([<>=!]+)$/i', $key, $m)) {
                    $col = trim($m[1]);
                    $op  = $m[2];
                } else {
                    $col = $key;
                    $op  = '=';
                }
                $ph = ':' . preg_replace('/[^a-zA-Z0-9_]/', '_', $col);
                $clauses[] = $this->escapeIdentifier($col) . " $op $ph";
                $params[$ph] = $value;
            }
            $whereSql = ' WHERE ' . implode(' AND ', $clauses);
        }

        $orderSql = '';
        if ($order) {
            $parts = [];
            foreach ($order as $col => $dir) {
                $parts[] = $this->escapeIdentifier($col) . ' ' . (strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC');
            }
            $orderSql = ' ORDER BY ' . implode(', ', $parts);
        }

        $limitSql = '';
        if ($limit !== null) {
            $limitSql = ' LIMIT ' . (int)$limit;
            if ($offset !== null) {
                $limitSql .= ' OFFSET ' . (int)$offset;
            }
        }

        $sql = "SELECT $select FROM $table{$whereSql}{$orderSql}{$limitSql}";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query failed: $sql → " . $e->getMessage());
            return [];
        }
    }

    /**
     * Save (INSERT or UPDATE) a record
     *
     * @param array $data Associative array with:
     *   - 'tbl'        => table name (required)
     *   - 'id'         => if present → UPDATE, else INSERT
     *   - other keys   → column names and values
     * @return int Last insert ID (on insert) or number of affected rows (on update)
     * @throws InvalidArgumentException
     * @throws PDOException
     */
    public function save(array $data): int
    {
        if (empty($data['tbl'])) {
            throw new InvalidArgumentException("Table name ('tbl') is required");
        }

        $table = $this->escapeIdentifier($data['tbl']);
        unset($data['tbl']); // remove meta key

        // Decide INSERT or UPDATE
        $isUpdate = !empty($data['id']) && is_numeric($data['id']) && $data['id'] > 0;
        $id = $isUpdate ? (int)$data['id'] : null;
        unset($data['id']); // remove id from columns to set

        if (empty($data)) {
            throw new InvalidArgumentException("No fields to save");
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        if ($isUpdate) {
            // UPDATE
            $setParts = [];
            foreach ($columns as $col) {
                $setParts[] = $this->escapeIdentifier($col) . ' = :' . $col;
            }
            $setClause = implode(', ', $setParts);

            $sql = "UPDATE $table SET $setClause WHERE id = :id";
            $params = $data;
            $params['id'] = $id;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->rowCount(); // usually 1 or 0
        } else {
            // INSERT
            $columnsEscaped = array_map([$this, 'escapeIdentifier'], $columns);
            $sql = "INSERT INTO $table (" . implode(', ', $columnsEscaped) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);

            return (int)$this->pdo->lastInsertId();
        }
    }

    public function escapeIdentifier(string|int $identifier): string
    {
        return '`' . str_replace('`', '``', (string)$identifier) . '`';
    }

    // src/Db.php
    public function create(array $schema): void
    {
        $table = $schema['tbl'] ?? throw new InvalidArgumentException("Missing 'tbl'");
        unset($schema['tbl']);

        $engine  = $schema['ENGINE'] ?? 'InnoDB';
        unset($schema['ENGINE']);

        $colDefs = [];
        foreach ($schema as $col => $def) {
            $colDefs[] = $this->escapeIdentifier($col) . ' ' . $def;
        }

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->escapeIdentifier($table) . " (
            " . implode(",\n        ", $colDefs) . "
        ) ENGINE=$engine DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->pdo->exec($sql);
    }

    /**
     * Generic Alter Table helper
     * @param string $table Table name
     * @param string $action e.g., "ADD COLUMN", "MODIFY COLUMN", "ADD INDEX"
     * @param string $name Column or Index name
     * @param string $definition SQL definition (e.g., "VARCHAR(255) NOT NULL")
     */
    public function alter(string $table, string $action, string $name, string $definition = ''): void
    {
        $table = $this->escapeIdentifier($table);
        $name  = $this->escapeIdentifier($name);
        
        // Example: ALTER TABLE `jobs` ADD COLUMN `priority` INT DEFAULT 0
        $sql = "ALTER TABLE $table $action $name $definition";
        
        try {
            $this->pdo->exec($sql);
            \App\Services\Logger::info("Schema change: $sql");
        } catch (\PDOException $e) {
            // We log it but don't necessarily crash if the column already exists
            \App\Services\Logger::debug("Alter note (possibly exists): " . $e->getMessage());
        }
    }
}