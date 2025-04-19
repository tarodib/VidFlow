<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if(!isset($_SESSION["username_in"])){
    header("Location: /login");
}

if(isset($_POST["search"])){
    $searchbarValue = "/?search=" . $_POST["searchbar"] . "";
    header("Location: ".$searchbarValue);
    exit;
}

if(isset($_POST["logout"])){
    session_destroy();
    header("Location: /login");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VidFlow Video Player</title>
  <link rel="icon" href="/webicon.ico"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link id="styleelements" rel="stylesheet" href="/style.css?ver=1.5"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inder&family=Itim&display=swap" rel="stylesheet">
  <style>
  :root {
  --youtube-blue: #04a7de;
  }

html {
  box-sizing: border-box;
  height: 100%;
}

*, *::before, *::after {
  box-sizing: inherit;
  margin: 0;
  padding: 0;
}

body {
  height: 100%;
  background-color: #E3E1E1;
}

.container {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: flex-start;
  align-items: center;
}

.video-container {
  width: 800px;
  height: 450px;
  border-radius: 4px;
  margin: 0 auto;
  position: absolute;
  left: 50px;
  top: 150px;
}

video {
  width: 100%;
  height: 100%;
  border-radius: 4px;
  object-fit: cover;
}

.video-controls {
  right: 0;
  left: 0;
  padding: 10px;
  position: absolute;
  bottom: 0;
  transition: all 0.2s ease;
  background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.5));
}

.video-controls.hide {
  opacity: 0;
  pointer-events: none;
}

.video-progress {
  position: relative;
  height: 8.4px;
  margin-bottom: 10px;
  background: #04a7de;
}

progress {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  border-radius: 2px;
  width: 100%;
  height: 8.4px;
  pointer-events: none;
  position: absolute;
  top: 0;
  background: #08c1ff;
}

progress::-webkit-progress-bar {
  background-color: #75706f;
  border-radius: 2px;
}

progress::-webkit-progress-value {
  background: #0488b5;
  border-radius: 2px;
}

progress::-moz-progress-bar {
  border: 1px solid #04a7de;
  background: #04a7de;
}

.seek {
  position: absolute;
  top: 0;
  width: 100%;
  cursor: pointer;
  margin: 0;
}

.seek:hover+.seek-tooltip {
  display: block;
}

.seek-tooltip {
  display: none;
  position: absolute;
  top: -50px;
  margin-left: -20px;
  font-size: 12px;
  padding: 3px;
  content: attr(data-title);
  font-weight: bold;
  color: #fff;
  background-color: rgba(0, 0, 0, 0.6);
}

.bottom-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.left-controls {
  display: flex;
  align-items: center;
  color: #b7f2ae;
}

.volume-controls {
  display: flex;
  align-items: center;
  margin-right: 10px;
}

.volume-controls input {
  width: 100px;
  opacity: 1;
  transition: all 0.4s ease;
}

.volume-controls:hover input, .volume-controls input:focus {
  width: 100px;
  opacity: 1;
}

.video-player-buttons {
  cursor: pointer;
  position: relative;
  margin-right: 7px;
  font-size: 12px;
  padding: 3px;
  border: none;
  outline: none;
  background-color: transparent;
  color: #b7f2ae;
}

.video-player-buttons * {
  pointer-events: none;
}

.video-player-buttons::before {
  content: attr(data-title);
  position: absolute;
  display: none;
  right: 0;
  top: -50px;
  background-color: rgba(0,0,0,0,6);
  color: #04d145;
  font-weight: bold;
  padding: 4px 6px;
  word-break: keep-all;
  white-space: pre;
}

.video-player-buttons:hover::before {
  display: inline-block;
}

.fullscreen-button {
  margin-right: 0;
}

.pip-button svg {
  width: 26px;
  height: 26px;
}

.playback-animation {
  pointer-events: none;
  position: absolute;
  top: 50%;
  left: 50%;
  margin-left: -40px;
  margin-top: -40px;
  width: 80px;
  height: 80px;
  border-radius: 80px;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  opacity: 0;
}

input[type=range] {
  -webkit-appearance: none;
  -moz-appearance: none;
  height: 8.4px;
  background: transparent;
  cursor: pointer;
}

input[type=range]:focus {
  outline: none;
}

