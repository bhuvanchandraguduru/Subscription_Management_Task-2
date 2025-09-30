<?php
session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
        
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            // Store error in session and redirect
            $_SESSION['login_error'] = "Invalid email or password.";
            header("Location: login.html?error=1");
            exit();
        }
    } catch (Exception $e) {
        // Handle unexpected errors gracefully
        $_SESSION['login_error'] = "Something went wrong. Please try again.";
        header("Location: login.html?error=1");
        exit();
    }
    
}
