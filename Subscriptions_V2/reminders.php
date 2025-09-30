<?php
require 'vendor/autoload.php'; // PHPMailer via Composer
require 'db.php'; // your database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. Get subscriptions with billing_date within next 3 days
$stmt = $pdo->prepare("
    SELECT s.id, s.service, s.billing_date, s.reminder_days, u.email, u.name
    FROM subscriptions s
    JOIN users u ON s.user_id = u.id
    WHERE s.reminder_days > 0
    AND DATEDIFF(s.billing_date, CURDATE()) = s.reminder_days
");
$stmt->execute();
$reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Send emails
if ($reminders) {
    foreach ($reminders as $r) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your_email@gmail.com';
            $mail->Password   = 'your_app_password'; // use app password, not your Gmail password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('your_email@gmail.com', 'Subscription Manager');
            $mail->addAddress($r['email'], $r['name']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Reminder: Upcoming payment for {$r['service']}";
            $mail->Body    = "
                <h2>Hi {$r['name']},</h2>
                <p>This is a reminder that your subscription to <b>{$r['service']}</b> 
                is due on <b>{$r['billing_date']}</b>.</p>
                <p>Please ensure your payment method is updated to avoid interruption.</p>
                <br>
                <small>– Subscription Manager</small>
            ";

            $mail->send();
            echo "Reminder sent to {$r['email']} for {$r['service']}<br>";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
} else {
    echo "No reminders today.";
}
?>