input[type=range]::-webkit-slider-runnable-track {
  width: 100%;
  cursor: pointer;
  border-radius: 1.3px;
  -webkit-appearance: none;
  transition: all 0.4s ease;
}

input[type=range]::-webkit-slider-thumb {
  height: 16px;
  width: 16px;
  border-radius: 16px;
  background: var(--youtube-blue);
  cursor: pointer;
  -webkit-appearance: none;
  margin-left: -1px;
}

input[type=range]:focus::-webkit-slider-runnable-track {
  background: transparent;
}

input[type=range].volume {
  height: 5px;
  background-color: lightgreen;;
}

input[type=range].volume::-webkit-slider-runnable-track {
  background-color: transparent;
}

input[type=range].volume::-webkit-slider-thumb {
  margin-left: 0;
  height: 14px;
  width: 14px;
  background: green;
}

input[type=range]::-moz-range-track {
  width: 100%;
  height: 8.4px;
  cursor: pointer;
  border: 1px solid transparent;
  background: transparent;
  border-radius: 1.3px;
}

input[type=range]::-moz-range-thumb {
  height: 14px;
  width: 14px;
  border-radius: 50px;
  border: 1px solid var(--youtube-blue);
  background: var(--youtube-blue);
  cursor: pointer;
  margin-top: 5px;
}

input[type=range]:focus::-moz-range-track {
  outline: none;
}

input[type=range].volume::-moz-range-thumb {
  border: 1px solid #fff;
  background: #fff;
}

.hidden {
  display: none;
}

svg {
  width: 28px;
  height: 28px;
  fill: #fff;
  stroke: #fff;
  cursor: pointer;
}


#qualitySelector {
    padding: 5px;
    font-size: 16px;
    border-radius: 5px;
    background-color: #fff;
    
}


  </style>
