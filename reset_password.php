<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$db_username = "user";
$db_password = "betamode696";
$dbname = "vidflow";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Connect to the database
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Validate the token
    $stmt = $conn->prepare("SELECT email, reset_expiry FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $reset_expiry = $row['reset_expiry'];

        // Check if the token has expired
        if (strtotime($reset_expiry) < time()) {
            echo "This reset link has expired.";
            exit();
        }

        if (isset($_POST['reset_password'])) {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password !== $confirm_password) {
                echo "Passwords do not match.";
            } else {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password and clear the reset token
                $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE email = ?");
                $updateStmt->bind_param("ss", $hashed_password, $email);

                if ($updateStmt->execute()) {
                    echo "Password reset successful. You can now log in with your new password.";
                } else {
                    echo "Failed to reset password.";
                }
            }
        }
    } else {
        echo "Invalid or expired reset token.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "No reset token provided.";
}

?>

<!-- HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <form method="POST" action="">
        <h2>Reset Your Password</h2>
        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password" required>
        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        <button type="submit" name="reset_password">Reset Password</button>
    </form>
</body>
</html>
