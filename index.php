<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$searchURI = $_SERVER["REQUEST_URI"];
if(!isset($_SESSION["username_in"])){
    header("Location: login");
}
if(isset($_POST["search"])){
    $searchbarValue = "?search=" . $_POST["searchbar"] . "";
    header("Location: ".$searchbarValue);
    exit;
}

if(isset($_POST["logout"])){
    session_destroy();
    header("Location: /login");
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VidFlow - Főoldal</title>
<link rel="icon" href="webicon.ico"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link id="styleelements" rel="stylesheet" href="style.css?v=3.7"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inder&family=Itim&display=swap" rel="stylesheet">
</head>
<body>
<div class="header" id="mainheader">
<img src="pageelements/menu_icon.png" id="menuicon" class="headerelements" onclick="hideShowBoxes()"/>
<img src="vidflow_official_logo.png" id="officiallogo" class="headerelements" onclick="goHomePage()">
<div id="searchpart">
<form method="POST">
<input type="text" name="searchbar" id="searchbar" placeholder="Itt tudsz keresni"/>
<button type="submit" name="search" id="search">Keresés</button>
</form>
</div>
<div id="usercontrol" class="headerelements">
<img src="<?php echo($_SESSION['profilepic_in']); ?>" id="userprofilepic" class="headerelements" onclick="openProfileUserMenu()"/>
<b id="profileusername" class="headerelements" onclick="openProfileUserMenu()"><?php echo($_SESSION['username_in']); ?>&nbsp;
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
<br>
<div id="uservideosboxes">
<div id="likedvideosbox">
    <div id="likedvideosbox_title">
        <h5>Kedvelt videók</h5>
    </div>
    <div id="likedvideoslist">
    </div>
</div>
<div id="playlistvideosbox">
    <div id="playlistvideosbox_title">
        <h5>Lejátszási listák</h5>
    </div>
    <div id="playlistvideoslist">
    </div>
</div>
</div>
<div id="uservideosboxes_mobile"></div>
<div id="likedvideostatus-modal"></div>
<div id="playlistvideosbox-modal"></div>
<div id="addtoplaylist-modal"></div>
<div id="settingsmenu-modal"></div>
<div class="container mt-4">
  <div class="row">
  <?php

    require 'vendor/autoload.php';

    use Dotenv\Dotenv;

    $dotenv = Dotenv::createImmutable(__DIR__ . '');
    $dotenv->load();
    function searchInProgress($searchURI){
        $splitURI = explode("?search=", $searchURI);
        $searchTerm = $splitURI[1];

    function fetchYouTubeResults($searchTerm, $resultsPerBatch = 5) {
        $searchUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&q=" . $searchTerm . "&maxResults={$resultsPerBatch}&key={$_ENV['YOUTUBE_API_KEY_2']}";
        $searchResponse = file_get_contents($searchUrl);

        if ($searchResponse === false) {
            return [];
        }
        $searchData = json_decode($searchResponse, true);
        $videoIds = [];
        foreach ($searchData['items'] as $item) {
            $videoIds[] = $item['id']['videoId'];
        }
        if (empty($videoIds)) {
            return [];
        }
        $videoDetailsUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=" . implode(',', $videoIds) . "&key={$_ENV['YOUTUBE_API_KEY_1']}";
        $detailsResponse = file_get_contents($videoDetailsUrl);
        if ($detailsResponse === false) {
            return [];
        }
        $detailsData = json_decode($detailsResponse, true);
        $videos = [];
        foreach ($detailsData['items'] as $item) {
            $durationIso8601 = $item['contentDetails']['duration'];
            $videos[] = [
                'id' => $item['id'],
                'title' => $item['snippet']['title'],
                'duration' => formatDuration($durationIso8601),
                'uploader' => $item['snippet']['channelTitle'],
                'thumbnail' => $item['snippet']['thumbnails']['medium']['url'],
                'uploader_url' => "https://www.youtube.com/channel/" . $item['snippet']['channelId'],
            ];
        }

        return $videos;
    }

    function formatDuration($duration) {
        try {
            $interval = new DateInterval($duration);
            return ($interval->h > 0 ? $interval->h . ':' : '') .
                   str_pad($interval->i, 2, '0', STR_PAD_LEFT) . ':' .
                   str_pad($interval->s, 2, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            return 'Ismeretlen';
        }
    }

    $resultsPerBatch = 10;
    $videos = fetchYouTubeResults($searchTerm, $resultsPerBatch);

    if (!empty($videos)) { ?>
        <div class="container mt-4">
            <?php foreach ($videos as $video): ?>
                <div class="row mb-3">
                    <div class="col-md-4"><a style="text-decoration: none; color: #7a0202;" href="/watch?id=<?= htmlspecialchars($video['id']) ?>">
                            <img class="rounded video-thumbnail" src="<?= htmlspecialchars($video['thumbnail']) ?>" alt="<?= htmlspecialchars($video['title']) ?>">
                    </a></div>
                    <div class="col-md-8"><a style="text-decoration: none; color: #7a0202;" href="/watch?id=<?= htmlspecialchars($video['id']) ?>">
                        <h5 class="video-name"><?= htmlspecialchars($video['title']) ?></h5>
                        <p class="text-muted mb-1 video-uploader-name">By <a href="<?= htmlspecialchars($video['uploader_url']) ?>" target="_blank" style="text-decoration: none;"><?= htmlspecialchars($video['uploader']) ?></a></p>
                        <p class="text-muted">Időtartam: <?= htmlspecialchars($video['duration']) ?></p></a>
                        <img class="video-add-to-playlist2" src="/pageelements/add-to-playlist.png" alt="Lejátszási lista" onclick='openPlayListModal(&quot;<?= $_SESSION['username_in'] ?>&quot;,&quot;<?= htmlspecialchars($video['id']) ?>&quot;)'/>
                        <img class="video-add-to-like2" src="/pageelements/video_like_button.png" alt="Like" onclick='addVideoToLiked(&quot;<?= $_SESSION['username_in'] ?>&quot;,&quot;<?= htmlspecialchars($video['id']) ?>&quot;,&quot;<?= htmlspecialchars($video['title']) ?>&quot;,&quot;<?= htmlspecialchars($video['duration']) ?>&quot;,&quot;<?= htmlspecialchars($video['uploader']) ?>&quot;,&quot;<?= htmlspecialchars($video['id']) ?>&quot;)'/>
                    </div>
                </div>
                <hr>
            <?php endforeach; ?>
        </div>
    <?php } else {
        echo "<p>Nincsenek találatok a keresésre!</p>";
    }
}

    
    function convertYouTubeDuration($duration)
    {
        try {
            $interval = new DateInterval($duration);
            return ($interval->h > 0 ? $interval->h . ':' : '') .
                   str_pad($interval->i, 2, '0', STR_PAD_LEFT) . ':' .
                   str_pad($interval->s, 2, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            return 'Ismeretlen';
        }
    }
    
    if (str_contains($searchURI, "?search=")) {
        searchInProgress($searchURI);
    } else {
    $allVideoData = [];
    $trendingVideosUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&chart=mostPopular&regionCode=HU&key={$_ENV['YOUTUBE_API_KEY_1']}&maxResults=20";
    $getTrendingVideos = file_get_contents($trendingVideosUrl);
    if ($getTrendingVideos === false) {
        die("Hiba történt a YouTube API meghívásakor!");
    }
    $trendingVideos = json_decode($getTrendingVideos, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Hiba történt a JSON dekódolásakor: ' . json_last_error_msg());
    }
    foreach ($trendingVideos['items'] as $video) {
        $videoId = $video['id'];
        $snippet = $video['snippet'];
        $contentDetails = $video['contentDetails'];

        $channelId = $snippet['channelId'];
        $channelDetailsUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet&id=$channelId&key={$_ENV['YOUTUBE_API_KEY_1']}";
        $channelResponse = file_get_contents($channelDetailsUrl);

        if ($channelResponse === false) {
            die('Hiba történt a csatorna adatok lekérésekor.');
        }

        $channelData = json_decode($channelResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            die('Hiba történt a csatorna JSON dekódolásakor: ' . json_last_error_msg());
        }

        $uploaderAvatar = $channelData['items'][0]['snippet']['thumbnails']['default']['url'] ?? '';

        $allVideoData[] = [
            'id' => $videoId,
            'title' => $snippet['title'] ?? 'Ismeretlen cím',
            'uploader' => $snippet['channelTitle'] ?? 'Ismeretlen feltöltő',
            'thumbnail' => $snippet['thumbnails']['medium']['url'] ?? '',
            'length_minutes' => isset($contentDetails['duration'])
                ? convertYouTubeDuration($contentDetails['duration'])
                : 'Ismeretlen időtartam',
            'uploader_avatar' => $uploaderAvatar
        ];
    }

    foreach ($allVideoData as $videometadata): ?>
      <div class="col-md-3 col-sm-6 mb-4">
          <div class="card">
              <div class="ratio ratio-16x9"><a style="text-decoration: none; color: #7a0202;" href="/watch?id=<?= htmlspecialchars($videometadata['id']) ?>">
                <div class="thumbnail-container">
                  <img class="card-img-top video-thumbnail" src="<?= htmlspecialchars($videometadata['thumbnail']) ?>" alt="<?= htmlspecialchars($videometadata['title']) ?>">
                  <div class="video-duration"><?= htmlspecialchars($videometadata['length_minutes']) ?></div>
                </div>
                  </a>
              </div>
              <div class="card-body"><a style="text-decoration: none; color: #7a0202;" href="/watch?id=<?= htmlspecialchars($videometadata['id']) ?>">
                  <h6 class="video-name"><?= htmlspecialchars($videometadata['title']) ?></h6>
                    <div class="video-details-container">
                      <img class="video-uploader-pic" src="<?= htmlspecialchars($videometadata['uploader_avatar']) ?>" alt="<?= htmlspecialchars($videometadata['uploader']) ?>">
                      <span class="video-uploader-name"><?= htmlspecialchars($videometadata['uploader']) ?></span>
                      </a>
                      <div class="video-options">
                      <img class="video-options-button" src="/pageelements/options_icon.png" alt="Options">
                        <div class="video-options-content">
                            <div class="video-add-to-like-menu" onclick='addVideoToLiked(&quot;<?= $_SESSION['username_in'] ?>&quot;,&quot;<?= htmlspecialchars($videometadata['id']) ?>&quot;,&quot;<?= htmlspecialchars($videometadata['title']) ?>&quot;,&quot;<?= htmlspecialchars($videometadata['length_minutes']) ?>&quot;,&quot;<?= htmlspecialchars($videometadata['uploader']) ?>&quot;,&quot;<?= htmlspecialchars($videometadata['uploader_avatar']) ?>&quot;)'>
                                <img class="video-add-to-like" src="/pageelements/video_like_button.png" alt="Like"/>
                                <span class="video-add-to-like-text">Kedvelés</span>
                            </div>
                        <div class="menu-divider"></div>
                        <div class="video-add-to-playlist-menu" onclick='openPlayListModal(&quot;<?= $_SESSION['username_in'] ?>&quot;,&quot;<?= htmlspecialchars($videometadata['id']) ?>&quot;)'>
                            <img class="video-add-to-playlist" src="/pageelements/add-to-playlist.png" alt="Lejátszási lista" />
                            <span class="video-add-to-playlist-text">Lejátszási listához adás</span>
                        </div>
                        <div class="menu-divider"></div>
                        <div class="video-copy-link">
                            <a onclick='copyURLShare(&quot;<?= htmlspecialchars($videometadata['id']) ?>&quot;)'>Link másolása</a>
                        </div>
                        </div>
                    </div>
                    </div>
              </div>
          </div>
      </div>
    <?php endforeach;
    }
    ?>
  </div>
</div>
</body>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<script>
document.body.style.backgroundColor = "<?php echo htmlspecialchars($_SESSION['bkgColor']); ?>";
document.body.style.fontFamily = "<?php echo htmlspecialchars($_SESSION['fontFamily']); ?>";
searchbar.style.fontFamily = "<?php echo htmlspecialchars($_SESSION['fontFamily']); ?>";
profileusername.style.fontFamily = "<?php echo htmlspecialchars($_SESSION['fontFamily']); ?>";
var videobordercolor1 = "<?php echo htmlspecialchars($_SESSION['firstvideoborderColor']); ?>";
var videobordercolor2 = "<?php echo htmlspecialchars($_SESSION['secondvideoborderColor']); ?>";
var menuicondisplay = 1;
function hideShowBoxes(){
if(menuicondisplay != 1){
    document.getElementById("likedvideosbox").style.display = "none";
    document.getElementById("playlistvideosbox").style.display = "none";
    menuicondisplay = 1;
} else {
    document.getElementById("likedvideosbox").style.display = "block";
    document.getElementById("playlistvideosbox").style.display = "block";
    menuicondisplay = 0;
}
}
var addtoplaylistboxmodal;
const cards = document.querySelectorAll('.card');
cards.forEach(card => {
  card.style.backgroundColor = "<?php echo htmlspecialchars($_SESSION['bkgColor']); ?>";
});
const videothumbnails = document.querySelectorAll('.video-thumbnail');
const videolengthboxes = document.querySelectorAll('.video-duration');
videothumbnails.forEach(videothumbnail => {
    videothumbnail.style.background = "linear-gradient(to bottom,"+videobordercolor1+","+videobordercolor2+") border-box, white padding-box";
});
videolengthboxes.forEach(videolengthbox => {
    videolengthbox.style.background = "linear-gradient(to bottom,"+videobordercolor1+","+videobordercolor2+") border-box, white padding-box";
});
document.getElementById("likedvideosbox").style.borderImage = "linear-gradient(to bottom, "+videobordercolor1+" 0%, "+videobordercolor2+" 100%) 1";
document.getElementById("playlistvideosbox").style.borderImage = "linear-gradient(to bottom, "+videobordercolor1+" 0%, "+videobordercolor2+" 100%) 1";
function loadAllLikedVideos(){
    fetch('<?= (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://${_SERVER['HTTP_HOST']}" ?>/services/liked_videos_get.php?user=<?= htmlspecialchars($_SESSION["username_in"]) ?>')
        .then(response => response.json())
        .then(data => {
            const likedvideoscontainer = document.querySelector('#likedvideoslist');
            likedvideoscontainer.innerHTML = "";

            data.forEach(video => {
                const videoCard = `
                    <div class="card liked-card">
              <div class="ratio ratio-16x9"><a style="text-decoration: none; color: #7a0202;" href="/watch?id=${video[0]}">
                <div class="thumbnail-container">
                  <img class="card-img-top video-thumbnail" src="https://img.youtube.com/vi/${video[0]}/sddefault.jpg" alt="${video[1]}">
                  <div class="video-duration">${video[2]}</div>
                </div>
                  </a>
              </div>
              <div class="card-body"><a style="text-decoration: none; color: #7a0202;" href="/watch?id=${video[0]}">
                  <h6 class="video-name">${decodeURIComponent(video[1])}</h6>
                  <p class="video-details">
                      <span class="video-uploader-name" style="margin-left: 0px;">${video[3]}</span></a>
                      <img class="remove-liked-video" src="/pageelements/trash_icon.png" alt="Trash" onclick="removeLikedVideoFromList('<?= $_SESSION['username_in'] ?>','${video[0]}','${video[1]}','${video[2]}','${video[3]}','${video[4]}')"/>
                  </p>
              </div>
          </div>`;
                likedvideoscontainer.innerHTML += videoCard;
            });
            const cards = document.querySelectorAll('.liked-card');
            cards.forEach(card => {
                card.style.backgroundColor = "#E1B995";
            });

            const videothumbnails = document.querySelectorAll('.video-thumbnail');
            videothumbnails.forEach(videothumbnail => {
                videothumbnail.style.background = "linear-gradient(to bottom,"+videobordercolor1+","+videobordercolor2+") border-box, white padding-box";
            });
            const videolengthboxes = document.querySelectorAll('.video-duration');
            videolengthboxes.forEach(videolengthbox => {
                videolengthbox.style.background = "linear-gradient(to bottom,"+videobordercolor1+","+videobordercolor2+") border-box, white padding-box";
            });
        })
        .catch(error => console.error('Error fetching videos:', error));
}
loadAllLikedVideos();
document.addEventListener('click', function(event) {
    if (event.target.matches('.video-options-button')) {
        const dropdownContent = event.target.nextElementSibling;
        dropdownContent.classList.toggle('video-options-show');
    } else {
        const dropdowns = document.getElementsByClassName('video-options-content');
        for (let i = 0; i < dropdowns.length; i++) {
            const openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('video-options-show')) {
                openDropdown.classList.remove('video-options-show');
            }
        }
    }
});
function openProfileUserMenu(){
    document.getElementById("settingsmenu-modal").innerHTML = `<div class="modal fade" id="profilemenu_modal_box" tabindex="-1" role="dialog" aria-labelledby="profilemenu_modal_box" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="modal-title profilemenu_modal_options" id="profilemenu_modal_page1" onclick="openUserMenuPage1()">Adatok</button>
        <button type="button" class="modal-title profilemenu_modal_options" id="profilemenu_modal_page2" onclick="openUserMenuPage2()">Testreszabás</button>
        <button type="button" class="modal-title profilemenu_modal_options" id="profilemenu_modal_page3" onclick="openUserMenuPage3()">Lejátszási előzmények</button>
        <?php
        if(isset($_SESSION["userplan_status"])){
        ?>
        <br><br><button type="button" class="modal-title profilemenu_modal_options" id="profilemenu_modal_page4" onclick="openUserMenuPage4()">Felhasználók kezelése</button>
        <?php
        }
        ?>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="profilemenu_modal_content">
        <h4>Válassz egy menüpontot!</h4>
      </div>
    </div>
  </div>
</div>`;
const settingsmodal = new bootstrap.Modal(document.getElementById('profilemenu_modal_box'));
settingsmodal.show();
openUserMenuPage1();
}

function openUserMenuPage1(){
    fetch('services/userdata_handling.php')
        .then(response => response.json())
        .then(data => {
        document.getElementById("profilemenu_modal_content").innerHTML = `
        <h5>Adatok áttekintése, megváltoztatása</h5><br>
        <form>
        <label for="userchangeusernameinput">Felhasználónév:</label>&nbsp;
        <input type="text" name="userchangeusernameinput" id="userchangeusernameinput" disabled/>
        <br><br>
        <label for="userchangenameinput">Név:</label>&nbsp;
        <input type="text" name="userchangenameinput" id="userchangenameinput" disabled/>
        <br><br>
        <label for="userchangeemailinput">E-Mail:</label>&nbsp;
        <input type="email" name="userchangeemailinput" id="userchangeemailinput" disabled/>
        <br><br><b style="color: red;" id="userchangedata_status"></b>
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('userchangeusernameinput').removeAttribute('disabled'); document.getElementById('userchangenameinput').removeAttribute('disabled'); document.getElementById('userchangeemailinput').removeAttribute('disabled'); this.style.display = 'none'; userchangedata_savebutton.style.display = 'block';">Szerkesztés</button>
        <button type="button" class="btn btn-primary" name="userchangedata_savebutton" id="userchangedata_savebutton" style="display: none;" onclick="saveUserDataChange(document.getElementById('userchangeusernameinput').value,document.getElementById('userchangenameinput').value,document.getElementById('userchangeemailinput').value)">Mentés</button>
        </form>
        <br><br>
        <h5>Jelszó megváltoztatása</h5><br>
        <form>
        <label for="userchangepasswordinput">Új jelszó:</label>
        <input type="password" name="userchangepasswordinput" id="userchangepasswordinput">
        <br><br>
        <label for="userchangepasswordagaininput">Új jelszó mégegyszer:</label>
        <input type="password" name="userchangepasswordagaininput" id="userchangepasswordagaininput">
        <br><br><b id="userchangepassword_status"></b>
        <button type="button" class="btn btn-primary" name="userchangepassword_savebutton" id="userchangepassword_savebutton" onclick="saveUserPasswordChange(document.getElementById('userchangepasswordinput').value,document.getElementById('userchangepasswordagaininput').value)">Mentés</button>
        `;
        document.getElementById("userchangeusernameinput").value = data[0];
        document.getElementById("userchangenameinput").value = data[1];
        document.getElementById("userchangeemailinput").value = data[2];
    });
}

function saveUserDataChange(usernameofuserchange, nameofuserchange, emailofuserchange){
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "services/userdata_handling.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("newusername="+encodeURIComponent(usernameofuserchange)+"&newname="+encodeURIComponent(nameofuserchange)+"&newemail="+encodeURIComponent(emailofuserchange));
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            try {
                const userDataChangeResponse = JSON.parse(this.responseText);
                if(userDataChangeResponse.status === "success"){
                    document.getElementById("userchangedata_status").innerHTML = "";
                    alert(userDataChangeResponse.message);
                } else if(userDataChangeResponse.status === "usernamesuccess"){
                    document.getElementById("userchangedata_status").innerHTML = "";
                    alert(userDataChangeResponse.message);
                    window.location.href="/";
                } else {
                    document.getElementById("userchangedata_status").innerHTML = userDataChangeResponse.message+"<br><br>";
                }
            } catch (e) {
                console.error("Failed to parse server response:", e);
            }
        }
    };
}