</head>
<body>
<div class="header" id="mainheader">
<img src="/pageelements/menu_icon.png" id="menuicon" class="headerelements" onclick="hideShowBoxes()"/>
<img src="/vidflow_official_logo.png" id="officiallogo" class="headerelements" onclick="goHomePage()">
<div id="searchpart">
<form method="POST">
<input type="text" name="searchbar" id="searchbar" placeholder="Itt tudsz keresni"/>
<button type="submit" name="search" id="search">Keresés</button>
</form>
</div>
<div id="usercontrol">
<img src="<?php echo($_SESSION['profilepic_in']); ?>" id="userprofilepic" class="headerelements" onclick="openProfileUserMenu()"/>
<b id="profileusername" class="headerelements" onclick="openProfileUserMenu()"><?php echo($_SESSION['username_in']); ?>&nbsp;
<?php
require '../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if(isset($_SESSION['userplan_status'])){
    echo($_SESSION['userplan_status']);
}
if(isset($_GET['id'])){
$videoDurationInSeconds;
$apiUrl = "https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id={$_GET['id']}&key={$_ENV['YOUTUBE_API_KEY_3']}";
$videoApiGet = file_get_contents($apiUrl);
$videoDataJson = json_decode($videoApiGet, true);
if (isset($videoDataJson['items'][0]['contentDetails']['duration'])) {
    $videoDurationRaw = $videoDataJson['items'][0]['contentDetails']['duration'];
    $videoDurationInterval = new DateInterval($videoDurationRaw);
    $videoDurationInSeconds = ($videoDurationInterval->d * 24 * 60 * 60) + ($videoDurationInterval->h * 60 * 60) + ($videoDurationInterval->i * 60) + $videoDurationInterval->s;
}
$getVideoDetailsUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id={$_GET['id']}&key={$_ENV['YOUTUBE_API_KEY_3']}";
$getVideoDetails = file_get_contents($getVideoDetailsUrl);
$videoDetailsJson = json_decode($getVideoDetails, true);
$videoTitle = $videoDetailsJson['items'][0]['snippet']['title'];
$channelTitle = $videoDetailsJson['items'][0]['snippet']['channelTitle'];
$channelId = $videoDetailsJson['items'][0]['snippet']['channelId'];
$channelDetailsUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet&id=$channelId&key={$_ENV['YOUTUBE_API_KEY_3']}";
$channelResponse = file_get_contents($channelDetailsUrl);
if ($channelResponse === false) {
    die('Hiba történt a csatorna adatok lekérésekor.');
}
$channelData = json_decode($channelResponse, true);
$uploaderAvatar = $channelData['items'][0]['snippet']['thumbnails']['default']['url'] ?? '';
$numberOfRelatedVideos = 6;

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
    
function getRelatedVideosAlgorithm($searchTerm, $resultsPerBatch = 6) {
        $relatedsearchUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&q=" . urlencode($searchTerm) . "&maxResults={$resultsPerBatch}&key={$_ENV['YOUTUBE_API_KEY_2']}";
        $relatedsearchResponse = file_get_contents($relatedsearchUrl);

        if ($relatedsearchResponse === false) {
            return [];
        }
        $relatedsearchData = json_decode($relatedsearchResponse, true);
        $relatedvideoIds = [];
        foreach ($relatedsearchData['items'] as $item) {
            $relatedvideoIds[] = $item['id']['videoId'];
        }
        if (empty($relatedvideoIds)) {
            return [];
        }
        $relatedvideoDetailsUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=" . implode(',', $relatedvideoIds) . "&key={$_ENV['YOUTUBE_API_KEY_3']}";
        $relateddetailsResponse = file_get_contents($relatedvideoDetailsUrl);
        if ($relateddetailsResponse === false) {
            return [];
        }
        $relateddetailsData = json_decode($relateddetailsResponse, true);
        $relatedvideos = [];
        foreach ($relateddetailsData['items'] as $relateditem) {
            $relatedvideoduration = $relateditem['contentDetails']['duration'];
            $relatedvideos[] = [
                'id' => $relateditem['id'],
                'title' => $relateditem['snippet']['title'],
                'duration' => formatDuration($relatedvideoduration),
                'uploader' => $relateditem['snippet']['channelTitle'],
                'thumbnail' => $relateditem['snippet']['thumbnails']['medium']['url'],
                'uploader_url' => "https://www.youtube.com/channel/" . $relateditem['snippet']['channelId'],
            ];
        }

        return $relatedvideos;
    }
    
if(isset($videoDetailsJson['items'][0]['snippet']['tags'])){
$numberofTagsforVideo = 0;
$tagsForVideo = "";
while($numberofTagsforVideo < count($videoDetailsJson['items'][0]['snippet']['tags'])){
    $tagsForVideo = $tagsForVideo . $videoDetailsJson['items'][0]['snippet']['tags'][$numberofTagsforVideo] . ",";
    $numberofTagsforVideo++;
}
$currentvideorelatedvideos = getRelatedVideosAlgorithm(substr_replace($tagsForVideo, "", -1), $numberOfRelatedVideos);
}
}
?>
</b>
<div class="headerelements">
<form method="POST">
<button id="logout" name="logout" type="submit" onmouseenter="switchLogoutRed()" onmouseleave="switchLogoutDefault()"><img src="/pageelements/logout.png" id="logouticon" /></button>
</form>
</div>
</div>
</div>
<div id="likedvideostatus-modal"></div>
<div id="settingsmenu-modal"></div>
<div id="addtoplaylist-modal"></div>
<div id="featuredvideosbox">
    <div id="featuredvideosbox_title">
        <h5>Ajánlott videók</h5>
    </div>
    <div id="featuredvideoslist">
    <?php
    if(isset($videoDetailsJson['items'][0]['snippet']['tags'])){
    for($relatedvideonumber = 1; $relatedvideonumber < count($currentvideorelatedvideos); $relatedvideonumber++){
    ?>
    <div class="card liked-card">
              <div class="ratio ratio-16x9"><a style="text-decoration: none; color: #7a0202;" href="/watch?id=<?= $currentvideorelatedvideos[$relatedvideonumber]['id'] ?>">
                <div class="thumbnail-container">
                  <img class="card-img-top video-thumbnail" src="https://img.youtube.com/vi/<?= $currentvideorelatedvideos[$relatedvideonumber]['id'] ?>/sddefault.jpg" alt="<?= $currentvideorelatedvideos[$relatedvideonumber]['title'] ?>">
                  <div class="video-duration"><?= $currentvideorelatedvideos[$relatedvideonumber]['duration'] ?></div>
                </div>
                  </a>
              </div>
              <div class="card-body"><a style="text-decoration: none; color: #7a0202;" href="/watch?id=<?= $currentvideorelatedvideos[$relatedvideonumber]['id'] ?>">
                  <h6 class="video-name"><?= $currentvideorelatedvideos[$relatedvideonumber]['title'] ?></h6>
                  <p class="video-details">
                      <span class="video-uploader-name" style="margin-left: 0px;"><?= $currentvideorelatedvideos[$relatedvideonumber]['uploader'] ?></span></a>
                  </p>
              </div>
          </div>
    <?php    
    }
    } else {
        echo("<script>document.getElementById('featuredvideosbox').style.display = 'none';</script>");
    }
    ?>
    </div>
