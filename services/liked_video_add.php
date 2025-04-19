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
$videolength = $_POST["videolength"];
$videouploader = $_POST["videouploader"];
$videouploaderpic = $_POST["videouploaderpic"];
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$getIfUserAlreadyHasLikedVideos = "SELECT username FROM liked_videos WHERE username = ?";
$execIfUserAlreadyHasLikedVideos = $conn->prepare($getIfUserAlreadyHasLikedVideos);
$execIfUserAlreadyHasLikedVideos->bind_param("s", $username);
$execIfUserAlreadyHasLikedVideos->execute();
$resultIfUserAlreadyHasLikedVideos = $execIfUserAlreadyHasLikedVideos->get_result();
if($resultIfUserAlreadyHasLikedVideos->num_rows == 0){
    $properVideoId = "" . $videoid . ";";
    $properVideoTitle = "" . $videotitle . ";";
    $properVideoLength = "" . $videolength . ";";
    $properVideoUploader = "" . $videouploader . ";";
    $properVideoUploaderPic = "" . $videouploaderpic . ";";
    $addUserToLikedVideos = "INSERT INTO liked_videos(username, video_url, video_title, video_length, video_uploader, video_uploader_pic) VALUES (?, ?, ?, ?, ?, ?)";
    $execaddUserToLikedVideos = $conn->prepare($addUserToLikedVideos);
    $execaddUserToLikedVideos->bind_param("ssssss", $username, $properVideoId, $properVideoTitle, $properVideoLength, $properVideoUploader, $properVideoUploaderPic);
    $execaddUserToLikedVideos->execute();
} else if($resultIfUserAlreadyHasLikedVideos->num_rows > 0) {
    $updateUserLikedVideos = "SELECT video_url, video_title, video_length, video_uploader, video_uploader_pic FROM liked_videos WHERE username = ?";
    $execupdateUserLikedVideos = $conn->prepare($updateUserLikedVideos);
    $execupdateUserLikedVideos->bind_param("s", $username);
    $execupdateUserLikedVideos->execute();
    $resultupdateUserLikedVideos = $execupdateUserLikedVideos->get_result();
    $row = $resultupdateUserLikedVideos->fetch_assoc();
    $existingVideos = $row['video_url'];
    $existingVideoTitles = $row['video_title'];
    $existingVideoLengths = $row['video_length'];
    $existingVideoUploaders = $row['video_uploader'];
    $existingVideoUploaderPics = $row['video_uploader_pic'];
    $videoToAddCheck = $videoid . ";";
    if (strpos($existingVideos, $videoToAddCheck) === false) {
    $updatedVideos = $existingVideos . $videoid . ";";
    $updatedVideoTitles = $existingVideoTitles . $videotitle . ";";
    $updatedVideoLengths = $existingVideoLengths . $videolength . ";";
    $updatedVideoUploaders = $existingVideoUploaders . $videouploader . ";";
    $updatedVideoUploaderPics = $existingVideoUploaderPics . $videouploaderpic . ";";
    $updateUserLikedVideos = "UPDATE liked_videos SET video_url = ?, video_title = ?, video_length = ?, video_uploader = ?, video_uploader_pic = ? WHERE username = ?";
    $execupdateUserLikedVideos = $conn->prepare($updateUserLikedVideos);
    $execupdateUserLikedVideos->bind_param("ssssss", $updatedVideos, $updatedVideoTitles, $updatedVideoLengths, $updatedVideoUploaders, $updatedVideoUploaderPics, $username);
    $execupdateUserLikedVideos->execute();
    }
}
$conn->close();
}
} else {
    echo("Uh oh! Authentication failed!");
}
?>