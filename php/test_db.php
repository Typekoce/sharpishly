<?php
try {
    $pdo = new PDO("mysql:host=db;dbname=sharpishly", "user", "pass");
    echo "✅ Success: PHP can talk to MySQL!";
} catch (PDOException $e) {
    echo "❌ Connection Failed: " . $e->getMessage();
}