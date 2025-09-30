<?php
include 'db.php'; // this gives us $pdo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$name, $email, $hashedPassword]);

        // ✅ Redirect to login page with success flag
        header("Location: login.html?registered=1");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // duplicate email
            echo "⚠️ Email already exists. Please use another.";
        } else {
            echo "❌ Error: " . $e->getMessage();
        }
    }
}
?>