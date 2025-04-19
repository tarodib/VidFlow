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

if(isset($_SESSION['username_in'])){
if ($_SERVER["REQUEST_METHOD"] == "POST") {
$username = $_SESSION["username_in"];
$videourl = $_POST["watchvideourl"];
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$getIfUserAlreadyHasWatchingHistory = "SELECT username FROM watching_history WHERE username = ?";
$execIfUserAlreadyHasWatchingHistory = $conn->prepare($getIfUserAlreadyHasWatchingHistory);
$execIfUserAlreadyHasWatchingHistory->bind_param("s", $username);
$execIfUserAlreadyHasWatchingHistory->execute();
$resultIfUserAlreadyHasWatchingHistory = $execIfUserAlreadyHasWatchingHistory->get_result();
if($resultIfUserAlreadyHasWatchingHistory->num_rows == 0){
    $properVideoURL = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://${_SERVER['HTTP_HOST']}/watch?id=" . $videourl . ";";
    $addUserToWatchingHistory = "INSERT INTO watching_history(username, video_url) VALUES (?, ?)";
    $execaddUserToWatchingHistory = $conn->prepare($addUserToWatchingHistory);
    $execaddUserToWatchingHistory->bind_param("ss", $username, $properVideoURL);
    $execaddUserToWatchingHistory->execute();
} else if($resultIfUserAlreadyHasWatchingHistory->num_rows > 0) {
    $selectUserWatchingHistory = "SELECT video_url FROM watching_history WHERE username = ?";
    $execselectUserWatchingHistory = $conn->prepare($selectUserWatchingHistory);
    $execselectUserWatchingHistory->bind_param("s", $username);
    $execselectUserWatchingHistory->execute();
    $resultselectUserWatchingHistory = $execselectUserWatchingHistory->get_result();
    $row = $resultselectUserWatchingHistory->fetch_assoc();
    $existingVideos = $row['video_url'];
    $updatedVideos = $existingVideos . (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://${_SERVER['HTTP_HOST']}/watch?id=" . $videourl . ";";
    $updateUserWatchingHistory = "UPDATE watching_history SET video_url = ? WHERE username = ?";
    $execupdateUserWatchingHistory = $conn->prepare($updateUserWatchingHistory);
    $execupdateUserWatchingHistory->bind_param("ss", $updatedVideos, $username);
    $execupdateUserWatchingHistory->execute();
}
}
$conn->close();
} else {
    echo("Uh oh! Authentication failed!");
}
?>