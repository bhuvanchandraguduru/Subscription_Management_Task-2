<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) { header("Location: login.php"); exit; }

// Fetch subscriptions
$stmt = $pdo->prepare("SELECT name, price, start_date, end_date FROM subscriptions WHERE user_id = ?");
$stmt->execute([$user_id]);
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=subscriptions.csv');

$output = fopen('php://output', 'w');

// Add header row
fputcsv($output, ['Name', 'Price', 'Start Date', 'End Date']);

// Add data rows
foreach ($subscriptions as $sub) {
    fputcsv($output, $sub);
}

fclose($output);
exit;
?>
