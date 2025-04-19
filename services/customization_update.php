<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$servername = $_ENV['DB_SERVERNAME'];
$db_username = $_ENV['DB_USERNAME'];
$db_password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $db_username, $db_password);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Adatbázis kapcsolódási hiba.']);
    exit;
}

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Ismeretlen hiba.', 'newImageUrl' => null];
$uploadDir = '../pictures/';
$webPathPrefix = '/pictures/';

$defaultProfilePic = '/pageelements/userprofile_default.png';
$defaultStyleCode = 'default';

if (!isset($_SESSION['username_in'])) {
    $response['message'] = 'Nincs bejelentkezve.';
    echo json_encode($response);
    exit;
}
$loggedInUsername = $_SESSION['username_in'];

try {
    $stmtUser = $pdo->prepare("SELECT id, profilepic, style_code FROM users WHERE username = ?");
    $stmtUser->execute([$loggedInUsername]);
    $currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    $currentUserId = $currentUser['id'];
    $currentProfilePic = $currentUser['profilepic'];
    $currentStyleCodeFromUser = $currentUser['style_code'];

    $currentFont = 'Arial';
    $currentBgColor = '#e3e1e1';
    $currentBorderColor1 = '#be0606';
    $currentBorderColor2 = '#7a0202';
    $currentCustomizationStyleCode = null;

    if ($currentStyleCodeFromUser) {
        $stmtStyle = $pdo->prepare("SELECT style_code, fontfamilyType, backgroundColor, videoborderColor FROM customization WHERE style_code = ?");
        $stmtStyle->execute([$currentStyleCodeFromUser]);
        $currentStyleData = $stmtStyle->fetch(PDO::FETCH_ASSOC);

        if ($currentStyleData) {
            $currentCustomizationStyleCode = $currentStyleData['style_code'];
            $currentFont = $currentStyleData['fontfamilyType'];
            $currentBgColor = $currentStyleData['backgroundColor'];
            $videoBorderColors = explode(';', $currentStyleData['videoborderColor']);
            $currentBorderColor1 = $videoBorderColors[0] ?? '#be0606';
            $currentBorderColor2 = $videoBorderColors[1] ?? '#7a0202';
        }
    }

} catch (PDOException $e) {
    $response['message'] = 'Adatbázis hiba történt az aktuális adatok lekérésekor.';
    echo json_encode($response);
    exit;
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'reset_defaults') {

        try {
            $pdo->beginTransaction();
            $stmtUpdatePic = $pdo->prepare("UPDATE users SET profilepic = ? WHERE id = ?");
            if (!$stmtUpdatePic->execute([$defaultProfilePic, $currentUserId])) {
                throw new Exception("Profilkép visszaállítása sikertelen (DB).");
            }

            $stmtUpdateStyleCode = $pdo->prepare("UPDATE users SET style_code = ? WHERE id = ?");
             if (!$stmtUpdateStyleCode->execute([$defaultStyleCode, $currentUserId])) {
                throw new Exception("Stílus kód visszaállítása sikertelen (DB).");
            }

            $oldPicPhysicalPath = realpath($uploadDir . basename($currentProfilePic));
            if ($currentProfilePic &&
                $currentProfilePic !== $defaultProfilePic &&
                $oldPicPhysicalPath &&
                strpos($oldPicPhysicalPath, realpath($uploadDir)) === 0 &&
                file_exists($oldPicPhysicalPath))
            {
                @unlink($oldPicPhysicalPath);
            }

            $_SESSION["profilepic_in"] = $defaultProfilePic;
            $_SESSION["style_code"] = $defaultStyleCode;

            $stmtDefaultStyleData = $pdo->prepare("SELECT fontfamilyType, backgroundColor, videoborderColor FROM customization WHERE style_code = ?");
            $stmtDefaultStyleData->execute([$defaultStyleCode]);
            $defaultStyleData = $stmtDefaultStyleData->fetch(PDO::FETCH_ASSOC);
            if ($defaultStyleData) {
                $_SESSION["bkgColor"] = $defaultStyleData['backgroundColor'];
                $_SESSION["fontFamily"] = $defaultStyleData['fontfamilyType'];
                $defaultBorderParts = explode(';', $defaultStyleData['videoborderColor'] ?? '');
                $_SESSION["firstvideoborderColor"] = $defaultBorderParts[0] ?? '#be0606';
                $_SESSION["secondvideoborderColor"] = $defaultBorderParts[1] ?? ($_SESSION["firstvideoborderColor"] ?? '#7a0202');
            } else {
                error_log("Could not fetch default style details for session update during reset. Style code: " . htmlspecialchars($defaultStyleCode));
            }

            $pdo->commit();
            $response['success'] = true;
            $response['message'] = 'Beállítások sikeresen visszaállítva az alapértelmezettre!';
            $response['newImageUrl'] = $defaultProfilePic;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
               $pdo->rollBack();
            }
            $response['message'] = "Hiba a visszaállítás során: " . $e->getMessage();
            error_log("Default reset failed for user " . $loggedInUsername . ": " . $e->getMessage());
            $response['newImageUrl'] = null;
        }

        echo json_encode($response);
        exit;

    }

    $newFontType = isset($_POST['fontType']) ? trim($_POST['fontType']) : null;
    $newBgColor = isset($_POST['bgColor']) ? trim($_POST['bgColor']) : null;
    $newBorderColor1 = isset($_POST['borderColor1']) ? trim($_POST['borderColor1']) : null;
    $newBorderColor2 = isset($_POST['borderColor2']) ? trim($_POST['borderColor2']) : null;
    $newVideoBorderCombined = null;
    if ($newBorderColor1 !== null && $newBorderColor2 !== null) {
        $newVideoBorderCombined = $newBorderColor1 . ';' . $newBorderColor2;
    }

    $newProfilePicPath = null;
    $destination = null;
    $uploadError = null;
    $picUpdateNeeded = false;

    if (isset($_FILES['userProfilePic']) && $_FILES['userProfilePic']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['userProfilePic'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024;

        if ($file['size'] > $maxFileSize) {
            $uploadError = 'A fájl mérete túl nagy (maximum ' . ($maxFileSize / 1024 / 1024) . ' MB).';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedMimeTypes)) {
                $uploadError = 'Nem engedélyezett fájltípus. Csak JPG, PNG, GIF tölthető fel.';
            } else {
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
                $safeExtension = $extMap[$mimeType] ?? $fileExtension;
                $safeFilename = bin2hex(random_bytes(8)) . '_' . time() . '.' . $safeExtension;
                $destination = $uploadDir . $safeFilename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $newProfilePicPath = $webPathPrefix . $safeFilename;
                    $picUpdateNeeded = true;
                } else {
                    $uploadError = 'Hiba történt a fájl mentése közben. Ellenőrizd a mappa jogosultságait!';
                    error_log("Failed to move uploaded file to: " . $destination . " for user: " . $loggedInUsername);
                }
            }
        }
    } elseif (isset($_FILES['userProfilePic']) && $_FILES['userProfilePic']['error'] !== UPLOAD_ERR_NO_FILE) {
        switch ($_FILES['userProfilePic']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE: $uploadError = "A fájl mérete túl nagy."; break;
            case UPLOAD_ERR_PARTIAL: $uploadError = "A fájl csak részben töltődött fel."; break;
            case UPLOAD_ERR_CANT_WRITE: $uploadError = "Hiba a fájl lemezre írása közben."; break;
            default: $uploadError = 'Ismeretlen hiba a fájlfeltöltés során (kód: ' . $_FILES['userProfilePic']['error'] . ').';
        }
        error_log("File upload error for user {$loggedInUsername}: code " . $_FILES['userProfilePic']['error']);
    }

    if ($uploadError) {
        $response['message'] = $uploadError;
        echo json_encode($response);
        exit;
    }

    $styleUpdateNeeded = false;
    if ($newFontType !== null && $newFontType !== $currentFont) $styleUpdateNeeded = true;
    if ($newBgColor !== null && $newBgColor !== $currentBgColor) $styleUpdateNeeded = true;
    if ($newVideoBorderCombined !== null && ($newBorderColor1 !== $currentBorderColor1 || $newBorderColor2 !== $currentBorderColor2)) $styleUpdateNeeded = true;


    if ($picUpdateNeeded || $styleUpdateNeeded) {
        $pdo->beginTransaction();
        try {
            $actionsPerformed = [];

            if ($picUpdateNeeded && $newProfilePicPath !== null) {
                 $oldPicPhysicalPath = realpath($uploadDir . basename($currentProfilePic));
                 if ($currentProfilePic &&
                     $currentProfilePic !== $defaultProfilePic &&
                     $oldPicPhysicalPath &&
                     strpos($oldPicPhysicalPath, realpath($uploadDir)) === 0 &&
                     file_exists($oldPicPhysicalPath))
                 {
                    @unlink($oldPicPhysicalPath);
                 }

                $stmtUpdatePic = $pdo->prepare("UPDATE users SET profilepic = ? WHERE id = ?");
                if($stmtUpdatePic->execute([$newProfilePicPath, $currentUserId])){
                    $actionsPerformed[] = "Profilkép frissítve";
                    $_SESSION["profilepic_in"] = $newProfilePicPath;
                } else {
                     throw new Exception("Profilkép adatbázis frissítése sikertelen.");
                }
            }
            if ($styleUpdateNeeded) {
                $finalFont = $newFontType ?? $currentFont;
                $finalBgColor = $newBgColor ?? $currentBgColor;
                $finalBorderCombined = $newVideoBorderCombined ?? ($currentBorderColor1 . ';' . $currentBorderColor2);
                if ($currentCustomizationStyleCode && $currentCustomizationStyleCode !== $defaultStyleCode) {
                    $stmtUpdateStyle = $pdo->prepare("UPDATE customization SET fontfamilyType = ?, backgroundColor = ?, videoborderColor = ? WHERE style_code = ?");
                    if(!$stmtUpdateStyle->execute([$finalFont, $finalBgColor, $finalBorderCombined, $currentCustomizationStyleCode])){
                        throw new Exception("Meglévő egyedi stílus frissítése sikertelen.");
                    }
                    $actionsPerformed[] = "Stílus frissítve";
                } else {
                    $newGeneratedStyleCode = bin2hex(random_bytes(5));
                    $stmtInsertStyle = $pdo->prepare("INSERT INTO customization (style_code, fontfamilyType, backgroundColor, videoborderColor) VALUES (?, ?, ?, ?)");
                    if($stmtInsertStyle->execute([$newGeneratedStyleCode, $finalFont, $finalBgColor, $finalBorderCombined])) {
                        $stmtUpdateUserStyle = $pdo->prepare("UPDATE users SET style_code = ? WHERE id = ?");
                        if(!$stmtUpdateUserStyle->execute([$newGeneratedStyleCode, $currentUserId])){
                             throw new Exception("Felhasználó stílus kódjának frissítése sikertelen az új stílus hozzárendelésekor.");
                        }
                        $actionsPerformed[] = "Új egyedi stílus létrehozva és hozzárendelve";
                        $_SESSION["style_code"] = $newGeneratedStyleCode;
                    } else {
                         throw new Exception("Új stílus beszúrása sikertelen a customization táblába.");
                    }
                }
                 $_SESSION["bkgColor"] = $finalBgColor;
                 $_SESSION["fontFamily"] = $finalFont;
                 $finalBorderParts = explode(';', $finalBorderCombined);
                 $_SESSION["firstvideoborderColor"] = $finalBorderParts[0] ?? '#be0606';
                 $_SESSION["secondvideoborderColor"] = $finalBorderParts[1] ?? ($_SESSION["firstvideoborderColor"] ?? '#7a0202');
            }

            $pdo->commit();
            $response['success'] = true;
            if (!empty($actionsPerformed)) {
                 $response['message'] = implode(" és ", $actionsPerformed) . "!";
                 if ($picUpdateNeeded && $newProfilePicPath !== null) {
                     $response['newImageUrl'] = $newProfilePicPath;
                 } else {
                     unset($response['newImageUrl']);
                 }
            } else {
                 $response['message'] = 'Nem történt módosítandó adat.';
                 unset($response['newImageUrl']);
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
               $pdo->rollBack();
            }
            $response['message'] = "Hiba a mentés során: " . $e->getMessage();
            error_log("Customization update failed for user " . $loggedInUsername . ": " . $e->getMessage());
             if ($picUpdateNeeded && isset($destination) && file_exists($destination)){
                 @unlink($destination);
             }
             $response['newImageUrl'] = null;
             $response['success'] = false;
        }

    } else {
        $response['success'] = true;
        $response['message'] = 'Nem történt módosítandó adat.';
        unset($response['newImageUrl']);
    }

} else {
    $response['message'] = 'Hibás kérés (nem POST).';
    $response['success'] = false;
}

echo json_encode($response);
exit;

?>