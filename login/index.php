<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="loginstyle.css"/>
    <title>Bejelentkezés - VidFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jersey+15&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inder&display=swap" rel="stylesheet">
    <link rel="icon" href="/vidflow/webicon.ico">
</head>
<body>
    <!--OFFICIAL LOGO OF VIDFLOW-->
    <img src="/vidflow/vidflow_official_logo.png" alt="VIDFLOW" class="logo">
    
    <!--LOGIN FORM-->
    <div class="logindiv">
        <h2 id="login_title" style="font-family: 'Josefin Sans';">BEJELENTKEZÉS</h2><br><br>
        <label for="field" class="label">Felhasználónév vagy E-mail cím:</label><br>
        <input type="text" class="field">
        <br><br>
        <label for="field" class="label">Jelszó:</label><br>
        <input type="password"  class="field">
        <br><br>
        <input type="submit" class="login" value="Belépés">
        <br><br><br>
        <a id="forgetpass" href="/vidflow/forgetpassword.php">Elfelejtette jelszavát?</a>
        <a id="noaccount" href="/vidflow/register">Nincs még fiókja?</a>
        <br><br><br>
        <!--THIRD PARTY LOGINS-->
        <button id="barnatechlogin" onclick="openBarnatechLogin()" style="display: inline-flex; align-items: center; padding: 5px; border: none; background-color: #f0f0f0; cursor: pointer;">
            <img src="https://barnatech.hu/barnatech_appicon.png" width="30" height="auto" style="margin-right: 5px; border-radius: 5px;" />
            Belépés BarnaTech.hu fiókkal
        </button>
        <br><br>
        <button id="googlelogin" onclick="openGoogleLogin()" style="display: inline-flex; align-items: center; padding: 5px; border: none; background-color: #f0f0f0; cursor: pointer;">
            <img src="google_logo.webp" width="30" height="auto" style="margin-right: 5px; border-radius: 5px;" />
            Belépés Google fiókkal
        </button>
    </div>
    <a id="registerbutton" onclick="location.href='/vidflow/register'">Regisztráció</button>
</body>
</html>