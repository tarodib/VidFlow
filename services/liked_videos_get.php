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

if(isset($_SESSION["username_in"])){
    if (isset($_GET['user'])) {
    $username = $_GET['user'];
    $likedvideoslist = array();
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $getlikedvideodata_command = "SELECT video_url, video_title, video_length, video_uploader, video_uploader_pic FROM liked_videos WHERE username = ?";
    $execgetlikedvideodata = $conn->prepare($getlikedvideodata_command);
    $execgetlikedvideodata->bind_param("s", $username);
    $execgetlikedvideodata->execute();
    $resultgetlikedvideodata = $execgetlikedvideodata->get_result();
    $row = $resultgetlikedvideodata->fetch_assoc();
    $getlikedvideos_urls = explode(";", $row["video_url"]);
    $getlikedvideos_titles = explode(";", $row["video_title"]);
    $getlikedvideos_titles = str_replace("'", "%27", $getlikedvideos_titles);
    $getlikedvideos_lengths = explode(";", $row["video_length"]);
    $getlikedvideos_uploaders = explode(";", $row["video_uploader"]);
    $getlikedvideos_uploaders = str_replace("'", "%27", $getlikedvideos_uploaders);
    $getlikedvideos_uploaderpics = explode(";", $row["video_uploader_pic"]);
    $videokszama = 0;
    while($videokszama < (count($getlikedvideos_urls)-1)){
        $likedvideodetails = array();
        $likedvideodetails[] = $getlikedvideos_urls[$videokszama];
        $likedvideodetails[] = $getlikedvideos_titles[$videokszama];
        $likedvideodetails[] = $getlikedvideos_lengths[$videokszama];
        $likedvideodetails[] = $getlikedvideos_uploaders[$videokszama];
        $likedvideodetails[] = $getlikedvideos_uploaderpics[$videokszama];
        $likedvideoslist[] = $likedvideodetails;
        $videokszama++;
    }
    $json = json_encode($likedvideoslist, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo($json);
    $conn->close();
} else {
    echo("Missing user to get videos from!");
}
} else {
    echo("Uh oh! Authentication failed!");
}
?>