function saveUserPasswordChange(passofpasswordchange, passagainofpasswordchange){
    if(passofpasswordchange == passagainofpasswordchange){
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "services/userdata_passwordchange.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("newpassword="+encodeURIComponent(passofpasswordchange));
        xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            try {
                const userDataChangeResponse = JSON.parse(this.responseText);
                if(userDataChangeResponse.status === "success"){
                    document.getElementById("userchangepassword_status").innerHTML = "";
                    alert(userDataChangeResponse.message);
                } else {
                    document.getElementById("userchangepassword_status").innerHTML = userDataChangeResponse.message+"<br><br>";
                }
            } catch (e) {
                console.error("Failed to parse server response:", e);
            }
            }
        };
    } else {
        document.getElementById("userchangepassword_status").innerHTML = "<img src='pageelements/warning_icon.png' width='30' height='auto'/>&nbsp;Nem egyeznek a jelszavak!<br><br>";
    }
}

function openUserMenuPage2() {
    document.getElementById("profilemenu_modal_content").innerHTML = `
    <form method="post" id="customization_form" enctype="multipart/form-data">
        <div id="profilepicupload_box">
            <h4 id="profilepicupload_title">Profilkép feltöltése</h4><br>
            <label id="profilepicupload_customattach" style="display: block; border: 1px dashed grey; padding: 10px; cursor: pointer; text-align: center; height: 230px; width: 230px; position: relative; overflow: hidden;">
                <img id="profilepicupload_preview" style="display: none; max-width: 100%; max-height: 100%; object-fit: contain;">
                <input type="file" name="profilepicupload_attach" id="profilepicupload_attach" accept="image/png, image/gif, image/jpeg" style="display: none;">
                <img src="pageelements/upload_icon-black.png" id="profilepicupload_uploadicon" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 50px;">
                <br><span id="profilepicupload_uploadtext" style="position: absolute; bottom: 10px; left: 0; right: 0;">Válassza ki a feltöltendő fájlt</span>
            </label>
            <span style="font-size: 12px; color: black;">Elfogadott képformátumok: JPG, JPEG, PNG, GIF</span><br>
        </div>
        <div id="customizepage_box">
            <h4 id="customizepage_title">Oldal testreszabása</h4><br>
            <div class="customization-row" id="customizepage_fonttype">
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
            <div class="customization-row" id="customizepage_backgroundcolor">
                <span id="customizepage_backgroundcolor_title">Háttérszín:</span>
                <input type="color" id="customizepage_backgroundcolor_choose" name="customizepage_backgroundcolor_choose" value="#e3e1e1"/>
            </div>
            <br>
            <div class="customization-row" id="customizepage_videobordercolor">
                <span id="customizepage_videobordercolor_title" style="border: 5px solid transparent; padding: 2px 5px;">Videó keret szín:</span>
                <input type="color" id="customizepage_videobordercolor_choose1" name="customizepage_videobordercolor_choose1" value="#be0606"/>
                <input type="color" id="customizepage_videobordercolor_choose2" name="customizepage_videobordercolor_choose2" value="#7a0202"/>
            </div>
            <br>
            <span id="customization_changes_status" style="display: block; margin-top: 10px; font-weight: bold;"></span>
            <div id="savecustomizationchanges">
                <input type="submit" class="btn btn-primary" id="savecustomizationchanges_button" name="savecustomizationchanges_button" value="Mentés"/>
                <input type="button" class="btn btn-danger" id="defaultcustomizationchanges_button" name="defaultcustomizationchanges_button" value="Alap visszaállítás"/>
            </div>
        </div>
    </form>
    `;
    const pictureInput = document.getElementById("profilepicupload_attach");
    const previewImage = document.getElementById("profilepicupload_preview");
    const uploadIcon = document.getElementById("profilepicupload_uploadicon");
    const uploadText = document.getElementById("profilepicupload_uploadtext");
    const uploadLabel = document.getElementById("profilepicupload_customattach");
    const fontSelect = document.getElementById("customizepage_fonttype_choose");
    const fontTitle = document.getElementById("customizepage_fonttype_title");
    const borderColor1Input = document.getElementById("customizepage_videobordercolor_choose1");
    const borderColor2Input = document.getElementById("customizepage_videobordercolor_choose2");
    const videoBorderTitle = document.getElementById("customizepage_videobordercolor_title");
    const customizationForm = document.getElementById("customization_form");
    const statusElement = document.getElementById("customization_changes_status");
    const resetButton = document.getElementById("defaultcustomizationchanges_button");

    if (pictureInput && previewImage && uploadIcon && uploadText && uploadLabel) {
        pictureInput.addEventListener("change", function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.style.display = "block";
                    uploadIcon.style.display = "none";
                    uploadText.style.display = "none";
                    previewImage.style.width = "100%";
                    previewImage.style.height = "100%";
                    uploadLabel.style.padding = "0";
                    uploadLabel.style.height = "230px";
                    uploadLabel.style.border = "none";
                };
                reader.readAsDataURL(file);
            } else {
                 previewImage.src = "";
                 previewImage.style.display = "none";
                 uploadIcon.style.display = "block";
                 uploadText.style.display = "block";
                 uploadLabel.style.padding = "10px";
                 uploadLabel.style.height = "230px";
                 uploadLabel.style.border = "1px dashed grey";
            }
        });
    }

    if (fontSelect && fontTitle) {
        fontSelect.addEventListener("change", function(event) {
            fontTitle.style.fontFamily = event.target.value;
        });
        fontTitle.style.fontFamily = fontSelect.value;
    }

    let videobordercolor1_selected = "#be0606";
    let videobordercolor2_selected = "#7a0202";

    function updateBorderImage() {
        if (videoBorderTitle) {
            videoBorderTitle.style.borderImage = `linear-gradient(to bottom, ${videobordercolor1_selected}, ${videobordercolor2_selected}) 1`;
        }
    }

     if (borderColor1Input && videoBorderTitle) {
        videobordercolor1_selected = borderColor1Input.value;
        borderColor1Input.addEventListener("change", function(event) {
            videobordercolor1_selected = event.target.value;
            updateBorderImage();
        });
     }

     if (borderColor2Input && videoBorderTitle) {
        videobordercolor2_selected = borderColor2Input.value;
        borderColor2Input.addEventListener("change", function(event) {
            videobordercolor2_selected = event.target.value;
            updateBorderImage();
        });
     }
     updateBorderImage();


    if (customizationForm && statusElement) {
        customizationForm.addEventListener('submit', function(event) {
            event.preventDefault();
            statusElement.textContent = 'Feltöltés folyamatban...';
            statusElement.style.color = 'blue';

            const customizationadatok = new FormData();
            if (pictureInput.files.length > 0) {
                customizationadatok.append('userProfilePic', pictureInput.files[0]);
            }
            if(fontSelect) customizationadatok.append('fontType', fontSelect.value);
            const bgColorInput = document.getElementById('customizepage_backgroundcolor_choose');
            if(bgColorInput) customizationadatok.append('bgColor', bgColorInput.value);
            if(borderColor1Input) customizationadatok.append('borderColor1', borderColor1Input.value);
            if(borderColor2Input) customizationadatok.append('borderColor2', borderColor2Input.value);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'services/customization_update.php', true);
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const customizationresponse = JSON.parse(xhr.responseText);
                        if (customizationresponse.success) {
                            statusElement.textContent = customizationresponse.message || 'Sikeres mentés!';
                            statusElement.style.color = 'green';
                            setTimeout(function() {
                                alert('Az oldalt újra kell tölteni a visszaállítás befejezéséhez!');
                                location.reload();
                            }, 500);

                            if (customizationresponse.newImageUrl) {
                                const newUserProfilePic = document.getElementById("userprofilepic");
                                if (newUserProfilePic) {
                                    newUserProfilePic.src = customizationresponse.newImageUrl;
                                }
                            }
                        } else {
                            statusElement.textContent = customizationresponse.message || 'Hiba történt a szerver oldalon.';
                            statusElement.style.color = 'red';
                        }
                    } catch (e) {
                        statusElement.textContent = 'Hiba a válasz feldolgozása közben.';
                        statusElement.style.color = 'red';
                        console.error("JSON parse error:", e);
                        console.error("Received response:", xhr.responseText);
                    }
                } else {
                    statusElement.textContent = `Hiba: ${xhr.status} ${xhr.statusText}`;
                    statusElement.style.color = 'red';
                }
            };
            xhr.onerror = function() {
                statusElement.textContent = 'Hálózati hiba történt.';
                statusElement.style.color = 'red';
            };
            xhr.send(customizationadatok);
        });
    }
    
    if (resetButton && statusElement) {
        resetButton.addEventListener('click', function() {
            statusElement.textContent = 'Visszaállítás folyamatban...';
            statusElement.style.color = 'blue';
            const resetFormData = new FormData();
            resetFormData.append('action', 'reset_defaults');
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'services/customization_update.php', true);
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            statusElement.textContent = response.message || 'Sikeres visszaállítás!';
                            statusElement.style.color = 'green';
                            setTimeout(function() {
                                alert('Az oldalt újra kell tölteni a visszaállítás befejezéséhez!');
                                location.reload();
                            }, 500);

                        } else {
                            statusElement.textContent = response.message || 'Hiba történt a visszaállítás során.';
                            statusElement.style.color = 'red';
                        }
                    } catch (e) {
                        statusElement.textContent = 'Hiba a szerver válaszának feldolgozása közben.';
                        statusElement.style.color = 'red';
                        console.error("JSON parse error (reset):", e);
                        console.error("Received response (reset):", xhr.responseText);
                    }
                }
            };

            xhr.onerror = function() {
                statusElement.textContent = 'Hálózati hiba történt a visszaállítás során.';
                statusElement.style.color = 'red';
            };

            xhr.send(resetFormData);
        });
    }
}


