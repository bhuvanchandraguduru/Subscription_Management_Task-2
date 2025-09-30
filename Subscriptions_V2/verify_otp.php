<?php
session_start();
require 'db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $otp_input = trim($_POST['otp']);
    $new_password = trim($_POST['new_password']);

    // Validate OTP
    if(!isset($_SESSION['otp']) || !isset($_SESSION['otp_mobile'])){
        $_SESSION['otp_error'] = "No OTP session found. Try again.";
        header("Location: forgot_password.php");
        exit;
    }

    // Optional: OTP expiry (5 minutes)
    if(time() - $_SESSION['otp_time'] > 300){
        unset($_SESSION['otp']);
        unset($_SESSION['otp_mobile']);
        $_SESSION['otp_error'] = "OTP expired. Please try again.";
        header("Location: forgot_password.php");
        exit;
    }

    if($otp_input != $_SESSION['otp']){
        $_SESSION['otp_error'] = "Invalid OTP.";
        header("Location: verify_otp.php");
        exit;
    }

    // OTP is correct → update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = :pwd WHERE mobile = :mobile");
    $stmt->execute([
        'pwd' => $hashed_password,
        'mobile' => $_SESSION['otp_mobile']
    ]);

    // Clear OTP session
    unset($_SESSION['otp']);
    unset($_SESSION['otp_mobile']);
    unset($_SESSION['otp_time']);

    $_SESSION['otp_success'] = "Password reset successfully! You can now login.";
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify OTP</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="light">
<main class="hero">
  <div class="hero-content">
    <h1>Verify OTP</h1>
    <form action="verify_otp.php" method="POST" class="register-form">
      <input type="text" name="otp" placeholder="Enter OTP" required>
      <input type="password" name="new_password" placeholder="New Password" required>
      <button type="submit">Reset Password</button>
    </form>

    <?php
    if(isset($_SESSION['otp_error'])){
        echo '<p style="color:red;">'.$_SESSION['otp_error'].'</p>';
        unset($_SESSION['otp_error']);
    }
    ?>
  </div>
</main>
</body>
</html>

