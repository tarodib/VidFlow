<?php
session_start();
require '../vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$host = $_ENV['DB_SERVERNAME'];
$dbusername = $_ENV['DB_USERNAME'];
$dbpassword = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];
if(isset($_SESSION["username_in"])){
    if($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = $_SESSION["username_in"];
        $getnewuserusername = $_POST["newusername"];
        $getnewusername = $_POST["newname"];
        $getnewuseremail = $_POST["newemail"];
        if (empty($getnewuserusername) || empty($getnewusername) || empty($getnewuseremail)) {
            echo(json_encode(["status" => "error", "message" => "Helytelen adatok megadva!"]));
            exit;
        }
        $conn = new mysqli($host, $dbusername, $dbpassword, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $getuserdata_command = "SELECT username, name, email FROM users WHERE username != ?";
        $execgetuserdata = $conn->prepare($getuserdata_command);
        $execgetuserdata->bind_param("s", $username);
        $execgetuserdata->execute();
        $resultgetuserdata = $execgetuserdata->get_result();
        $errorsduringchange = 0;
        if ($resultgetuserdata->num_rows > 0) {
            while($usernamerow = $resultgetuserdata->fetch_assoc()) {
                if($getnewuserusername == $usernamerow["username"]){
                    echo(json_encode(["status" => "error", "message" => "<img src='pageelements/warning_icon.png' width='30' height='auto'/>&nbsp;A felhasználónévvel már létezik fiók!"]));
                    $errorsduringchange=1;
                    exit;
                } else if($getnewuseremail == $usernamerow["email"]){
                    echo(json_encode(["status" => "error", "message" => "<img src='pageelements/warning_icon.png' width='30' height='auto'/>&nbsp;Az email címmel már létezik fiók!"]));
                    $errorsduringchange=1;
                    exit;
                } else if($getnewusername == $usernamerow["name"]){
                    echo(json_encode(["status" => "error", "message" => "<img src='pageelements/warning_icon.png' width='30' height='auto'/>&nbsp;A névvel már létezik fiók!"]));
                    $errorsduringchange=1;
                    exit;
                }
            }
        }
        if($errorsduringchange == 0){
           $uploadnewuserdata_command = "UPDATE users SET username = ?, email = ?, name = ? WHERE username = ?";
           $execuploadnewuserdata = $conn->prepare($uploadnewuserdata_command);
           $execuploadnewuserdata->bind_param("ssss", $getnewuserusername, $getnewuseremail, $getnewusername, $username);
           $execuploadnewuserdata->execute();
           if($getnewuserusername != $username){
                $uploadnewuserlikedvideosdata_command = "UPDATE liked_videos SET username = ? WHERE username = ?";
                $execuploadnewuserlikedvideosdata = $conn->prepare($uploadnewuserlikedvideosdata_command);
                $execuploadnewuserlikedvideosdata->bind_param("ss", $getnewuserusername, $username);
                $execuploadnewuserlikedvideosdata->execute();
                echo json_encode(["status" => "usernamesuccess", "message" => 'Felhasználónév változtatás miatt újboli belépés szükséges a fiókba!']);
                session_destroy();
                exit;
           } else {
                echo json_encode(["status" => "success", "message" => 'Sikeres adatmódosítás!']);
           } 
        }
        $conn->close();
    } else {
        $username = $_SESSION["username_in"];
        $conn = new mysqli($host, $dbusername, $dbpassword, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $alluserdata = [];
        $getuserdata_command = "SELECT username, name, email FROM users WHERE username = ?";
        $execgetuserdata = $conn->prepare($getuserdata_command);
        $execgetuserdata->bind_param("s", $username);
        $execgetuserdata->execute();
        $resultgetuserdata = $execgetuserdata->get_result();
        $row = $resultgetuserdata->fetch_assoc();
        $alluserdata[] = $row["username"];
        $alluserdata[] = $row["name"];
        $alluserdata[] = $row["email"];
        echo(json_encode($alluserdata));
        $conn->close();
    }
}
?>