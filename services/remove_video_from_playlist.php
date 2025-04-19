<?php
session_start();
ini_set('display_errors', 1);
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
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["playlist_name"]) && isset($_POST["video_id"])) {
        $username = $_SESSION['username_in'];
        $playlistName = $_POST["playlist_name"];
        $videoIdToRemove = $_POST["video_id"];
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            echo("Hiba: Adatbázis kapcsolódási hiba: " . $conn->connect_error);
            exit;
        }
        $sqlGet = "SELECT playlist_videos FROM playlists WHERE username = ? AND playlist_name = ?";
        $stmtGet = $conn->prepare($sqlGet);
        if ($stmtGet === false) {
            echo "Hiba az SQL lekérdezés előkészítésekor (SELECT): " . $conn->error;
            $conn->close();
            exit;
        }
        $stmtGet->bind_param("ss", $username, $playlistName);
        $stmtGet->execute();
        $resultGet = $stmtGet->get_result();

        if ($resultGet->num_rows > 0) {
            $row = $resultGet->fetch_assoc();
            $currentVideosString = $row['playlist_videos'] ?? '';
            $videosArray = array_filter(explode(';', rtrim($currentVideosString, ';')));
            $initialCount = count($videosArray);
            $videosArray = array_diff($videosArray, [$videoIdToRemove]);
            $finalCount = count($videosArray);
            $newVideosString = implode(';', $videosArray);
            if (!empty($newVideosString)) {
                $newVideosString .= ';';
            }
            if ($finalCount < $initialCount) {
                $sqlUpdate = "UPDATE playlists SET playlist_videos = ? WHERE username = ? AND playlist_name = ?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                if ($stmtUpdate === false) {
                     echo "Hiba az SQL lekérdezés előkészítésekor (UPDATE): " . $conn->error;
                     $stmtGet->close();
                     $conn->close();
                     exit;
                }
                $stmtUpdate->bind_param("sss", $newVideosString, $username, $playlistName);
                if ($stmtUpdate->execute()) {
                    echo "Videó ('" . htmlspecialchars($videoIdToRemove) . "') sikeresen eltávolítva a '" . htmlspecialchars($playlistName) . "' listából!";
                } else {
                    echo "Hiba történt a lista frissítése során: " . $stmtUpdate->error;
                }
                $stmtUpdate->close();
            }
        }
        $stmtGet->close();
        $conn->close();

    } else {
         echo("Hiba: Hiányzó adatok a videó eltávolításához.");
    }
} else {
    echo("Uh oh! Authentication failed!");
}
?>

