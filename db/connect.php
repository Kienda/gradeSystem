<?php
$dbPath = __DIR__ . '/database.sqlite';

if (!file_exists($dbPath)) {
    file_put_contents($dbPath, '');
}

try {
    $pdo = new PDO('sqlite:' . $dbPath); // renamed from $db
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Database error: ' . $e->getMessage());
}

