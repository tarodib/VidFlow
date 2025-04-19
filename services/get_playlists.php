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
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["videoid"])) {
        $username = $_POST["username"];
        $videoId = $_POST["videoid"];
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $sql = "SELECT playlist_name FROM playlists WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $safePlaylistName = htmlspecialchars($row["playlist_name"]);
                echo "<div class='playlist-item-container'>";
                echo "<div class='playlist-item' data-playlist-name='" . $safePlaylistName . "'>" . $safePlaylistName . "</div>";
                echo "<img src='/pageelements/trash_icon.png' alt='Törlés' class='playlist-delete-icon' data-playlist-name='" . $safePlaylistName . "' title='Lista törlése'>";
                echo "</div>";
            }
        } else {
            echo "<p style='text-align: center; color: grey;'>Nincsenek lejátszási listáid. Hozz létre egyet!</p>";
        }
        $stmt->close();
        $conn->close();
    } else {
        echo("Hiba: Hiányzó adatok.");
    }
} else {
    echo("Uh oh! Authentication failed!");
}
?>