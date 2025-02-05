<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
include '../database/connection.php';
session_start();

if (isset($_POST['reset_password'])) {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT admin_id FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $token = bin2hex(random_bytes(50));
        $admin_id = $admin['admin_id'];

        $stmt = $conn->prepare("INSERT INTO forgot_password (admin_id, token) VALUES (?, ?)");
        $stmt->execute([$admin_id, $token]);

        $reset_link = "http://localhost/employee-system/admin/reset_password.php?token=$token";

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Use your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'gmanagementtt111@gmail.com'; // Your Gmail
            $mail->Password = 'skbtosbmkiffrajr'; // Your app password (never use personal password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Sender & recipient settings
            $mail->setFrom('gmanagementtt111@gmail.com', 'Admin Support');
            $mail->addAddress($email);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";
            $mail->Body = "Click the link to reset your password: <a href='$reset_link'>$reset_link</a>";

            // Send email
            $mail->send();
            $_SESSION['success'] = "Password reset link has been sent to your email.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['error'] = "Email not found!";
    }

    header('location: admin_login.php');
    exit();
}
