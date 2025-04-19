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
        $playlistName = $_POST["playlist_name"];
         $videoId = $_POST["videoid"];
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $sql = "SELECT playlist_name FROM playlists WHERE username = ? AND playlist_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $playlistName);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "Ez a lejátszási lista már létezik!";
        } else {
            $sql = "INSERT INTO playlists (username, playlist_name, playlist_videos) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $videos = "" . $videoId . ";";
            $stmt->bind_param("sss", $username, $playlistName, $videos);
            if ($stmt->execute()) {
                echo "Lejátszási lista létrehozva!";
            } else {
                echo "Hiba a lejátszási lista létrehozásakor!";
            }
        }
        $conn->close();
    }
} else {
    echo("Uh oh! Authentication failed!");
}
?>