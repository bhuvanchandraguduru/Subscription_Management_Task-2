<?php
// search_api.php
session_start();
require 'db.php'; // PDO connection

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$searchTerm = $_GET['q'] ?? '';
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';

if ($role === 'admin') {
    $sql = "SELECT service, amount, billing_date, user_id FROM subscriptions 
            WHERE service LIKE :search ORDER BY billing_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => "%$searchTerm%"]);
} else {
    $sql = "SELECT service, amount, billing_date FROM subscriptions 
            WHERE service LIKE :search AND user_id = :user_id ORDER BY billing_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => "%$searchTerm%", 'user_id' => $userId]);
}

$results = $stmt->fetchAll();
echo json_encode(['success' => true, 'data' => $results]);
