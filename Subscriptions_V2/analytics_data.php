<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) { die(json_encode(['error'=>'not logged in'])); }

// Count active/expired
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN end_date >= ? THEN 1 ELSE 0 END) AS active,
    SUM(CASE WHEN end_date < ? THEN 1 ELSE 0 END) AS expired
 FROM subscriptions WHERE user_id = ?");
$stmt->execute([$today, $today, $user_id]);
$status = $stmt->fetch(PDO::FETCH_ASSOC);

// Monthly spending
$stmt = $pdo->prepare("SELECT DATE_FORMAT(start_date, '%Y-%m') as month, SUM(price) as total
 FROM subscriptions WHERE user_id = ? GROUP BY month ORDER BY month");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$months = [];
$spending = [];
foreach ($rows as $r) {
    $months[] = $r['month'];
    $spending[] = $r['total'];
}

echo json_encode([
    'active' => (int)$status['active'],
    'expired' => (int)$status['expired'],
    'months' => $months,
    'spending' => $spending
]);
?>
