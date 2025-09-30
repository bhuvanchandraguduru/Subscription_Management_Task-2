<?php
session_start();
require 'db.php'; // your PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = trim($_POST['mobile']);

    // Check if mobile exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = :mobile");
    $stmt->execute(['mobile' => $mobile]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['otp_error'] = "Mobile number not found.";
        header("Location: forgot_password.php"); // ✅ not .html
        exit;
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_mobile'] = $mobile;
    $_SESSION['otp_time'] = time();

    // TODO: Integrate SMS API (like Twilio / MSG91 / Nexmo)
    // Example: sendSMS($mobile, "Your OTP is $otp");

    $_SESSION['otp_sent'] = true;
    header("Location: forgot_password.php"); // ✅ show success alert
    exit;
}
