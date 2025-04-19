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
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["playlist_name"])) {
        $username = $_POST["username"];
        $playlistName = $_POST["playlist_name"];
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $sql = "DELETE FROM playlists WHERE username = ? AND playlist_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $playlistName);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "Lejátszási lista ('" . htmlspecialchars($playlistName) . "') sikeresen törölve!";
            } else {
                echo "A lejátszási lista ('" . htmlspecialchars($playlistName) . "') nem található.";
            }
        } else {
            echo "Hiba történt a törlés során.";
        }
        $stmt->close();
        $conn->close();
    } else {
         echo("Hiba: Hiányzó adatok a törléshez.");
    }
} else {
    echo("Uh oh! Authentication failed!");
}
?>
