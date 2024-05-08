<?php
require 'cookies.php';
require 'lib/eng.tpl.php';
include_once 'data/db.connection.php';

// Check if user is logged in, if not then redirect back to index.php
session_start();
if (isset($_SESSION["isLoggedInUser"])) {
    // Check if session has username associated with session
    if (!isset($_SESSION["username"])) {
        // Faulty session -> destroy it
        session_unset();
        session_destroy();
        header("Location: index.php");
    }
} else if (checkCookieForRememberMe()) {
    // Set session data based on cookie
    if ($username = getUsernameFromCookie($_COOKIE["rememberMe"])) {
        $_SESSION["username"] = $username;
        $_SESSION["isLoggedInUser"] = "true";
    }
} else {
    // User is redirected to index page
    header("Location: index.php");
}

// When the user clicks the logout button, destroy session and remove cookies, throw user to index page
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["logoutAction"]) && $_POST["logoutAction"] == "true") {
        session_unset();
        session_destroy();
        removeUserCookie();
        header("Location: index.php");
    }
}

// Get user id of profile from database
function getIdOfSessionUser($username)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT id FROM profiles WHERE username=?";
    $query = $mysqli->prepare($query);
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    return $row["id"];
}

class Folder
{
    public $title;
    public $taskTitles;

    public $id;

    function __construct($title, $taskTitles, $id)
    {
        $this->title = $title;
        $this->taskTitles = $taskTitles;
        $this->id = $id;
    }
}

/*
Make folder objects based on folders found in database and return them
*/
function getFolderIdArrayByUserId($userId)
{
    $folders = [];
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT * FROM folders INNER JOIN `profiles-folders` ON `folders`.`Id` = `profiles-folders`.`folderId` WHERE `profiles-folders`.`profileId` = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();
    while ($row = mysqli_fetch_assoc($result)) {
        // Select tasks associated with each profile
        $query = "SELECT * FROM `tasks` INNER JOIN `folders-tasks` ON `tasks`.`Id` = `folders-tasks`.`taskId` WHERE `folders-tasks`.`folderId` = ?";
        $query = $mysqli->prepare($query);
        $query->bind_param("i", $row["id"]);
        $query->execute();
        $result2 = $query->get_result();
        $taskTitles = [];
        while ($row2 = mysqli_fetch_assoc($result2)) {
            array_push($taskTitles, $row2["title"]);
        }
        $folder = new Folder($row["title"], $taskTitles, $row["id"]);
        array_push($folders, $folder);
    }
    return $folders;
}

/*
Return array of folder's usernames based on folder id
*/
function getFolderMembersDict($id)
{
    $usernames = array();
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT * FROM `profiles` INNER JOIN `profiles-folders` ON `profiles`.`Id` = `profiles-folders`.`profileId` WHERE `profiles-folders`.`folderId` = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    while ($row = mysqli_fetch_assoc($result)) {
        $usernames[$row['id']] = $row['username'];
    }
    return $usernames;
}

function getProfilePicture($id)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT profilePictureId FROM profiles WHERE id=? ";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    return $row["profilePictureId"];
}

