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
    $username = $_POST["user"];
    $videoid = $_POST["videoid"];
    $videotitle = $_POST["videotitle"];
    $videotitle = str_replace("%27", "'", $videotitle);
    $videolength = $_POST["videolength"];
    $videouploader = $_POST["videouploader"];
    $videouploader = str_replace("%27", "'", $videouploader);
    $videouploaderpic = $_POST["videouploaderpic"];
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $getUsersAllLikedVideos = "SELECT video_url, video_title, video_length, video_uploader, video_uploader_pic FROM liked_videos WHERE username = ?";
    $execgetUsersAllLikedVideos = $conn->prepare($getUsersAllLikedVideos);
    $execgetUsersAllLikedVideos->bind_param("s", $username);
    $execgetUsersAllLikedVideos->execute();
    $resultgetUsersAllLikedVideos = $execgetUsersAllLikedVideos->get_result();
    $row = $resultgetUsersAllLikedVideos->fetch_assoc();
    $existingVideos = $row['video_url'];
    $existingVideoTitles = $row['video_title'];
    $existingVideoLengths = $row['video_length'];
    $existingVideoUploaders = $row['video_uploader'];
    $existingVideoUploaderPics = $row['video_uploader_pic'];
    $replaceLikedVideosIds = "" . $videoid . ";";
    $replaceLikedVideosTitles = "" . $videotitle . ";";
    $replaceLikedVideosLengths = "" . $videolength . ";";
    $replaceLikedVideosUploaders = "" . $videouploader . ";";
    $replaceLikedVideosUploaderPics = "" . $videouploaderpic . ";";
    $newLikedVideosIds = str_replace($replaceLikedVideosIds, "", $existingVideos);
    $newLikedVideosTitles = str_replace($replaceLikedVideosTitles, "", $existingVideoTitles);
    $newLikedVideosLengths = str_replace($replaceLikedVideosLengths, "", $existingVideoLengths);
    $newLikedVideosUploaders = str_replace($replaceLikedVideosUploaders, "", $existingVideoUploaders);
    $newLikedVideosUploaderPics = str_replace($replaceLikedVideosUploaderPics, "", $existingVideoUploaderPics);
    $updateUsersAllLikedVideos = "UPDATE liked_videos SET video_url = ?, video_title = ?, video_length = ?, video_uploader = ?, video_uploader_pic = ? WHERE username = ?";
    $execupdateUsersAllLikedVideos = $conn->prepare($updateUsersAllLikedVideos);
    $execupdateUsersAllLikedVideos->bind_param("ssssss", $newLikedVideosIds, $newLikedVideosTitles, $newLikedVideosLengths, $newLikedVideosUploaders, $newLikedVideosUploaderPics, $username);
    $execupdateUsersAllLikedVideos->execute();
    $conn->close();
    }
}

?>