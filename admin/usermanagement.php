<?php
session_start();
require '../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$dbhost = $_ENV['DB_SERVERNAME'];
$dbname = $_ENV['DB_NAME'];
$dbusername = $_ENV['DB_USERNAME'];
$dbpassword = $_ENV['DB_PASSWORD'];

if (isset($_SESSION["username_in"]) && isset($_SESSION["userplan_status"])) {
    $sqlconn1 = new mysqli($dbhost, $dbusername, $dbpassword, $dbname);

    if ($sqlconn1->connect_error) {
        die("Connection failed: " . $sqlconn1->connect_error);
    }

    if (isset($_GET['delete_username'])) {
        $delete_username = $_GET['delete_username'];
        $delete_sql = "DELETE FROM users WHERE username = ?";
        $stmt = $sqlconn1->prepare($delete_sql);
        $stmt->bind_param("s", $delete_username);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>User $delete_username deleted successfully.</p>";
        } else {
            echo "<p style='color: red;'>Error deleting user: " . $sqlconn1->error . "</p>";
        }

        $stmt->close();
    }

    if (isset($_GET['make_admin_username'])) {
        $make_admin_username = $_GET['make_admin_username'];
        $make_admin_sql = "UPDATE users SET plan = 3 WHERE username = ?";
        $stmt = $sqlconn1->prepare($make_admin_sql);
        $stmt->bind_param("s", $make_admin_username);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>User $make_admin_username is now an admin.</p>";
        } else {
            echo "<p style='color: red;'>Error updating user: " . $sqlconn1->error . "</p>";
        }

        $stmt->close();
    }

    $getusersfromsql = "SELECT id, username, name, email, plan FROM users";
    $usersfromsqltable = $sqlconn1->query($getusersfromsql);

    if ($usersfromsqltable->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Felhasználónév</th>";
        echo "<th>Név</th>";
        echo "<th>E-mail</th>";
        echo "<th>Csomag</th>";
        echo "<th>Törlés</th>";
        echo "<th>Admin</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($userrowsfromsqltable = $usersfromsqltable->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($userrowsfromsqltable['id']) . "</td>";
            echo "<td>" . htmlspecialchars($userrowsfromsqltable['username']) . "</td>";
            echo "<td>" . htmlspecialchars($userrowsfromsqltable['name']) . "</td>";
            echo "<td>" . htmlspecialchars($userrowsfromsqltable['email']) . "</td>";
            echo "<td>" . htmlspecialchars($userrowsfromsqltable['plan']) . "</td>";
            echo "<td><a href='?delete_username=" . htmlspecialchars($userrowsfromsqltable['username']) . "' onclick=\"return confirm('Biztosan törölni szeretnéd ezt a felhasználót?');\">Törlés</a></td>";
            if ($userrowsfromsqltable['plan'] == 3) {
                echo "<td>Admin</td>";
            } else {
                echo "<td><a href='?make_admin_username=" . htmlspecialchars($userrowsfromsqltable['username']) . "' onclick=\"return confirm('Biztosan adminná szeretnéd tenni ezt a felhasználót?');\">Hozzáadás</a></td>";
            }

            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "Felhasználók nem találhatóak!";
    }

    $sqlconn1->close();
}
?>
