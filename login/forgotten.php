<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require '../vendor/autoload.php';

// Load environment variables
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
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USERNAME'];
            $mail->Password = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];

            // Email settings
            $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'VidFlow Password Reset Request';

            // Prepare reset link
            $resetLink = "http://barnatech.hu/vidflow/reset_password.php?token=$reset_token";

            // Email body
            $mail->Body = "
                <h2>Password Reset Request</h2>
                <p>Hi {$username},</p>
                <p>Click the link below to reset your password:</p>
                <a href='{$resetLink}'>Reset Password</a>
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
    <link rel="stylesheet" href="forgot_password.css">
</head>
<body>
    <div class="container">
        <div class="logindiv">
            <h2>Elfelejtett jelszó</h2>
            <form method="POST" action="">
                <label class="label" for="email">Adja meg e-mail címét:</label><br>
                 <br>
                <input class="field" type="email" name="email" id="email" placeholder="E-mail cím" required><br><br>
                <button class="login" type="submit" name="forgot_password">Jelszó visszaállítása</button>
            </form>
        </div>
    </div>
</body>
</html>
