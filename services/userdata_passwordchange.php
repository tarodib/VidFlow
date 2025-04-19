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
    if($_SERVER["REQUEST_METHOD"] === "POST"){
        $username = $_SESSION["username_in"];
        $getnewpassword = $_POST["newpassword"];
        if(empty($getnewpassword)){
            echo(json_encode(["status" => "error", "message" => "Helytelen adatok megadva!"]));
            exit;
        }
        $conn = new mysqli($host, $dbusername, $dbpassword, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $getuserpassword_command = "SELECT password FROM users WHERE username = ?";
        $execgetuserpassword = $conn->prepare($getuserpassword_command);
        $execgetuserpassword->bind_param("s", $username);
        $execgetuserpassword->execute();
        $resultgetuserpassword = $execgetuserpassword->get_result();
        $currentpasswordrow = $resultgetuserpassword->fetch_assoc();
        if(password_verify($getnewpassword, $currentpasswordrow["password"])){
            echo(json_encode(["status" => "error", "message" => "<img src='pageelements/warning_icon.png' width='30' height='auto'/>&nbsp;Az új jelszó nem egyezhet a jelenlegi jelszóval!"]));
            exit;
        }
        $newpasswordhashing = password_hash($getnewpassword, PASSWORD_DEFAULT);
        $updateuserpassword_command = "UPDATE users SET password = ? WHERE username = ?";
        $execupdateuserpassword = $conn->prepare($updateuserpassword_command);
        $execupdateuserpassword->bind_param("ss", $newpasswordhashing, $username);
        $execupdateuserpassword->execute();
        echo json_encode(["status" => "success", "message" => 'Jelszó sikeresen megváltoztatva!']);
        $conn->close();
    }
}
?>