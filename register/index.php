<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.1">
    <link rel="stylesheet" href="registerstyle.css?ver=1"/>
    <title>Regisztráció - VidFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@100;300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jersey+15&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inder&display=swap" rel="stylesheet">
    <link rel="icon" href="/webicon.ico">
</head>
<body>
<?php
session_start();
?>
    <img src="../vidflow_official_logo.png" alt="" class="logo">
    <form action="register.php" method="post">
    <div class="registration">
        <h2 class="registration_title" style="font-family: 'Josefin Sans';">REGISZTRÁCIÓ</h2><br>
        <label for="name">Név:</label><br>
        <input type="text" class="field" id="name" name="name" required/><br><br>
        <label for="email">E-Mail:</label><br>
        <input type="email" class="field" id="email" name="email" required><br><br>
        <label for="username">Felhasználónév:</label><br>
        <input type="text" class="field" id="username" name="username" required><br><br>
        <label for="password">Jelszó:</label><br>
        <input type="password" class="field" id="password" name="password" required><br><br>
        <label for="passwordconfirm">Jelszó megerősítése:</label><br>
        <input type="password" class="field" id="passwordconfirm" name="passwordconfirm" required><br><br><div id="errors">
        <!--Hibak ellenorzese a regisztracio folyaman-->
        <?php
        if(isset($_SESSION["error"])){
            echo($_SESSION["error"]);
            unset($_SESSION["error"]);
            unset($_SESSION["usertoregister"]);
        }
        ?>
        </div><br>
        <input type="submit" class="register" name="register" value="Regisztrálás">
        <br><br><br>
        <span id="accquestion">Van már fiókja?&nbsp;</span><a href="../login" id="existingacc">Lépjen be most</a>
    </form>
    
    </div>
    <a id="loginbutton" onclick="location.href='../login'">Bejelentkezés</button>
</body>
</html>
