<?php
// src/Db.php

declare(strict_types=1);

namespace App;   // ← root namespace of your app (not Controllers)

use PDO;
use PDOException;
use InvalidArgumentException;

class Db
{
    private ?PDO $pdo = null;

    public function __construct()
    {
        // ← CHANGE THESE VALUES to match your real database
        // $host     = 'db';          // or 'localhost', or container name in Docker
        // $dbname   = 'sharpishly';  // ← your database name
        // $user     = 'user';           // ← change this
        // $password = 'pass';  // ← change this
        // $charset  = 'utf8mb4';
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

    private function escapeIdentifier(string $id): string
    {
        return '`' . str_replace('`', '``', $id) . '`';
    }
}