</div>
  <div class="container">
    <div class="video-container" id="video-container">
      <div class="playback-animation" id="playback-animation">
        <svg class="playback-icons">
          <use class="hidden" href="#play-icon"></use>
          <use href="#pause"></use>
        </svg>
    </div>
  <video id="video" width="905px;" height="400" preload="metadata" poster="https://img.youtube.com/vi/<?php echo htmlspecialchars($_GET['id']); ?>/sddefault.jpg">
	<source src="https://ytstream.barnatech.hu/stream?url=<?php echo htmlspecialchars($_GET['id']); ?>" type="video/mp4">  
  </video>
<br><br>

  <div class="video-controls hidden" id="video-controls">
        <div class="video-progress">
          <progress id="progress-bar" value="0" min="0"></progress>
          <input class="seek" id="seek" value="0" min="0" type="range" step="1">
          <div class="seek-tooltip" id="seek-tooltip">00:00</div>
        </div>

        <div class="bottom-controls">
          <div class="left-controls">
            <button data-title="Play (k)" id="play" class="video-player-buttons">
              <svg class="playback-icons">
                <use href="#play-icon"></use>
                <use class="hidden" href="#pause"></use>
              </svg>
            </button>

            <div class="volume-controls">
              <button data-title="Mute (m)" class="volume-button video-player-buttons" id="volume-button">
                <svg>
                  <use class="hidden" href="#volume-mute"></use>
                  <use class="hidden" href="#volume-low"></use>
                  <use href="#volume-high"></use>
                </svg>
              </button>

              <input class="volume" id="volume" value="1"
              data-mute="0.5" type="range" max="1" min="0" step="0.01">
            </div>

            <div class="time" id="customvideotime">
              <time id="time-elapsed">00:00</time>
              <span> / </span>
              <time id="duration">00:00</time>
            </div>
          </div>

          <div class="right-controls">
            <button data-title="PIP (p)" class="pip-button video-player-buttons" id="pip-button">
              <svg>
                <use href="#pip"></use>
              </svg>
            </button>
            <button data-title="Full screen (f)" class="fullscreen-button video-player-buttons" id="fullscreen-button">
              <svg>
                <use href="#fullscreen"></use>
                <use href="#fullscreen-exit" class="hidden"></use>
              </svg>
            </button>
            <img src="/webicon.png" width="28px;" id="vidflowiconinplayer" height="auto" alt="VidFlow" title="VidFlow Player" style="margin-top: -20px;">
          </div>
        </div>
      </div><h4><?= $videoTitle ?></h4><br><img id="videouploaderavatarpic" src="<?= $uploaderAvatar ?>"/><span id="channelNameofVideo"><?= $channelTitle ?></span><img src="/pageelements/add-to-playlist.png" style="float: right; width: 6%; @media only screen and (max-width: 750px) { width: 8%; }" alt="Add To Playlist" onclick='openPlayListModal(&quot;<?= $_SESSION['username_in'] ?>&quot;,&quot;<?= htmlspecialchars($videoDetailsJson['items'][0]['id']) ?>&quot;)'/><img class="video-add-to-like" style="float: right; width: 6%; margin-right: 20px; @media only screen and (max-width: 750px) { width: 8%; }" src="/pageelements/video_like_button.png" alt="Like" onclick='addVideoToLiked(&quot;<?= $_SESSION['username_in'] ?>&quot;,&quot;<?= htmlspecialchars($videoDetailsJson['items'][0]['id']) ?>&quot;,&quot;<?= htmlspecialchars($videoTitle) ?>&quot;,&quot;<?= htmlspecialchars(formatDuration($videoDataJson['items'][0]['contentDetails']['duration'])) ?>&quot;,&quot;<?= htmlspecialchars($channelTitle) ?>&quot;,&quot;<?= htmlspecialchars($videoDetailsJson['items'][0]['id']) ?>&quot;)'/>
    </div>
  </div>
  

  <svg style="display: none">
    <defs>
      <symbol id="pause" viewBox="0 0 24 24">
        <path d="M14.016 5.016h3.984v13.969h-3.984v-13.969zM6 18.984v-13.969h3.984v13.969h-3.984z"></path>
      </symbol>

      <symbol id="play-icon" viewBox="0 0 24 24">
        <path d="M8.016 5.016l10.969 6.984-10.969 6.984v-13.969z"></path>
      </symbol>

      <symbol id="volume-high" viewBox="0 0 24 24">
      <path d="M14.016 3.234q3.047 0.656 5.016 3.117t1.969 5.648-1.969 5.648-5.016 3.117v-2.063q2.203-0.656 3.586-2.484t1.383-4.219-1.383-4.219-3.586-2.484v-2.063zM16.5 12q0 2.813-2.484 4.031v-8.063q1.031 0.516 1.758 1.688t0.727 2.344zM3 9h3.984l5.016-5.016v16.031l-5.016-5.016h-3.984v-6z"></path>
      </symbol>

      <symbol id="volume-low" viewBox="0 0 24 24">
      <path d="M5.016 9h3.984l5.016-5.016v16.031l-5.016-5.016h-3.984v-6zM18.516 12q0 2.766-2.531 4.031v-8.063q1.031 0.516 1.781 1.711t0.75 2.32z"></path>
      </symbol>

      <symbol id="volume-mute" viewBox="0 0 24 24">
      <path d="M12 3.984v4.219l-2.109-2.109zM4.266 3l16.734 16.734-1.266 1.266-2.063-2.063q-1.547 1.313-3.656 1.828v-2.063q1.172-0.328 2.25-1.172l-4.266-4.266v6.75l-5.016-5.016h-3.984v-6h4.734l-4.734-4.734zM18.984 12q0-2.391-1.383-4.219t-3.586-2.484v-2.063q3.047 0.656 5.016 3.117t1.969 5.648q0 2.203-1.031 4.172l-1.5-1.547q0.516-1.266 0.516-2.625zM16.5 12q0 0.422-0.047 0.609l-2.438-2.438v-2.203q1.031 0.516 1.758 1.688t0.727 2.344z"></path>
      </symbol>

      <symbol id="fullscreen" viewBox="0 0 24 24">
      <path d="M14.016 5.016h4.969v4.969h-1.969v-3h-3v-1.969zM17.016 17.016v-3h1.969v4.969h-4.969v-1.969h3zM5.016 9.984v-4.969h4.969v1.969h-3v3h-1.969zM6.984 14.016v3h3v1.969h-4.969v-4.969h1.969z"></path>
      </symbol>

      <symbol id="fullscreen-exit" viewBox="0 0 24 24">
      <path d="M15.984 8.016h3v1.969h-4.969v-4.969h1.969v3zM14.016 18.984v-4.969h4.969v1.969h-3v3h-1.969zM8.016 8.016v-3h1.969v4.969h-4.969v-1.969h3zM5.016 15.984v-1.969h4.969v4.969h-1.969v-3h-3z"></path>
      </symbol>

      <symbol id="pip" viewBox="0 0 24 24">
      <path d="M21 19.031v-14.063h-18v14.063h18zM23.016 18.984q0 0.797-0.609 1.406t-1.406 0.609h-18q-0.797 0-1.406-0.609t-0.609-1.406v-14.016q0-0.797 0.609-1.383t1.406-0.586h18q0.797 0 1.406 0.586t0.609 1.383v14.016zM18.984 11.016v6h-7.969v-6h7.969z"></path>
      </symbol>
    </defs>
  </svg>