function openUserMenuPage3(){
    document.getElementById("profilemenu_modal_content").innerHTML = `
    <h5 style="text-align: center;">Megtekintési Előzmények</h5>
    <div id="history-list-container">
        <p>Előzmények betöltése...</p>
    </div>
    `;
    const historyContainer = document.getElementById('history-list-container');
    const phpScriptUrl = '/services/watchinghistory_get.php';
    fetch(phpScriptUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            historyContainer.innerHTML = '';
            if (data.error) {
                 historyContainer.innerHTML = `<p class="error-message">Hiba: ${data.error}</p>`;
                 return;
            }
            if (Array.isArray(data) && data.length > 0) {
                const list = document.createElement('ul');
                list.id = 'history-list';

                data.forEach(url => {
                    const listItem = document.createElement('li');
                    const link = document.createElement('a');
                    link.href = url;
                    link.textContent = url;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';

                    listItem.appendChild(link);
                    list.appendChild(listItem);
                });
                historyContainer.appendChild(list);
            } else {
                historyContainer.innerHTML = '<br><p style="color: orange;">Nincsenek megtekintési előzmények.</p>';
            }
        })
        .catch(error => {
            console.error('Fetch Hiba:', error);
            historyContainer.innerHTML = `<p class="error-message">Hiba történt az adatok lekérése közben.</p>`;
        });
}

