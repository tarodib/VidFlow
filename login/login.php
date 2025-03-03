<?php
require 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$input, $input]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['is_verified']) {
            $_SESSION["username_in"]=$user["username"];
            $_SESSION["profilepic_in"]=$user["profilepic"];
            if($user["plan"] == "3"){
                $_SESSION["userplan_status"] = "<img src='/pageelements/verifiedicon.png' width='15' height='auto' id='userprofilestatus' class='headerelements'/>&nbsp;";
            }
            // Getting data for style code generated for user
            $getstylecode_raw = $user["style_code"];
            $getstylefromstylecode = $pdo->prepare("SELECT * FROM customization WHERE style_code = ?");
            $getstylefromstylecode->execute([$getstylecode_raw]);
            $getstylecode = $getstylefromstylecode->fetch();
            $_SESSION["bkgColor"] = $getstylecode["backgroundColor"];
            $_SESSION["fontFamily"] = $getstylecode["fontfamilyType"];
            $splitvideoborderColor = $getstylecode["videoborderColor"];
            $splittedvideoborderColor = explode(";", $splitvideoborderColor);
            $_SESSION["firstvideoborderColor"] = $splittedvideoborderColor[0];
            $_SESSION["secondvideoborderColor"] = $splittedvideoborderColor[1];
            header("Location: /");
        } else {
            $_SESSION["error"] .= "<img src='/pageelements/warning_icon.png' width='30' height='auto' alt='Warning!' style='display: inline-block; vertical-align: middle;'/>
        <span style='color: #d16500; font-family: Arial; display: inline-block; vertical-align: middle;'>Kérjük hitelesítse fiókját a bejelentkezéshez!</span><br>";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION["error"] .= "<img src='/pageelements/caution_icon.png' width='30' height='auto' alt='Warning!' style='display: inline-block; vertical-align: middle;'/>
        <span style='color: red; font-family: Arial; display: inline-block; vertical-align: middle;'>Hibás felhasználónév vagy jelszó!</span><br>";
        header("Location: index.php");
        exit();
    }
} 
?>
