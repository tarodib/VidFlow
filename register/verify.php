<?php
session_start();
// Database configuration
$servername = "localhost";
$db_username = "user";
$db_password = "betamode696";
$dbname = "vidflow";

// Enable error reporting (useful for debugging; disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Create a new connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("Invalid verification link!");
}

$token = $_GET['token'];

// Prepare a statement to select the user with the provided token
$stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

// Check if the token exists in the database
if ($result->num_rows == 0) {
    echo "Invalid or expired token!";
    exit();
}

$user = $result->fetch_assoc();

// Check if the user is already verified
if ($user['is_verified'] == 1) {
    echo "Account is already verified!";
} else {
    // Update the user's status to verified
    $updateStmt = $conn->prepare("UPDATE users SET is_verified = 1, token = NULL WHERE id = ?");
    $updateStmt->bind_param("i", $user['id']);
    if ($updateStmt->execute()) {
        $_SESSION["error"] .= "<img src='/pageelements/green_tick.png' width='30' height='auto' alt='Success!' style='display: inline-block; vertical-align: middle;'/>
        <span style='color: darkgreen; font-family: Arial; display: inline-block; vertical-align: middle;'>Sikeresen jóváhagyta fiókját!</span><br>";
        header("Location: /");
        exit();
    } else {
        $_SESSION["error"] .= "<img src='/pageelements/warning_icon.png' width='30' height='auto' alt='Error!' style='display: inline-block; vertical-align: middle;'/>
        <span style='color: red; font-family: Arial; display: inline-block; vertical-align: middle;'>Hiba történt a jóváhagyás során! Próbálja újra!</span><br>";
        header("Location: /");
        exit();
    }
    $updateStmt->close();
}

$stmt->close();
$conn->close();
?>
