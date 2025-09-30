<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reminder_days'])) {
    foreach ($_POST['reminder_days'] as $sub_id => $days) {
        $stmt = $pdo->prepare("UPDATE subscriptions SET reminder_days = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$days, $sub_id, $_SESSION['user_id']]);
    }
    header("Location: settings.php?success=1");
    exit();
}
?>
