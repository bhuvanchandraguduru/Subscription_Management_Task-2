<?php
// db.php

$host = 'localhost';
$db   = 'subscription_manager_1'; // your DB name
$user = 'root';
$pass = 'root';  // MAMP default password
$charset = 'utf8mb4';

// ✅ Build DSN string properly
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// ✅ PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// ✅ Create PDO connection
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>