<?php
if(isset($_SESSION["userplan_status"])){
?>
function openUserMenuPage4(){
    document.getElementById("profilemenu_modal_content").innerHTML = "<iframe src='admin/usermanagement.php' width='100%' height='250' style='border: none'/>";
}

function openUserMenuPage5(){
    document.getElementById("profilemenu_modal_content").innerHTML = "This is Settings Page 5";
}
<?php
}
?>
window.addEventListener('load', () => {
var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
if (isMobile) {
    document.getElementById("mainheader").innerHTML = `
    <div id="usercontrol" class="headerelements">
    <img src="vidflow_official_logo.png" id="officiallogo" class="headerelements" onclick="goHomePage()">
    <img src="<?php echo($_SESSION['profilepic_in']); ?>" id="userprofilepic" class="headerelements" onclick="openProfileUserMenu()"/>
    <b id="profileusername" class="headerelements" onclick="openProfileUserMenu()"><?php echo($_SESSION['username_in']); ?>&nbsp;
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
    <div id="searchpart">
    <form method="POST">
    <input type="text" name="searchbar" id="searchbar" placeholder="Itt tudsz keresni"/>
    <button type="submit" name="search" id="search">Keresés</button>
    </form><br>
    <button id="likedvideosboxopener" onclick="openLikedVideosModalMobile()">Kedvelt videók</button>
    <button id="playlistboxopener" onclick="openPlaylistModalMobile()">Lejátszási listák</button>
    </div>
    `;
    document.getElementById("styleelements").href = "stylemobile.css?v=1.4";
    document.getElementById("uservideosboxes").innerHTML = "";
    document.getElementById("likedvideosboxopener").style.borderImage = "linear-gradient(to bottom, "+videobordercolor1+" 0%, "+videobordercolor2+" 100%) 1";
    document.getElementById("likedvideosboxopener").style.backgroundColor = "<?php echo htmlspecialchars($_SESSION['bkgColor']); ?>";
    document.getElementById("playlistboxopener").style.borderImage = "linear-gradient(to bottom, "+videobordercolor1+" 0%, "+videobordercolor2+" 100%) 1";
    document.getElementById("playlistboxopener").style.backgroundColor = "<?php echo htmlspecialchars($_SESSION['bkgColor']); ?>";
}
});
function openLikedVideosModalMobile(){
    document.getElementById("uservideosboxes_mobile").innerHTML = `
    <div class="modal fade" id="likedVideosModal" tabindex="-1" role="dialog" aria-labelledby="likedVideosModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="likedvideosbox">
                    <div id="likedvideosbox_title">
                    <h5>Kedvelt videók</h5>
                    </div>
                    <div id="likedvideoslist">
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    `;
    const likedvideosboxmodal = new bootstrap.Modal(document.getElementById('likedVideosModal'));
    likedvideosboxmodal.show();
    document.getElementById("likedvideosbox").style.borderImage = "linear-gradient(to bottom, "+videobordercolor1+" 0%, "+videobordercolor2+" 100%) 1";
    loadAllLikedVideos();
}
function openPlaylistModalMobile(){
    document.getElementById("uservideosboxes_mobile").innerHTML = `
    <div class="modal fade" id="playlistModalMobile" tabindex="-1" role="dialog" aria-labelledby="playlistModalMobile" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="playlistvideosbox">
                    <div id="playlistvideosbox_title">
                    <h5>Lejátszási listák</h5>
                    </div>
                    <div id="playlistvideoslist">
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    `;
    const playlistboxmodal = new bootstrap.Modal(document.getElementById('playlistModalMobile'));
    playlistboxmodal.show();
    document.getElementById("playlistvideosbox").style.borderImage = "linear-gradient(to bottom, "+videobordercolor1+" 0%, "+videobordercolor2+" 100%) 1";
    loadPlaylistsIntoSidebar()
}

