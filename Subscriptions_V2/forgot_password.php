<?php 
session_start(); 
require 'db.php'; // make sure this connects to your DB

// Step 2: Handle OTP verification and password reset
if (isset($_POST['verify_otp'])) {
    $otp = $_POST['otp'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!isset($_SESSION['otp']) || !isset($_SESSION['reset_mobile'])) {
        $_SESSION['otp_error'] = "Session expired. Please try again.";
        header("Location: forgot_password.php");
        exit;
    }

    if ($otp != $_SESSION['otp']) {
        $_SESSION['otp_error'] = "Incorrect OTP. Try again.";
        header("Location: forgot_password.php");
        exit;
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['otp_error'] = "Passwords do not match.";
        header("Location: forgot_password.php");
        exit;
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $mobile = $_SESSION['reset_mobile'];

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE mobile = ?");
    $stmt->bind_param("ss", $hashed_password, $mobile);

    if ($stmt->execute()) {
        unset($_SESSION['otp'], $_SESSION['reset_mobile']);
        $_SESSION['otp_sent'] = "Password successfully updated!";
    } else {
        $_SESSION['otp_error'] = "Error updating password. Try again.";
    }

    header("Location: forgot_password.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .alert {
      margin-bottom: 15px;
      padding: 12px;
      border-radius: 6px;
      font-size: 14px;
      text-align: center;
    }
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
  </style>
</head>
<body class="light">
<header class="navbar">
  <div class="logo">🔔 Subscription Management System</div>
  <nav><a href="login.html">Login</a></nav>
</header>

<main class="hero">
  <div class="hero-content">
    <h1>Forgot Password</h1>

    <!-- Alerts -->
    <?php if (isset($_SESSION['otp_error'])): ?>
      <div class="alert alert-error">❌ <?= $_SESSION['otp_error']; ?></div>
      <?php unset($_SESSION['otp_error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['otp_sent'])): ?>
      <div class="alert alert-success">✅ <?= $_SESSION['otp_sent']; ?></div>
      <?php unset($_SESSION['otp_sent']); ?>
    <?php endif; ?>

    <?php if (!isset($_SESSION['reset_mobile'])): ?>
      <!-- Step 1: Send OTP -->
      <p>Enter your registered mobile number to receive an OTP.</p>
      <form action="send_otp.php" method="POST" class="register-form">
        <input type="text" name="mobile" placeholder="Enter Mobile Number" required>
        <button type="submit">Send OTP</button>
      </form>
    <?php else: ?>
      <!-- Step 2: Verify OTP and Reset Password -->
      <p>Enter the OTP sent to your mobile and choose a new password.</p>
      <form action="" method="POST" class="register-form">
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit" name="verify_otp">Reset Password</button>
      </form>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
