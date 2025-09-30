<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) { header("Location: login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        fgetcsv($handle); // skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            [$name, $price, $start_date, $end_date] = $data;
            $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, name, price, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $price, $start_date, $end_date]);
        }
        fclose($handle);
        header("Location: subscriptions.php?imported=1");
        exit;
    }
}
?>
