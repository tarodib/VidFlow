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

$playlists_data = [];

if (isset($_SESSION["username_in"])) {
    $username = $_SESSION["username_in"];
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        echo json_encode(['error' => 'Adatbázis kapcsolódási hiba: ' . $conn->connect_error]);
        exit;
    }
    $sql = "SELECT playlist_name, playlist_videos FROM playlists WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $playlist_name = $row['playlist_name'];
            $video_ids_string = $row['playlist_videos'] ?? '';
            $video_ids_array = array_filter(explode(';', rtrim($video_ids_string, ';')));
            $playlists_data[] = [
                'name' => $playlist_name,
                'videos' => array_values($video_ids_array)
            ];
        }
        $stmt->close();
    } else {
         $playlists_data = ['error' => 'SQL előkészítési hiba.'];
    }

    $conn->close();

} else {
    echo("Uh oh! Authentication failed!");
}
header('Content-Type: application/json');
echo json_encode($playlists_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>
