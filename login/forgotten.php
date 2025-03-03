<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require '../vendor/autoload.php';

//get variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = $_ENV['DB_SERVERNAME'];
$db_username = $_ENV['DB_USERNAME'];
$db_password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];

if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'];
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['username'];
        $reset_token = bin2hex(random_bytes(16));
        $reset_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
        $updateStmt->bind_param("sss", $reset_token, $reset_expiry, $email);
        $updateStmt->execute();

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USERNAME'];
            $mail->Password = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];
            $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'VidFlow Password Reset Request';
            $resetLink = "http://barnatech.hu/vidflow/reset_password.php?token=$reset_token";
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

