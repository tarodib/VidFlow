<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.1">
    <link rel="stylesheet" href="loginstyle.css?ver=1.1"/>
    <title>Bejelentkezés - VidFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jersey+15&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inder&display=swap" rel="stylesheet">
    <link rel="icon" href="../webicon.ico">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="./script.js?v=1.0"></script>

<?php
session_start();
?>

</head>
<body>
    <!--OFFICIAL LOGO OF VIDFLOW-->
    <img src="../vidflow_official_logo.png" alt="VIDFLOW" class="logo">
    <!--LOGIN FORM-->
    <div class="logindiv">
    <form action="login.php" method="POST">
        <h2 id="login_title" style="font-family: 'Josefin Sans';">BEJELENTKEZÉS</h2><br><br>
        <label for="field" class="label">Felhasználónév vagy E-mail cím:</label><br>
        <input type="text" class="field" name="email">
        <br><br>
        <label for="field" class="label">Jelszó:</label><br>
        <input type="password" class="field" name="password">
        <br><br>
        <input type="submit" class="login" value="Belépés">
        <br><br><br>
        <a id="forgetpass" href="forgotten.php">Elfelejtette jelszavát?</a>
        <a id="noaccount" href="../register/index.php">Nincs még fiókja?</a>
        <br><br><div id="errors">
        <!--Hibak ellenorzese a belepes folyaman-->
        <?php
        if(isset($_SESSION["error"])){
            echo($_SESSION["error"]);
            unset($_SESSION["error"]);
        }
        ?>
        </div><br><br>
        <!--THIRD PARTY LOGINS-->
        <!--<div id="loginusingbarnatech">
            <form action="login.php" method="post" name="Login_Form">
            <a id="closeloginwithbarnatech" href="" onclick="closeloginwithbarnatechjs()">&times;</a><br><br>
            <table width="400" border="0" align="center" cellpadding="5" cellspacing="1" class="Table">
            <tr>
            <b id="formlabel">Felhasználónév</b><br><input name="Username" type="text" class="Input" id="userNameBox" style="border: 2px solid orange; border-radius: 3px;width: 150px;" onblur="checkUsernameBox(this)"></td>
            </tr>
            <tr><br><br>
            <b id="formlabel">Jelszó</b><br><input name="Password" id="passBox" type="password" class="Input" style="border: 2px solid orange; border-radius: 3px; width: 150px;" onblur="checkPassBox(this)"></td>
            </tr>
            <br><input type="checkbox" onclick="showPassword()" id="showpassbutton"><label for="showpassbutton" style="font-family: Arial; font-size: 15px;">Jelszó mutatása</label><br><span id="emptyBoxerror"></span>
            <tr>
            <td><input name="Submit" type="submit" value="Belépés" class="Button3" style="background-color: #b0aeac; border: 3px solid black; color: #fcfcfc; margin-right: 150px;"></td>
      
            </tr>
            <tr>
            <td>
            <span style="margin-right: 150px;">Nincs fiókod? <a href="#registeruser" onclick="openregisterform()">Regisztráció</a></td></tr></span>
            </table>
            </form>
        </div>-->
        <div id="googlelogin" style="display: inline-flex; align-items: center; padding: 5px; border: none; background-color: #f0f0f0; cursor: pointer;">
            <img src="/pageelements/google_logo.webp" width="30" height="auto" style="margin-right: 5px; border-radius: 5px;" />
            Belépés Google fiókkal
        </div>

	<div id="g_id_onload" 
     data-client_id="170029422952-vklbgsc1bt4g4b0od4m1bs26h8hp3a72.apps.googleusercontent.com"
     data-callback="handleCredentialResponse">
</div>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
  // Define the callback function in the global scope
  function handleCredentialResponse(response) {
    console.log("Encoded JWT ID token: " + response.credential);

    // Optionally decode the JWT token to access user information
    const decodedToken = jwt_decode(response.credential); // Requires `jwt-decode` library
    console.log("Decoded Token:", decodedToken);
  }
</script>



    </form>
    </div>
    <a id="registerbutton" onclick="location.href='../register'">Regisztráció</button>
    
</body>
<script src="./script.js?v=1.0"></script>
</html>