</body>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<script>
<?php
    if(isset($_GET["id"])){
?>
    let getcurrentvideoid = "<?= $_GET['id'] ?>";
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "/services/watchinghistory_add.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("watchvideourl="+encodeURIComponent(getcurrentvideoid));
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log("Video added to watching history!");
        }
    };
<?php
    }
?>

var menuicondisplay = 1;
function hideShowBoxes(){
if(menuicondisplay != 1){
    document.getElementById("featuredvideosbox").style.display = "none";
    menuicondisplay = 1;
} else {
    document.getElementById("featuredvideosbox").style.display = "block";
    menuicondisplay = 0;
}
}
        
const video = document.getElementById('video');
const videoControls = document.getElementById('video-controls');
const playButton = document.getElementById('play');
const playbackIcons = document.querySelectorAll('.playback-icons use');
const timeElapsed = document.getElementById('time-elapsed');
const duration = document.getElementById('duration');
const progressBar = document.getElementById('progress-bar');
const seek = document.getElementById('seek');
const seekTooltip = document.getElementById('seek-tooltip');
const volumeButton = document.getElementById('volume-button');
const volumeIcons = document.querySelectorAll('.volume-button use');
const volumeMute = document.querySelector('use[href="#volume-mute"]');
const volumeLow = document.querySelector('use[href="#volume-low"]');
const volumeHigh = document.querySelector('use[href="#volume-high"]');
const volume = document.getElementById('volume');
const playbackAnimation = document.getElementById('playback-animation');
const fullscreenButton = document.getElementById('fullscreen-button');
const videoContainer = document.getElementById('video-container');
const fullscreenIcons = fullscreenButton.querySelectorAll('use');
const pipButton = document.getElementById('pip-button');

