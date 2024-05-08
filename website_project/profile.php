<?php

require 'cookies.php';
require 'lib/eng.tpl.php';
include_once 'data/db.connection.php';

session_start();
if (isset($_SESSION["isLoggedInUser"])) {
    //Check if session has username associated with session
    if (!isset($_SESSION["username"])) {
        //Faulty session -> destroy it
        session_unset();
        session_destroy();
        header("Location: index.php");
    }
} else if (checkCookieForRememberMe()) {
    //Set session data based on cookie
    if ($username = getUsernameFromCookie($_COOKIE["rememberMe"])) {
        $_SESSION["username"] = $username;
        $_SESSION["isLoggedInUser"] = "true";
    }
} else {
    //User is redirected to index page
    header("Location: index.php");
}

class Task
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
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
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Select tasks associated with each profile
        $query_tasks = "SELECT * FROM `tasks` INNER JOIN `folders-tasks` ON `tasks`.`Id` = `folders-tasks`.`taskId` WHERE `folders-tasks`.`folderId` = ?";
        $stmt_tasks = $mysqli->prepare($query_tasks);
        $stmt_tasks->bind_param("i", $row["id"]);
        $stmt_tasks->execute();
        $result_tasks = $stmt_tasks->get_result();
        $taskTitles = [];
        while ($row_tasks = $result_tasks->fetch_assoc()) {
            array_push($taskTitles, $row_tasks["title"]);
        }
        $folder = new Folder($row["title"], $taskTitles, $row["id"]);
        array_push($folders, $folder);
    }
    return $folders;
}

// Gets the id of the user
function getIdOfSessionUser($username)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT id FROM profiles WHERE username=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row["id"];
}

// Gets the id of the Email
function getEmail($id)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT email FROM profiles WHERE id=? ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row["email"];
}

function getTasksByFolderId($folderId)
{
    $tasks = [];
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }

    // Prepare and execute query to select tasks associated with the folderId
    $query = "SELECT tasks.* FROM tasks INNER JOIN `folders-tasks` ON tasks.id = `folders-tasks`.taskId WHERE `folders-tasks`.folderId = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $folderId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch tasks and add them to the $tasks array
    while ($row = $result->fetch_assoc()) {
        $task = new Task($row["id"]);
        array_push($tasks, $task);
    }

    $stmt->close();
    return $tasks;
}

// Updates the profile picture in the database
function addProfilePicture($username, $profilePictureId)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }

    $id = getIdOfSessionUser($username);

    $query = "SELECT profilePictureId FROM profiles WHERE id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($existingProfilePictureId);
        $stmt->fetch();
        $stmt->close();

        if (!empty($profilePictureId)) {
            $updateQuery = "UPDATE profiles SET profilePictureId=? WHERE id=?";
            $updateStmt = $mysqli->prepare($updateQuery);
            $updateStmt->bind_param("ii", $profilePictureId, $id);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }
    $mysqli->close();
}

// Gets the ID of the profile picture
function getProfilePicture($id)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT profilePictureId FROM profiles WHERE id=? ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row["profilePictureId"];
}

// Deletes the user profile
function deleteProfile($id)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }

    // Loop through folder IDs to delete associated tasks
    $folderIds = getFolderIdArrayByUserId($id);
    foreach ($folderIds as $folderId) {
        $tasks = getTasksByFolderId($folderId->id);
        deleteTasks($tasks);
    }

    // Delete folders associated with the profile
    $query = "DELETE FROM `profiles-folders` WHERE profileId=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Delete folders according to owner
    $query = "DELETE FROM folders WHERE owner=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Delete the profile
    $query = "DELETE FROM profiles WHERE id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

function deleteTasks($tasks)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }

    // Delete tasks from folders-tasks table
    $query = $mysqli->prepare("DELETE FROM `folders-tasks` WHERE taskId=?");

    foreach ($tasks as $task) {
        $taskId = $task->id;
        $query->bind_param("i", $taskId);
        $query->execute();
    }

    $query->close();

    // Delete tasks from tasks table
    $query = $mysqli->prepare("DELETE FROM tasks WHERE id=?");

    foreach ($tasks as $task) {
        $taskId = $task->id;
        $query->bind_param("i", $taskId);
        $query->execute();
    }

    $query->close();
}

// Data handling
$username = $_SESSION["username"];
$userId = getIdOfSessionUser($username);
$userEmail = getEmail($userId);
$profilePictureId = getProfilePicture($userId);
$profileImageIds = [1, 2, 3, 4, 5, 6, 7, 8];
$profilePictures = '';

foreach ($profileImageIds as $imageId) {
    $isSelected = ($imageId == $profilePictureId) ? 'class="selected-profile-img"' : '';
    $profilePictures .= '<img id="profile-pic-' . $imageId . '" onclick="setProfileId(' . $imageId . ')" ' . $isSelected . ' src="img/profiles/' . $imageId . '.jpg" alt="Profile Image ' . $imageId . '"><br>';
}

// Checks for logout, delete profile and selected image
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['selected-profile-id'])) {
        $profilePictureId = $_POST['selected-profile-id'];
        addProfilePicture($username, $profilePictureId);
        echo '<script>addBorder(' . $profilePictureId . ');</script>';
    }
    if (isset($_POST["logoutAction"]) && $_POST["logoutAction"] === "true") {
        session_unset();
        session_destroy();
        removeUserCookie();
        header("Location: index.php");
    }
    if (isset($_POST['deleteAction']) && $_POST["deleteAction"] === "true") {
        session_unset();
        session_destroy();
        removeUserCookie();
        deleteProfile($userId);
        header("Location: index.php");
        exit();
    }
}


$template = new Template('templates/profile_tpl.php');
$template->assign("username", $username);
$template->assign("email", $userEmail);
$template->assign("profile_images", $profilePictures);

echo $template->render();
