<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$db_username = "user";
$db_password = "betamode696";
$dbname = "vidflow";

if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'];

    // Connect to the database
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the email exists
    $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['username'];

        // Generate a reset token and expiry time
        $reset_token = bin2hex(random_bytes(16));
        $reset_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Update the database with the reset token and expiry
        $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
        $updateStmt->bind_param("sss", $reset_token, $reset_expiry, $email);
        $updateStmt->execute();

        // Send reset email
        $mail = new PHPMailer(true);
        try {
            // SMTP settings
            $mail->isSMTP();
            $mail->Host = 'smtp.mail.me.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'birob2005'; // Your SMTP email
            $mail->Password = 'bkfe-oxin-uimw-hads'; // Your SMTP email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email settings
            $mail->setFrom('vidflow@barnatech.hu', 'VidFlow');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'VidFlow Password Reset Request';

            // Prepare reset link
            $resetLink = "http://barnatech.hu/vidflow/reset_password.php?token=$reset_token";

            // Email body
            $mail->Body = "
                <h2>Password Reset Request</h2>
                <p>Hi $username,</p>
                <p>Click the link below to reset your password:</p>
                <a href='$resetLink'>Reset Password</a>
                <p>This link will expire in 1 hour. If you did not request a password reset, ignore this email.</p>
            ";

            $mail->send();
            echo "Password reset email sent. Please check your inbox.";
        } catch (Exception $e) {
            echo "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "No account found with that email address.";
    }

    $stmt->close();
    $conn->close();
}

?>

<!-- HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>
    <form method="POST" action="">
        <h2>Forgot Password</h2>
        <label for="email">Enter your email address:</label>
        <input type="email" name="email" id="email" required>
        <button type="submit" name="forgot_password">Reset Password</button>
    </form>
</body>
</html>
