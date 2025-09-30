<?php
session_start();
header("Content-Type: application/json");
include 'db.php'; // this provides $pdo

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';
$action = $_GET['action'] ?? '';

switch ($action) {
    // 🟢 List subscriptions
    case 'list':
        if ($role === 'admin') {
            // Admin can see all subscriptions with user info
            $stmt = $pdo->query("
                SELECT s.*, u.username, u.email 
                FROM subscriptions s
                JOIN users u ON s.user_id = u.id
                ORDER BY u.username, s.billing_date
            ");
            $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Normal user sees only their own
            $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode(["success" => true, "data" => $subs]);
        break;

    // 🟢 Get single subscription
    case 'get':
        $id = $_GET['id'] ?? 0;
        if ($role === 'admin') {
            $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
        }
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($sub) {
            echo json_encode(["success" => true, "data" => $sub]);
        } else {
            echo json_encode(["success" => false, "error" => "Not found"]);
        }
        break;

    // 🟢 Create subscription
    case 'create':
        $service = $_POST['service'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $billing_date = $_POST['billing_date'] ?? '';
        $status = $_POST['status'] ?? 'Active';

        if (empty($service) || empty($billing_date)) {
            echo json_encode(["success" => false, "error" => "Service and billing date are required"]);
            exit();
        }

        // Admin can assign subscription to any user
        $target_user = ($role === 'admin' && isset($_POST['user_id'])) ? $_POST['user_id'] : $user_id;

        $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, service, amount, billing_date, status) 
                               VALUES (?, ?, ?, ?, ?)");
        $ok = $stmt->execute([$target_user, $service, $amount, $billing_date, $status]);

        echo json_encode(["success" => $ok]);
        break;

    // 🟢 Update subscription
    case 'update':
        $id = $_POST['id'] ?? 0;
        $service = $_POST['service'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $billing_date = $_POST['billing_date'] ?? '';
        $status = $_POST['status'] ?? 'Active';

        if ($role === 'admin') {
            $stmt = $pdo->prepare("UPDATE subscriptions 
                                   SET service=?, amount=?, billing_date=?, status=? 
                                   WHERE id=?");
            $ok = $stmt->execute([$service, $amount, $billing_date, $status, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE subscriptions 
                                   SET service=?, amount=?, billing_date=?, status=? 
                                   WHERE id=? AND user_id=?");
            $ok = $stmt->execute([$service, $amount, $billing_date, $status, $id, $user_id]);
        }

        echo json_encode(["success" => $ok]);
        break;

    // 🟢 Delete subscription
    case 'delete':
        $id = $_POST['id'] ?? 0;
        if ($role === 'admin') {
            $stmt = $pdo->prepare("DELETE FROM subscriptions WHERE id = ?");
            $ok = $stmt->execute([$id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM subscriptions WHERE id = ? AND user_id = ?");
            $ok = $stmt->execute([$id, $user_id]);
        }
        echo json_encode(["success" => $ok]);
        break;

    default:
        echo json_encode(["success" => false, "error" => "Invalid action"]);
}
?>
