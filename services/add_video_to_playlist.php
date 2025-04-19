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
        $username = $_POST["username"];
        $videoId = $_POST["videoid"];
        $playlistName = $_POST["playlist_name"];
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $sql = "SELECT playlist_videos FROM playlists WHERE username = ? AND playlist_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $playlistName);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $existingVideos = $row['playlist_videos'];
        if (strpos($existingVideos, $videoId . ";") === false) {
            $updatedVideos = $existingVideos . $videoId . ";";
            $sql = "UPDATE playlists SET playlist_videos = ? WHERE username = ? AND playlist_name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $updatedVideos, $username, $playlistName);
            if ($stmt->execute()) {
                echo "Videó hozzáadva a lejátszási listához!";
            } else {
                echo "Hiba a videó hozzáadásakor!";
            }
        } else {
            echo "Ez a videó már szerepel a lejátszási listán!";
        }
        $conn->close();
    }
}  else {
    echo("Uh oh! Authentication failed!");
}
?>