var getvolumeslider = document.getElementById("volume");
getvolumeslider.style.backgroundColor = "<?php echo htmlspecialchars($_SESSION['firstvideoborderColor']); ?>";
var getvideotime = document.getElementById("customvideotime");
getvideotime.style.color = "<?php echo htmlspecialchars($_SESSION['secondvideoborderColor']); ?>";
document.body.style.backgroundColor = "<?php echo htmlspecialchars($_SESSION['bkgColor']); ?>";
document.body.style.fontFamily = "<?php echo htmlspecialchars($_SESSION['fontFamily']); ?>";
searchbar.style.fontFamily = "<?php echo htmlspecialchars($_SESSION['fontFamily']); ?>";
profileusername.style.fontFamily = "<?php echo htmlspecialchars($_SESSION['fontFamily']); ?>";


const videoWorks = !!document.createElement('video').canPlayType;
if (videoWorks) {
  video.controls = false;
  videoControls.classList.remove('hidden');
}

function togglePlay() {
  if (video.paused || video.ended) {
    video.play();
  } else {
    video.pause();
  }
}

function updatePlayButton() {
  playbackIcons.forEach((icon) => icon.classList.toggle('hidden'));

  if (video.paused) {
    playButton.setAttribute('data-title', 'Play (k)');
  } else {
    playButton.setAttribute('data-title', 'Pause (k)');
  }
}

function formatTime(timeInSeconds) {
  const result = new Date(timeInSeconds * 1000).toISOString().substr(11, 8);
  return {
    hours: result.substr(1, 2),
    minutes: result.substr(3, 2),
    seconds: result.substr(6, 2),
  };
}

function initializeVideo() {
  const videoDuration = Math.round(<?php echo($videoDurationInSeconds); ?>);
  seek.setAttribute('max', videoDuration);
  progressBar.setAttribute('max', videoDuration);
  const time = formatTime(videoDuration);
  duration.innerText = `${time.hours}:${time.minutes}:${time.seconds}`;
  duration.setAttribute('datetime', `${time.hours}h ${time.minutes}m ${time.seconds}s`);
}

function updateTimeElapsed() {
  const time = formatTime(Math.round(video.currentTime));
  timeElapsed.innerText = `${time.hours}:${time.minutes}:${time.seconds}`;
  timeElapsed.setAttribute('datetime', `${time.hours}h ${time.minutes}m ${time.seconds}s`);
}

function updateProgress() {
  seek.value = Math.floor(video.currentTime);
  progressBar.value = Math.floor(video.currentTime);
}

function updateSeekTooltip(event) {
  const skipTo = Math.round(
    (event.offsetX / event.target.clientWidth) *
      parseInt(event.target.getAttribute('max'), 10)
  );
  seek.setAttribute('data-seek', skipTo);
  const t = formatTime(skipTo);
  seekTooltip.textContent = `${t.hours}:${t.minutes}:${t.seconds}`;
  const rect = video.getBoundingClientRect();
  seekTooltip.style.left = `${event.pageX - rect.left}px`;
}