function openPlayListModal(username, videoId){
    document.getElementById("addtoplaylist-modal").innerHTML = `
    <div class="modal fade" id="playlistModal" tabindex="-1" role="dialog" aria-labelledby="playlistModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="playlistModalLabel">Add hozzá lejátszási listához</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="playlistModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Mégse</button>
                <button type="button" class="btn btn-primary" onclick="createNewPlaylist('${videoId.replace(/'/g, "\\'")}')">Új létrehozása</button>
            </div>
        </div>
    </div>
    </div>
    `;
    loadPlaylistsIntoModal(username, videoId);
    addtoplaylistboxmodal = new bootstrap.Modal(document.getElementById('playlistModal'));
    addtoplaylistboxmodal.show();
}

function loadPlaylistsIntoModal(username, videoId) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "services/get_playlists.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        var modalBody = document.getElementById("playlistModalBody");
        if (!modalBody) {
            console.error("Hiba: 'playlistModalBody' nem található a DOM-ban.");
            return;
        }
        if (this.status == 200) {
            modalBody.innerHTML = this.responseText;

            let playlistItems = modalBody.querySelectorAll('.playlist-item');
            playlistItems.forEach(item => {
                item.addEventListener('click', function() {
                    addVideoToPlaylist(username, videoId, this.dataset.playlistName);
                });
            });

            let deleteIcons = modalBody.querySelectorAll('.playlist-delete-icon');
            deleteIcons.forEach(icon => {
                icon.addEventListener('click', function(event) {
                    event.stopPropagation();
                    const playlistName = this.dataset.playlistName;
                    deletePlaylist(username, playlistName, videoId);
                });
            });

        } else {
            modalBody.innerHTML = "Hiba a lejátszási listák betöltésekor.";
        }
    }
    xhr.onerror = function() {
         var modalBody = document.getElementById("playlistModalBody");
         if (modalBody) {
             modalBody.innerHTML = "Hálózati hiba a lejátszási listák betöltésekor.";
         }
    };
    xhr.send("username=" + encodeURIComponent(username) + "&videoid=" + encodeURIComponent(videoId));
}

