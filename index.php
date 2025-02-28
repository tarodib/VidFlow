<?php
session_start();
if(!isset($_SESSION["username_in"])){
    header("Location: login");
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width">
<title>VidFlow - Főoldal</title>
<link rel="icon" href="webicon.ico"/>
<link rel="stylesheet" href="style.css?v=1.3"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inder&family=Itim&display=swap" rel="stylesheet">
</head>
<body>
<div class="header">
<img src="pageelements/menu_icon.png" id="menuicon" class="headerelements"/>
<img src="vidflow_official_logo.png" id="officiallogo" class="headerelements" onclick="goHomePage()">
<!--<h4>Üdvözlünk, <?php echo($_SESSION['username_in']); ?></h4>
<form method="POST">
<input id="logout" name="logout" type="submit" value="Kilépés"/>
</form>
<br>-->
<div id="searchpart">
<form method="POST">
<input type="text" name="searchbar" id="searchbar" placeholder="Itt tudsz keresni"/>
<button type="submit" name="search" id="search">Keresés</button>
</form>
</div>
<div id="usercontrol" class="headerelements">
<img src="<?php echo($_SESSION['profilepic_in']); ?>" id="userprofilepic" class="headerelements"/>
<b id="profileusername" class="headerelements"><?php echo($_SESSION['username_in']); ?>&nbsp;
<?php
if(isset($_SESSION['userplan_status'])){
    echo($_SESSION['userplan_status']);
}
?>
</b>
<div class="headerelements">
<form method="POST">
<button id="logout" name="logout" type="submit" onmouseenter="switchLogoutRed()" onmouseleave="switchLogoutDefault()"><img src="pageelements/logout.png" id="logouticon" /></button>
</form>
</div>
</div>
</div>
<!-- Download test code -->
<div class="download-section">
    <h2>Download Video</h2>
    <form id="downloadForm">
        <input type="text" id="videoUrl" placeholder="Enter video URL" required />
        <button type="submit">Download</button>
    </form>
    <div id="downloadStatus"></div>
</div>
</body>
<script src="script.js"></script>
<script>
/*Test code*/
document.getElementById("downloadForm").addEventListener("submit", async function(event) {
    event.preventDefault();

    const videoUrl = document.getElementById("videoUrl").value;
    const statusDiv = document.getElementById("downloadStatus");

    if (!videoUrl) {
        statusDiv.textContent = "Please enter a video URL.";
        return;
    }

    statusDiv.textContent = "Downloading...";

    try {
        const response = await fetch("download.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ url: videoUrl })
        });

        const result = await response.json();

        if (result.success) {
            statusDiv.innerHTML = `
                Download complete! <a href="downloads/${result.file}" download>Click here to download</a>
            `;
        } else {
            statusDiv.textContent = `Error: ${result.error}`;
        }
    } catch (error) {
        statusDiv.textContent = `Failed to download video: ${error.message}`;
    }
/*end test code*/
document.body.style.backgroundColor = "<?php echo htmlspecialchars($_SESSION['bkgColor']); ?>";
document.body.style.fontFamily = "<?php echo htmlspecialchars($_SESSION['fontFamily']); ?>";
searchbar.style.fontFamily = "<?php echo htmlspecialchars($_SESSION['fontFamily']); ?>";
profileusername.style.fontFamily = "<?php echo htmlspecialchars($_SESSION['fontFamily']); ?>";
</script>
</html>

<?php
if(isset($_POST["logout"])){
    session_destroy();
    header("Location: login");
}
?>