function skipAhead(event) {
  const skipTo = event.target.dataset.seek
    ? event.target.dataset.seek
    : event.target.value;
  video.currentTime = skipTo;
  progressBar.value = skipTo;
  seek.value = skipTo;
}

function updateVolume() {
  if (video.muted) {
    video.muted = false;
  }

  video.volume = volume.value;
}

function updateVolumeIcon() {
  volumeIcons.forEach((icon) => {
    icon.classList.add('hidden');
  });

  volumeButton.setAttribute('data-title', 'Mute (m)');

  if (video.muted || video.volume === 0) {
    volumeMute.classList.remove('hidden');
    volumeButton.setAttribute('data-title', 'Unmute (m)');
  } else if (video.volume > 0 && video.volume <= 0.5) {
    volumeLow.classList.remove('hidden');
  } else {
    volumeHigh.classList.remove('hidden');
  }
}

function toggleMute() {
  video.muted = !video.muted;

  if (video.muted) {
    volume.setAttribute('data-volume', volume.value);
    volume.value = 0;
  } else {
    volume.value = volume.dataset.volume;
  }
}

function animatePlayback() {
  playbackAnimation.animate(
    [
      {
        opacity: 1,
        transform: 'scale(1)',
      },
      {
        opacity: 0,
        transform: 'scale(1.3)',
      },
    ],
    {
      duration: 500,
    }
  );
}

function toggleFullScreen() {
  if (document.fullscreenElement) {
    document.exitFullscreen();
  } else if (document.webkitFullscreenElement) {
    // Need this to support Safari
    document.webkitExitFullscreen();
  } else if (videoContainer.webkitRequestFullscreen) {
    // Need this to support Safari
    videoContainer.webkitRequestFullscreen();
  } else {
    videoContainer.requestFullscreen();
  }
}

function updateFullscreenButton() {
  fullscreenIcons.forEach((icon) => icon.classList.toggle('hidden'));

  if (document.fullscreenElement) {
    fullscreenButton.setAttribute('data-title', 'Exit full screen (f)');
  } else {
    fullscreenButton.setAttribute('data-title', 'Full screen (f)');
  }
}

async function togglePip() {
  try {
    if (video !== document.pictureInPictureElement) {
      pipButton.disabled = true;
      await video.requestPictureInPicture();
    } else {
      await document.exitPictureInPicture();
    }
  } catch (error) {
    console.error(error);
  } finally {
    pipButton.disabled = false;
  }
}

function hideControls() {
  if (video.paused) {
    return;
  }

  videoControls.classList.add('hide');
}

function showControls() {
  videoControls.classList.remove('hide');
}

function keyboardShortcuts(event) {
  const { key } = event;
  switch (key) {
    case 'k':
      togglePlay();
      animatePlayback();
      if (video.paused) {
        showControls();
      } else {
        setTimeout(() => {
          hideControls();
        }, 2000);
      }
      break;
    case 'm':
      toggleMute();
      break;
    case 'f':
      toggleFullScreen();
      break;
    case 'p':
      togglePip();
      break;
  }
}

playButton.addEventListener('click', togglePlay);
video.addEventListener('play', updatePlayButton);
video.addEventListener('pause', updatePlayButton);
video.addEventListener('loadedmetadata', initializeVideo);
video.addEventListener('timeupdate', updateTimeElapsed);
video.addEventListener('timeupdate', updateProgress);
video.addEventListener('volumechange', updateVolumeIcon);
video.addEventListener('click', togglePlay);
video.addEventListener('click', animatePlayback);
video.addEventListener('mouseenter', showControls);
video.addEventListener('mouseleave', hideControls);
videoControls.addEventListener('mouseenter', showControls);
videoControls.addEventListener('mouseleave', hideControls);
seek.addEventListener('mousemove', updateSeekTooltip);
seek.addEventListener('input', skipAhead);
volume.addEventListener('input', updateVolume);
volumeButton.addEventListener('click', toggleMute);
fullscreenButton.addEventListener('click', toggleFullScreen);
videoContainer.addEventListener('fullscreenchange', updateFullscreenButton);
pipButton.addEventListener('click', togglePip);