function addVideoToPlaylist(username, videoId, playlistName) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "services/add_video_to_playlist.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (this.status == 200) {
            alert(this.responseText);
            addtoplaylistboxmodal.hide();
            loadPlaylistsIntoSidebar();
        } else {
             alert("Hiba történt a videó hozzáadásakor.");
        }
    }
    xhr.send("username=" + encodeURIComponent(username) + "&videoid=" + encodeURIComponent(videoId) + "&playlist_name=" + encodeURIComponent(playlistName));
}

function createNewPlaylist(videoId) {
    let playlistName = prompt("Add meg az új lejátszási lista nevét:");
    if (playlistName != null && playlistName.trim() !== "") {
        let username = '<?php echo addslashes($_SESSION['username_in']); ?>';
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "services/create_playlist.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (this.status == 200) {
                alert(this.responseText);
                addtoplaylistboxmodal.hide();
                loadPlaylistsIntoSidebar();
            } else {
                alert("Hiba történt a lejátszási lista létrehozásakor.");
            }
        }
         xhr.onerror = function() {
             alert("Hálózati hiba történt a lejátszási lista létrehozásakor.");
        };
        xhr.send("username=" + encodeURIComponent(username) + "&playlist_name=" + encodeURIComponent(playlistName) + "&videoid=" + encodeURIComponent(videoId));
    } else if (playlistName !== null) {
        alert("A lejátszási lista neve nem lehet üres.");
    }
}

