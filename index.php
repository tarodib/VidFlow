<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
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
<meta name="viewport" content="width=device-width">
<title>VidFlow - Főoldal</title>
<link rel="icon" href="webicon.ico"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" href="style.css?v=1.5"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inder&family=Itim&display=swap" rel="stylesheet">
</head>
<body>
<div class="header">
<img src="pageelements/menu_icon.png" id="menuicon" class="headerelements"/>
<img src="vidflow_official_logo.png" id="officiallogo" class="headerelements" onclick="goHomePage()">
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
<br>
<div class="container mt-4">
  <div class="row">
  <?php
    function searchInProgress($searchURI){
        $splitURI = explode("?search=", $searchURI);
        $searchTerm = $splitURI[1];

    function fetchYouTubeResults($searchTerm, $resultsPerBatch = 5) {
        $apiKey = 'AIzaSyCUtocPhyhUfi-KMJ56ya3r46HoOf79VhE';
        $searchUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&q=" . $searchTerm . "&maxResults={$resultsPerBatch}&key={$apiKey}";
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
        $videoDetailsUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=" . implode(',', $videoIds) . "&key={$apiKey}";
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
                <div class="row mb-3"><a style="text-decoration: none; color: #7a0202;" href="/watch?id=<?= htmlspecialchars($video['id']) ?>">
                    <div class="col-md-4">
                            <img class="rounded video-thumbnail" src="<?= htmlspecialchars($video['thumbnail']) ?>" alt="<?= htmlspecialchars($video['title']) ?>">
                    </div>
                    <div class="col-md-8">
                        <h5 class="video-name"><?= htmlspecialchars($video['title']) ?></h5>
                        <p class="text-muted mb-1 video-uploader-name">By <a href="<?= htmlspecialchars($video['uploader_url']) ?>" target="_blank" style="text-decoration: none;"><?= htmlspecialchars($video['uploader']) ?></a></p>
                        <p class="text-muted">Időtartam: <?= htmlspecialchars($video['duration']) ?></p>
                    </div>
                    </a>
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
            return ($interval->h * 60) + $interval->i + round($interval->s / 60, 2);
        } catch (Exception $e) {
            return 'Ismeretlen';
        }
    }
    
    if (str_contains($searchURI, "?search=")) {
        searchInProgress($searchURI);
    } else {
    $allVideoData = [];
    $trendingVideosUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&chart=mostPopular&regionCode=HU&key=AIzaSyCeoOEm4iaj18D6YeFI6yMhfUQ0uuFAuNY&maxResults=20";
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
        $channelDetailsUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet&id=$channelId&key=AIzaSyCeoOEm4iaj18D6YeFI6yMhfUQ0uuFAuNY";
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
          <div class="card"><a style="text-decoration: none; color: #7a0202;" href="/watch?id=<?= htmlspecialchars($videometadata['id']) ?>">
              <div class="ratio ratio-16x9">
                  <img class="card-img-top video-thumbnail" src="<?= htmlspecialchars($videometadata['thumbnail']) ?>" alt="<?= htmlspecialchars($videometadata['title']) ?>">
              </div>
              <div class="card-body">
                  <h6 class="video-name"><?= htmlspecialchars($videometadata['title']) ?></h6>
                  <p class="video-details">
                      <img class="video-uploader-pic" src="<?= htmlspecialchars($videometadata['uploader_avatar']) ?>" alt="<?= htmlspecialchars($videometadata['uploader']) ?>">
                      <span class="video-uploader-name"><?= htmlspecialchars($videometadata['uploader']) ?></span>
                  </p>
              </div>
          </a></div>
      </div>
    <?php endforeach;
    }
    ?>
  </div>
</div>
</body>
<script src="script.js"></script>
<script>
document.body.style.backgroundColor = "<?php echo htmlspecialchars($_SESSION['bkgColor']); ?>";
document.body.style.fontFamily = "<?php echo htmlspecialchars($_SESSION['fontFamily']); ?>";
searchbar.style.fontFamily = "<?php echo htmlspecialchars($_SESSION['fontFamily']); ?>";
profileusername.style.fontFamily = "<?php echo htmlspecialchars($_SESSION['fontFamily']); ?>";
var videobordercolor1 = "<?php echo htmlspecialchars($_SESSION['firstvideoborderColor']); ?>";
var videobordercolor2 = "<?php echo htmlspecialchars($_SESSION['secondvideoborderColor']); ?>";
const cards = document.querySelectorAll('.card');
cards.forEach(card => {
  card.style.backgroundColor = "<?php echo htmlspecialchars($_SESSION['bkgColor']); ?>";
});
const videothumbnails = document.querySelectorAll('.video-thumbnail');
videothumbnails.forEach(videothumbnail => {
    videothumbnail.style.background = "linear-gradient(to bottom,"+videobordercolor1+","+videobordercolor2+") border-box, white padding-box";
});
</script>
</html>
