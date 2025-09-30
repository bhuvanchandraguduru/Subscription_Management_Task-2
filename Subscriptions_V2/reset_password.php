<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $_POST['otp'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    if (isset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['reset_user_id'])) {
        if (time() > $_SESSION['otp_expiry']) {
            echo "OTP expired.";
            exit;
        }

        if ($otp == $_SESSION['otp']) {
            $user_id = $_SESSION['reset_user_id'];
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new_password, $user_id]);

            // Clear OTP session
            unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['reset_user_id']);

            echo "Password reset successful. <a href='login.html'>Login</a>";
        } else {
            echo "Invalid OTP.";
        }
    } else {
        echo "Session expired. Try again.";
    }
}
?>
