<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require '../vendor/autoload.php';
include "captcha.php";

session_start();
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$servername = $_ENV['DB_SERVERNAME'];
$db_username = $_ENV['DB_USERNAME'];
$db_password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];
$_SESSION["registerinprogress"];
$_SESSION["profilepic_upload_error"];
function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
$securityIPcheckURL = "http://ip-api.com/json/" . get_client_ip() . "?fields=countryCode,hosting";
$securityIPcheck = file_get_contents($securityIPcheckURL);
$getSecurityIPJSON = json_decode($securityIPcheck);
$getSecurityIPCountryCode = $getSecurityIPJSON->countryCode;
$getSecurityIPHosting = $getSecurityIPJSON->hosting;

if($getSecurityIPCountryCode == "HU" & $getSecurityIPHosting == false){
if (isset($_POST["register"])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $passwordconfirm = $_POST['passwordconfirm'];
    $captchacode = intval($_POST["captchainput"]);
    $_SESSION["usertoregister"] = $username;

    // A jelszo es a jelszo megegyszer osszehasonlitasa
    if ($password !== $passwordconfirm) {
        $_SESSION["error"] .= "<img src='/pageelements/caution_icon.png' width='30' height='auto' alt='Warning!' style='display: inline-block; vertical-align: middle;'/>
        <span style='color: red; font-family: Arial; display: inline-block; vertical-align: middle;'>A jelszavak nem egyeznek!</span><br>";
        $_SESSION["registerinprogress"] = false;
        header("Location: index.php");
        exit();
    } else if($captchacode !== $_SESSION["rightCaptchaNumber"]) {
        $_SESSION["error"] .= "<img src='/pageelements/caution_icon.png' width='30' height='auto' alt='Warning!' style='display: inline-block; vertical-align: middle;'/>
        <span style='color: red; font-family: Arial; display: inline-block; vertical-align: middle;'>A Captcha kód nem megfelelő!</span><br>";
        $_SESSION["registerinprogress"] = false;
        header("Location: index.php");
        exit();
    } else {
        //A regisztracio elvegzese
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // profilepicupload_checking if there is another account with the same credentials. If there is than giving error message.
        $getusernames = "SELECT username, email FROM users";
        $usernameresults = $conn->query($getusernames);
        
        if ($usernameresults->num_rows > 0) {
            while($usernamerow = $usernameresults->fetch_assoc()) {
                if($username == $usernamerow["username"]){
                    $_SESSION["error"] .= "<img src='/pageelements/caution_icon.png' width='30' height='auto' alt='Warning!' style='display: inline-block; vertical-align: middle;'/>
                    <span style='color: red; font-family: Arial; display: inline-block; vertical-align: middle;'>A felhasználónév már használatban van!</span><br>";
                    $_SESSION["registerinprogress"] = false;
                    header("Location: index.php");
                    exit();
                } else if($email == $usernamerow["email"]){
                    $_SESSION["error"] .= "<img src='/pageelements/caution_icon.png' width='30' height='auto' alt='Warning!' style='display: inline-block; vertical-align: middle;'/>
                    <span style='color: red; font-family: Arial; display: inline-block; vertical-align: middle;'>A megadott email címmel már létezik fiók!</span><br>";
                    $_SESSION["registerinprogress"] = false;
                    header("Location: index.php");
                    exit();
                }
            }
        }
        
        if(!isset($_SESSION["error"])){
            $_SESSION["registerinprogress"] = true;
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $created_at = date('Y-m-d H:i:s');
            $default_plan = 0;
            $isverified = 0;
            $token = bin2hex(random_bytes(16));
            $stmt = $conn->prepare("INSERT INTO users (name, email, username, password, created_at, plan, is_verified, token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $name, $email, $username, $hashed_password, $created_at, $default_plan, $isverified, $token);

            if ($stmt->execute()) {
                // Generate a unique token for email confirmation
                $token = bin2hex(random_bytes(16));
                $updateTokenStmt = $conn->prepare("UPDATE users SET token = ?, is_verified = 0 WHERE username = ?");
                $updateTokenStmt->bind_param("ss", $token, $username);
                $updateTokenStmt->execute();
                
                // Send a confirmation email with PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // SMTP settings
                    $mail->isSMTP();
                    $mail->Host = $_ENV['SMTP_HOST']; // Replace with your SMTP server
                    $mail->SMTPAuth = true;
                    $mail->Username = $_ENV['SMTP_USERNAME']; // Your SMTP email
                    $mail->Password = $_ENV['SMTP_PASSWORD'];   // Your SMTP email password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->CharSet = "UTF-8";
                    $mail->Encoding = 'base64';
                    $mail->Port = $_ENV['SMTP_PORT'];
            
                    // Email settings
                    $mail->setFrom('vidflow@barnatech.hu', 'VidFlow Support');
                    $mail->addAddress($email, $name);
                    $mail->isHTML(true);
                    $mail->Subject = 'Email Confirmation for VidFlow Registration';
            
                    // Prepare confirmation link
                    $verificationLink = "https://vidflow.barnatech.hu/register/verify.php?token=$token";
            
                    // Email body content
                    $mailContent = "
                        <link href='https://fonts.googleapis.com/css2?family=Inder&display=swap' rel='stylesheet'>
                        <div style='font-family: \0022Inder\0022, Arial; text-align: center; background-color: #dedede;'>
                        <img style='text-align: center;' src='https://vidflow.barnatech.hu/vidflow_official_logo.png' width='250' height='auto'/>
                        <h2>Welcome to VidFlow, $name!</h2>
                        <h3>Thank you for registering! Please click the link below to verify your email address:</h3><br>
                        <a style='border: 2px solid black; padding-top: 10px; padding-bottom: 10px; padding-right: 12px; padding-left: 12px; border-radius: 15px; background-color: #de0f00; color: white; cursor: pointer; text-decoration: none;' href='$verificationLink'>Confirm your email</a>
                        <br><br>
                        <p>If you did not register, you can ignore this email.</p>
                        <br><br><br>
                        <span style='font-size: 11px;'>Copyright © 2024 BarnaTech</span></div>
                    ";
                    $mail->Body = $mailContent;
            
                    $mail->send();
                    header("Location: register.php?customize_profile");
                    unset($_SESSION["error"]);
                } catch (Exception $e) {
                    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                echo "Error: " . $stmt->error;
            }
            
        }

        $stmt->close();
        $conn->close();
    }
}

if(strpos($_SERVER['REQUEST_URI'], "?customize_profile") !== false){
    if($_SESSION["registerinprogress"] == true){
        $_SESSION["registerinprogress"] = false;
        echo '
        <html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil személyreszabása - VidFlow regisztráció</title>
    <link rel="stylesheet" href="registerstyle2.css?ver=1"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Inder&amp;display=swap" rel="stylesheet">
    <link rel="icon" href="/vidflow/webicon.ico">
</head>
<body>
    <div id="profilepicupload_box">
    <h2 id="profilepicupload_title">Profilkép feltöltése</h2>
    <form method="post" enctype="multipart/form-data">
        <label id="profilepicupload_customattach">
        <img id="profilepicupload_preview"><br>
        <input type="file" name="profilepicupload_attach" id="profilepicupload_attach" accept="image/png, image/gif, image/jpeg" style="display: none;">
        <img src="/pageelements/upload_icon-white.png" id="profilepicupload_uploadicon">
        <br><span id="profilepicupload_uploadtext">Válassza ki a feltöltendő fájlt</span>
        </label>
    <span style="font-size: 12px; color: white;">Elfogadott képformátumok: JPG, JPEG, PNG, GIF</span><br>
    <span style="font-size: 20px; color: red;">' . $_SESSION["profilepic_upload_error"] . '</span>
    </div>
    <div id="customizepage_box">
    <h2 id="customizepage_title">Oldal testreszabása</h2><br>
    <div id="customizepage_fonttype">
        <span id="customizepage_fonttype_title">Betűtípus:</span>
        <select id="customizepage_fonttype_choose" name="customizepage_fonttype_choose">
            <option value="Arial">Arial</option>
            <option value="Times New Roman">Times New Roman</option>
            <option value="Verdana">Verdana</option>
            <option value="Tahoma">Tahoma</option>
            <option value="Georgia">Georgia</option>
            <option value="Courier New">Courier New</option>
            <option value="Brush Script MT">Brush Script MT</option>
            <option value="Trebuchet MS">Trebuchet MS</option>
            <option value="Garamond">Garamond</option>
        </select>
    </div>
    <br><br>
    <div id="customizepage_backgroundcolor">
        <span id="customizepage_backgroundcolor_title">Háttérszín:</span>
        <input type="color" id="customizepage_backgroundcolor_choose" name="customizepage_backgroundcolor_choose" value="#e3e1e1"/>
    </div>
    <br><br>
    <div id="customizepage_videobordercolor">
        <span id="customizepage_videobordercolor_title">Videó keret szín:</span>
        <input type="color" id="customizepage_videobordercolor_choose1" name="customizepage_videobordercolor_choose1" value="#be0606"/>
        <input type="color" id="customizepage_videobordercolor_choose2" name="customizepage_videobordercolor_choose2" value="#7a0202"/>
    </div>
    <br><br><br>
    <div id="continueregistration">
        <input type="submit" id="continueregistration_button" name="continueregistration_button" value="Tovább"/>
    </div>
    </form>
</body>
<script>
        const pictureInput = document.getElementById("profilepicupload_attach");
        const previewImage = document.getElementById("profilepicupload_preview");

        pictureInput.addEventListener("change", function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.style.display = "block";
                    profilepicupload_uploadicon.style.display = "none";
                    profilepicupload_uploadtext.style.display = "none";
                    profilepicupload_preview.style.width = "230";
                    profilepicupload_preview.style.height = "auto";
                    profilepicupload_customattach.style.padding = "0";
                    profilepicupload_customattach.style.height = "230";
                };
                reader.readAsDataURL(file);
            }
        });
        customizepage_fonttype_choose.addEventListener("change", function(event) {
           const fonttype_selected = document.getElementById("customizepage_fonttype_choose");
           const fonttype_selected_value = fonttype_selected.value;
           customizepage_fonttype_title.style.fontFamily = fonttype_selected_value;
        });
        var videobordercolor1_selected = "#BE0606";
        var videobordercolor2_selected = "#7a0202";
        customizepage_videobordercolor_choose1.addEventListener("change", function(event) {
            videobordercolor1_selected = document.getElementById("customizepage_videobordercolor_choose1").value;
            customizepage_videobordercolor_title.style.borderImage = "linear-gradient(to bottom,"+videobordercolor1_selected+","+videobordercolor2_selected+") 1";
        });
        customizepage_videobordercolor_choose2.addEventListener("change", function(event) {
            videobordercolor2_selected = document.getElementById("customizepage_videobordercolor_choose2").value;
            customizepage_videobordercolor_title.style.borderImage = "linear-gradient(to bottom,"+videobordercolor1_selected+","+videobordercolor2_selected+") 1";
        });
    </script>
</html>
        ';
    } else {
    header("Location: /register");
    }
}

