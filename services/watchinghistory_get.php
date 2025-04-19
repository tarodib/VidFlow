<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$servername = $_ENV['DB_SERVERNAME'];
$db_username = $_ENV['DB_USERNAME'];
$db_password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];
$watchHistoryUrls = [];
if(isset($_SESSION['username_in'])){
    $username = $_SESSION["username_in"];
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $getHistoryCommand = "SELECT video_url FROM watching_history WHERE username = ?";
    $stmt = $conn->prepare($getHistoryCommand);
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (!empty($row["video_url"])) {
                $urls = explode(";", $row["video_url"]);
                $watchHistoryUrls = array_filter($urls, 'strlen');
                $watchHistoryUrls = array_values($watchHistoryUrls);
            }
        }
        $stmt->close();
    }
    $conn->close();
    echo json_encode($watchHistoryUrls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} else {
    echo("Uh oh! Authentication failed!");
}
?>