document.addEventListener('DOMContentLoaded', () => {
  if (!('pictureInPictureEnabled' in document)) {
    pipButton.classList.add('hidden');
  }
});
document.addEventListener('keyup', keyboardShortcuts);

var timeout;
document.onmousemove = function(){
  clearTimeout(timeout);
  timeout = setTimeout(function(){hideControls();}, 4000);
}

video.onmousemove = function(){
  showControls();
}

function goHomePage(){
    location.href = "https://vidflow.barnatech.hu/";
}

function switchLogoutRed(){
    document.getElementById("logouticon").src = "/pageelements/logout_red.png";
}

function switchLogoutDefault(){
    document.getElementById("logouticon").src = "/pageelements/logout.png";
}

const cards = document.querySelectorAll('.card');
cards.forEach(card => {
  card.style.backgroundColor = "#E1B995";
});
var videobordercolor1 = "<?php echo htmlspecialchars($_SESSION['firstvideoborderColor']); ?>";
var videobordercolor2 = "<?php echo htmlspecialchars($_SESSION['secondvideoborderColor']); ?>";
document.getElementById("featuredvideosbox").style.borderImage = "linear-gradient(to bottom, "+videobordercolor1+" 0%, "+videobordercolor2+" 100%) 1";
const videothumbnails = document.querySelectorAll('.video-thumbnail');
const videolengthboxes = document.querySelectorAll('.video-duration');
videothumbnails.forEach(videothumbnail => {
    videothumbnail.style.background = "linear-gradient(to bottom,"+videobordercolor1+","+videobordercolor2+") border-box, white padding-box";
});
videolengthboxes.forEach(videolengthbox => {
    videolengthbox.style.background = "linear-gradient(to bottom,"+videobordercolor1+","+videobordercolor2+") border-box, white padding-box";
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
        <button type="button" class="modal-title profilemenu_modal_options" id="profilemenu_modal_page4" onclick="openUserMenuPage4()">Felhasználók kezelése</button>
        <?php
        }
        ?>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="profilemenu_modal_content">
        Profil beállítások rész, nemsokára feltöltésre kerül!
      </div>
    </div>
  </div>
</div>`;
const settingsmodal = new bootstrap.Modal(document.getElementById('profilemenu_modal_box'));
settingsmodal.show();
}

function openUserMenuPage1(){
    fetch('/services/userdata_handling.php')
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
    xhr.open("POST", "/services/userdata_handling.php", true);
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
        xhr.open("POST", "/services/userdata_passwordchange.php", true);
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
                <img src="/pageelements/upload_icon-black.png" id="profilepicupload_uploadicon" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 50px;">
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
            xhr.open('POST', '/services/customization_update.php', true);
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
            xhr.open('POST', '/services/customization_update.php', true);
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

window.addEventListener('load', () => {
var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
if (isMobile) {
    document.getElementById("mainheader").innerHTML = `
    <div id="usercontrol" class="headerelements">
    <img src="/vidflow_official_logo.png" id="officiallogo" class="headerelements" onclick="goHomePage()">
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
    <button id="logout" name="logout" type="submit" onmouseenter="switchLogoutRed()" onmouseleave="switchLogoutDefault()"><img src="/pageelements/logout.png" id="logouticon" /></button>
    </form>
    </div>
    </div>
    <div id="searchpart">
    <form method="POST">
    <input type="text" name="searchbar" id="searchbar" placeholder="Itt tudsz keresni"/>
    <button type="submit" name="search" id="search">Keresés</button>
    </form>
    </div>
    `;
    document.getElementById("styleelements").href = "/stylemobile.css?v=1.3";
    document.getElementById("uservideosboxes").innerHTML = "";
    document.getElementById("featuredvideosbox").style.display = "none";
}
});

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
    xhr.open("POST", "/services/get_playlists.php", true);
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
    xhr.open("POST", "/services/add_video_to_playlist.php", true);
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
        xhr.open("POST", "/services/create_playlist.php", true);
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
    xhr.open("POST", "/services/delete_playlist.php", true);
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

<?php
if(isset($_SESSION["userplan_status"])){
?>
function openUserMenuPage4(){
    document.getElementById("profilemenu_modal_content").innerHTML = "<iframe src='../admin/usermanagement.php' width='100%' height='250' style='border: none'/>";
}
<?php
}
?>
</script>
<script src="script.js?v=1.4"></script>
</html>
