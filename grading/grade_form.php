<?php
require_once('../db/connect.php');
require_once("../login/auth_judge.php");


$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    password TEXT,
    role TEXT
);
");

// Sample accounts
$users = [
    ['judge1', password_hash('pass123', PASSWORD_DEFAULT), 'judge'],
    ['judge2', password_hash('pass123', PASSWORD_DEFAULT), 'judge'],
    ['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin'],
];

foreach ($users as $u) {
    $stmt = $bd->prepare("INSERT OR IGNORE INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute($u);
}

echo "Users created.";
?>