function deletePlaylist(username, playlistName, videoId) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "services/delete_playlist.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (this.status == 200) {
            alert(this.responseText);
            loadPlaylistsIntoModal(username, videoId);
        } else {
            alert("Hiba történt a lejátszási lista törlésekor.");
        }
    }
    xhr.onerror = function() {
        alert("Hálózati hiba történt a lejátszási lista törlésekor.");
    };
    xhr.send("username=" + encodeURIComponent(username) + "&playlist_name=" + encodeURIComponent(playlistName));
}

function loadPlaylistsIntoSidebar() {
    const username = '<?php echo $_SESSION["username_in"]; ?>';
    const endpointUrl = '<?= (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://${_SERVER['HTTP_HOST']}" ?>/services/get_sidebar_playlists.php';
    const baseUrl = '<?= (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://${_SERVER['HTTP_HOST']}" ?>';

    fetch(endpointUrl)
        .then(response => {
            return response.json();
        })
        .then(data => {
            const playlistContainer = document.getElementById('playlistvideoslist');
            playlistContainer.innerHTML = "";
            if (!Array.isArray(data) || data.length === 0) {
                playlistContainer.innerHTML = '<p style="color: grey; padding: 5px;">Nincsenek lejátszási listáid.</p>';
                return;
            }
            data.forEach(playlist => {
                const safePlaylistName = playlist.name.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                let videoListHtml = '';
                if (playlist.videos && playlist.videos.length > 0) {
                    playlist.videos.forEach(videoId => {
                        const videoUrl = `${baseUrl}/watch?id=${encodeURIComponent(videoId)}`;
                        const safeVideoUrl = videoUrl.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        const safeVideoId = videoId.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                        videoListHtml += `
                            <div class="sidebar-video-entry">
                                <a href="${safeVideoUrl}" target="_blank">${safeVideoUrl}</a>
                                <img src="/pageelements/trash_icon.png"
                                     alt="Törlés"
                                     class="sidebar-video-delete-icon"
                                     title="Videó eltávolítása a listából"
                                     onclick="removeVideoFromSidebarPlaylist('${username}', '${safePlaylistName}', '${safeVideoId}')">
                            </div>`;
                    });
                } else {
                    videoListHtml = '<div class="sidebar-video-id" style="color: grey; font-style: italic;">(üres lista)</div>';
                }
                const playlistElementHtml = `
                    <div class="sidebar-playlist-item">
                        <div class="sidebar-playlist-name" title="Kattints a lenyitáshoz/összecsukáshoz">
                            ${safePlaylistName}
                            <span class="playlist-arrow">&#9658;</span>
                        </div>
                        <div class="sidebar-playlist-videos" style="display: none;">
                            ${videoListHtml}
                        </div>
                    </div>
                `;
                playlistContainer.innerHTML += playlistElementHtml;
            });
            playlistContainer.querySelectorAll('.sidebar-playlist-name').forEach(nameElement => {
                nameElement.addEventListener('click', function() {
                    const videosDiv = this.nextElementSibling;
                    const arrowSpan = this.querySelector('.playlist-arrow');
                    if (videosDiv) {
                        if (videosDiv.style.display === 'none') {
                            videosDiv.style.display = 'block';
                            if (arrowSpan) arrowSpan.innerHTML = '&#9660;';
                        } else {
                            videosDiv.style.display = 'none';
                            if (arrowSpan) arrowSpan.innerHTML = '&#9658;';
                        }
                    }
                });
            });
        })
        .catch(error => {
            console.error('Error fetching or processing playlists:', error);
            const playlistContainer = document.getElementById('playlistvideoslist');
            if (playlistContainer) {
                 playlistContainer.innerHTML = `<p style="color: red; padding: 5px;">Hiba a lejátszási listák betöltésekor.</p>`;
            }
        });
}
loadPlaylistsIntoSidebar();
function removeVideoFromSidebarPlaylist(username, playlistName, videoId) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "services/remove_video_from_playlist.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (this.status == 200) {
            loadPlaylistsIntoSidebar();
        } else {
            alert(`Hiba történt a videó eltávolításakor (Státusz: ${this.status})`);
        }
    };
    xhr.onerror = function() {
        alert("Hálózati hiba történt a videó eltávolítása közben.");
    };
    xhr.send("username=" + encodeURIComponent(username) + "&playlist_name=" + encodeURIComponent(playlistName) + "&video_id=" + encodeURIComponent(videoId));
}
</script>
<script src="script.js?v=1.8"></script>
</html>