/*
Return boolean flag of user ownership of folder
*/
function checkIfUserIsOwnerOfFolder($folderId, $userId)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT * FROM folders WHERE id = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $folderId);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    if ($row["owner"] == $userId) {
        return true;
    } else {
        return false;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>OurSite - Main Menu</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <script src="scripts/script.js" defer></script>
    <script src="scripts/errormessage.js" defer></script>
    <meta name="description" content="OurSite - Look at your or your team's currents tasks for today
    and make yourself free productive every day!">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="geolee">
    <meta name="author" content="stlill">
    <meta name="author" content="inviin">
</head>

<body>
    <header>
        <a href="index.php" class="logo"></a>
        <nav class="header-right">
            <a href="index.php">Home</a>
            <a class="active" href="main.php">Main</a>
            <a href="profile.php">Account Details</a>
            <button id="logout-button" class="logout-button" onclick="showLogOut()">Log Out</button>

            <div id="logout-prompt" class="confirm">
                <div class="confirm-content">
                    <p>Are you sure you want to log out?</p>
                    <table>
                        <form method="post" class="button-container">
                            <input type="hidden" id="logout" name="logoutAction" value="true">
                            <td><input type="submit" id="confirm-logout" value="Yes"></td>
                        </form>
                        <td><button id="cancel-logout" onclick="hideLogOut()">No</button></td>
                    </table>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <div>
            <h1>Take a look at your tasks for today</h1>
        </div>
        <div class="add-folder-button">
            <button onclick="showPopup()">+</button>
        </div>

        <div class="error-popup" id="error-popup">
            <span class="error-close" onclick="hideError()">&times;</span>
            <div class="error-message" id="error-message"></div>
        </div>

        <div id="popup" class="popup">
            <span class="close" onclick="hidePopup()">&times;</span>
            <form method="post" action="createfolder.php">
                <label for="folderTitle" class="required">Folder Title</label><br>
                <input type="text" id="folderTitle" name="folderTitle" placeholder="Group project" required pattern="[A-Za-z0-9!@#$%^&*()_+-]+" maxlength="30"><br><br>

                <label for="folderMembers">Folder Members</label><br>
                <label for="folderMembers" id="comma-separator-text">(use a comma for separator)</label><br>
                <input type="text" id="folderMembers" name="folderMembers" placeholder="geolee, inviin" pattern="[A-Za-z0-9!@#$%^&*()_+-]+"><br><br>

                <input type="submit" name="submit" id="submit" value="Create Folder" class="submit-button">
            </form>
        </div>

        <div class="folder-container">
            <?php
            $username = $_SESSION["username"];
            //Get id of session user
            $userId = getIdOfSessionUser($username);
            //Get array of folder id's associated with user
            $folderArray = getFolderIdArrayByUserId($userId);

            /*
            Main loop for creating folders on the user's main page
            */
            foreach ($folderArray as $folder) {
                $noTaskTitles = true;
                $emptyTasksMessage = 'No tasks yet';

                $folderTitle = $folder->title;

                $membersDict = getFolderMembersDict($folder->id);
                $showMembers = '';

                foreach ($membersDict as $id => $profileUsername) {

                    $profilePicture = getProfilePicture($id);
                    if ($profilePicture == null) {
                        $showMembers .= '<img class="folder-icon" src="img/user-icon.png" alt="' . $profileUsername . '">';
                    } else {
                        $showMembers .= '<img class="folder-icon" src="img/profiles/' . $profilePicture . '.jpg" alt="' . $profileUsername . '">';
                    }
                }



                $nothingMessage = 'No tasks yet';
                /*
                Create html input for a folder's description of tasks
                */
                $taskListHtml = '';
                $taskTitleCounter = 0;
                foreach ($folder->taskTitles as $title) {
                    $noTaskTitles = false;
                    $taskListHtml .= "<li>$title</li>";
                    $taskTitleCounter++;
                    if ($taskTitleCounter > 1) {
                        break;
                    }
                }

                // Template of settings for owner
                $settings = '<div id="settings_{FOLDER_ID}" class="settings">
                    <span class="close" onclick="hideSettings({FOLDER_ID})">&times;</span>
                    <h3>Modify the folder</h3>
                    <form method="post" action="modifyfolder.php">
                        <input type="hidden" name="folderId" value={FOLDER_ID}>
                        <label for="folderTitle">Folder Title</label><br>
                        <input type="text" id="folderTitle" name="folderTitle" placeholder="Group project" pattern="[A-Za-z0-9!@#$%^&*()_+-]+" maxlength="30"><br><br>
                
                        <label for="folderMembers">Folder Members</label><br>
                        <input type="text" id="folderMembers" name="folderMembers" placeholder="geolee, inviin" pattern="[A-Za-z0-9!@#$%^&*()_+-]+"><br><br>
                
                        <input type="submit" name="submit" id="submit" value="Modify Folder" class="submit-button">
                    </form>
                    <form method="post" action="modifyfolder.php">
                        <input type="hidden" name="folderId" value={FOLDER_ID}>
                        <input type="submit" name="submit" id="submit" value="Delete Folder" class="delete-button">
                    </form>
                </div>';
                $showSettings = checkIfUserIsOwnerOfFolder($folder->id, $userId);
                $settings = str_replace("{FOLDER_ID}", $folder->id, $settings);
                /*
                Assignment of variables to template
                */
                $template = new Template('templates/folder_tpl.php');
                $template->assign("show_settings", $showSettings);
                $template->assign("settings", $settings);
                $template->assign("nothing", $noTaskTitles);
                $template->assign("members", $showMembers);
                $template->assign("title", $folderTitle);
                $template->assign("tasks", $taskListHtml);
                $template->assign("nothing_message", $emptyTasksMessage);
                $template->assign("folder_id", $folder->id);

                echo $template->render();
            }
            ?>
        </div>
    </main>
    <footer>
        <p>Copyright © 2024 OurSite</p>
        <a href="privacy_policy.php">Privacy policy</a>
        <a href="terms_and_conditions.php">Terms & Conditions</a>
    </footer>
    <script src="scripts/logout.js" defer></script>
</body>

</html>