if(isset($_POST["continueregistration_button"])){
    $_SESSION["registerinprogress"] = true;
    $customization_fonttype = $_POST["customizepage_fonttype_choose"];
    $customization_backgroundcolor = $_POST["customizepage_backgroundcolor_choose"];
    $customization_videobordercolor1 = $_POST["customizepage_videobordercolor_choose1"];
    $customization_videobordercolor2 = $_POST["customizepage_videobordercolor_choose2"];

    if(!empty($_FILES['profilepicupload_attach']['tmp_name'])) {
    $profilepic_upload_dir = "../pictures/";
    $profilepic_upload_targetfile = $profilepic_upload_dir . basename($_FILES["profilepicupload_attach"]["name"]);
    $profilepic_upload_uploadOk = 1;
    $profilepic_upload_imagefiletype = strtolower(pathinfo($profilepic_upload_targetfile,PATHINFO_EXTENSION));

    $check = getimagesize($_FILES["profilepicupload_attach"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $profilepic_upload_uploadOk = 1;
    } else {
        $_SESSION["profilepic_upload_error"] = "A fajl nem egy kep!";
        $profilepic_upload_uploadOk = 0;
    }

    if (file_exists($profilepic_upload_targetfile)) {
        $_SESSION["profilepic_upload_error"] = "A feltoltott fajl mar letezik a szerveren! Kerjuk nevezze at fajljat feltoltes elott!";
        $profilepic_upload_uploadOk = 0;
    }

    if ($_FILES["profilepicupload_attach"]["size"] > 3000000) {
        $_SESSION["profilepic_upload_error"] = "A feltoltott fajl tul nagy! Kerjuk valassz kisebb kepet!";
        $profilepic_upload_uploadOk = 0;
    }

    if($profilepic_upload_imagefiletype != "jpg" && $profilepic_upload_imagefiletype != "png" && $profilepic_upload_imagefiletype != "jpeg"
    && $profilepic_upload_imagefiletype != "gif" ) {
        $_SESSION["profilepic_upload_error"] = "Csak JPG, JPEG, PNG, GIF fajltipusok elfogadottak!";
        $profilepic_upload_uploadOk = 0;
    }

    if ($profilepic_upload_uploadOk == 0) {
        header("Location: register.php?customize_profile");
        exit;
    } else {
        if (move_uploaded_file($_FILES["profilepicupload_attach"]["tmp_name"], $profilepic_upload_targetfile)) {
            echo "The file ". htmlspecialchars( basename( $_FILES["profilepicupload_attach"]["name"])). " has been uploaded.";
            $conn2 = mysqli_connect($servername, $db_username, $db_password, $dbname);
            if (!$conn2) {
                die("Connection failed: " . mysqli_connect_error());
            }
            $profilepicupload_sqlpic = "UPDATE users SET profilepic = '/pictures/" . $_FILES["profilepicupload_attach"]["name"] . "' WHERE username = '" . $_SESSION["usertoregister"] . "';";
            if (mysqli_query($conn2, $profilepicupload_sqlpic)) {
                $randomgenerate_style_code = bin2hex(random_bytes(10 / 2));
                mysqli_query($conn2, "INSERT INTO customization(style_code, fontfamilyType, backgroundColor, videoborderColor) VALUES ('" . $randomgenerate_style_code . "', '" . $customization_fonttype . "', '" . $customization_backgroundcolor . "', '" . $customization_videobordercolor1 . ";" . $customization_videobordercolor2 . "')");
                mysqli_query($conn2, "UPDATE users SET style_code = '" . $randomgenerate_style_code . "' WHERE username = '" . $_SESSION["usertoregister"] . "';");
                header("Location: register.php?choose_plan");
            } else {
                echo "Error updating record: " . mysqli_error($conn2);
            }
            mysqli_close($conn2);
            unset($_SESSION["profilepic_upload_error"]);
            header("Location: register.php?choose_plan");
        } else {
            $_SESSION["profilepic_upload_error"] = "Hiba tortent a fajl feltoltes soran. Probald ujra!";
        }
    }
    } else {
        $conn2 = mysqli_connect($servername, $db_username, $db_password, $dbname);
        if (!$conn2) {
            die("Connection failed: " . mysqli_connect_error());
        }
        $profilepicupload_sqldefaultpic = "UPDATE users SET profilepic = '/pageelements/userprofile_default.png' WHERE username = '" . $_SESSION["usertoregister"] . "';";
        if (mysqli_query($conn2, $profilepicupload_sqldefaultpic)) {
            $randomgenerate_style_code = bin2hex(random_bytes(10 / 2));
            mysqli_query($conn2, "INSERT INTO customization(style_code, fontfamilyType, backgroundColor, videoborderColor) VALUES ('" . $randomgenerate_style_code . "', '" . $customization_fonttype . "', '" . $customization_backgroundcolor . "', '" . $customization_videobordercolor1 . ";" . $customization_videobordercolor2 . "')");
            mysqli_query($conn2, "UPDATE users SET style_code = '" . $randomgenerate_style_code . "' WHERE username = '" . $_SESSION["usertoregister"] . "';");
            unset($_SESSION["profilepic_upload_error"]);
            header("Location: register.php?choose_plan");
            exit;
        } else {
            echo "Error updating record: " . mysqli_error($conn2);
        }
        mysqli_close($conn2);
    }
    /*$conn3 = mysqli_connect($servername, $db_username, $db_password, $dbname);
    if (!$conn3) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $profilecustomization_uploadstyle = "EXEC uploadProfileCustomization @stylecodeIN = '" . $randomStyleCodeGenerated . "', @fontfamilyIN = '" . $_POST['customizepage_fonttype_choose'] . "', @backgroundColorIN = '" . $_POST['customizepage_backgroundcolor_choose'] . "', @videoborderColorIN = '" . $_POST['customizepage_videobordercolor_choose1'] . ";" . $_POST['customizepage_videobordercolor_choose2'] . "';";
    if (mysqli_query($conn3, $profilecustomization_uploadstyle)) {
    } else {
        echo "Error updating record: " . mysqli_error($conn3);
    }
    mysqli_close($conn3);*/
}


// Here will be the code for the plan choosing page, where you can decide whether you want free or paid account
if (strpos($_SERVER['REQUEST_URI'], "?choose_plan") !== false){
    if($_SESSION["registerinprogress"] == true){
    $_SESSION["registerinprogress"] = false;
    echo '<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Csomag kiválasztása - VidFlow regisztráció</title>
    <link rel="stylesheet" href="registerstyle2.css?ver=1"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Inder&amp;display=swap" rel="stylesheet">
    <link rel="icon" href="/webicon.ico">
</head>
<body>
    <div id="laststeptitle"><h2>Egy utolsó lépés van már csak hátra, ' . $_SESSION['usertoregister'] . '</h2><h3 style="color: #fa675c;">Válassz csomagot!</h3></div>
    <div id="plan1">
        <form action="register.php" method="post">
        <h3>"Free" csomag</h3>
        <p>Smaller advertisements</p>
        <p>Limited video resolution(max. 1440p)</p>
        <p>Limited audio quality(max. 128Kbit/s)</p>
        <p>Limited customization options</p>
        <br><input type="submit" id="freeplanchoose" name="freeplanchoose" value="Kiválasztom"/>
        </form>
    </div>
    <div id="plan2">
        <form action="register.php" method="post">
        <h3>"Donation" csomag</h3>
	<h4>300 HUF / month</h4>
        <p>Closeable advertisements</p>
        <p>High video resolution support(up to 4K)</p>
        <p>Better audio quality(up to 256Kbit/s)</p>
        <p>More customization options</p>
        <p>Exclusive subscriber badge</p>
        <input type="submit" id="donateplanchoose" name="donateplanchoose" value="Kiválasztom"/>
        </form>
    </div>
</body>
</html>';
} else {
    header("Location: /register");
}
}


if(isset($_POST["freeplanchoose"])){
    $_SESSION["registerinprogress"] = true;
    $conn4 = mysqli_connect($servername, $db_username, $db_password, $dbname);
    if (!$conn4) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $addplansql = "UPDATE users SET plan = '1' WHERE username = '" . $_SESSION["usertoregister"] . "';";
    if (mysqli_query($conn4, $addplansql)) {
        header("Location: register.php?successful_registration");
    } else {
        echo "Error updating record: " . mysqli_error($conn4);
    }
    mysqli_close($conn4);
} else if(isset($_POST["donateplanchoose"])){
    $_SESSION["registerinprogress"] = true;
    $conn4 = mysqli_connect($servername, $db_username, $db_password, $dbname);
    if (!$conn4) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $addplansql = "UPDATE users SET plan = '2' WHERE username = '" . $_SESSION["usertoregister"] . "';";
    if (mysqli_query($conn4, $addplansql)) {
        header("Location: register.php?successful_registration");
    } else {
        echo "Error updating record: " . mysqli_error($conn4);
    }
    mysqli_close($conn4);
}

if (strpos($_SERVER['REQUEST_URI'], "?successful_registration") !== false){
    if($_SESSION["registerinprogress"] == true){
    $_SESSION["registerinprogress"] = false;
    echo '<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sikeres regisztráció - VidFlow regisztráció</title>
    <link rel="stylesheet" href="registerstyle2.css?ver=1"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Inder&amp;display=swap" rel="stylesheet">
    <link rel="icon" href="/vidflow/webicon.ico">
</head>
<body>
<div id="welcomemessage">
<img src="/pageelements/green_tick.png" alt="Success" id="successicon"/>
<h2>Üdvözlünk a VidFlow tagjai között, ' . $_SESSION["usertoregister"] . '!</h2>
<h4>Az email cím jóváhagyását követően bejelentkezhetsz fiókodba!</h4>
<br>
<a id="gotologin" href="/login">Ugrás a bejelentkezéshez</a><br><br><br></div>
</body>
</html>';
unset($_SESSION["usertoregister"]);
unset($_SESSION["rightCaptchaNumber"]);
} else {
    header("Location: /register");
}
}
} else {
    die("Sorry, but registration is not allowed from your country or possibly illegal activities detected!");
}
?>
