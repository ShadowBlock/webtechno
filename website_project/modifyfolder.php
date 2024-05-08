<?php
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

// Removes command characters
function validateInput($input)
{
    return htmlspecialchars(trim($input));
}

// Check if a username exists in our database
function checkUsernameExists($username)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT username FROM profiles WHERE username = ?";
    $query = $mysqli->prepare($query);
    $username = sanitizeInputVar($mysqli, $username);
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}

// Get current title of folder and change if are different
function validateFolderNameChange($folderId, $folderTitle)
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
    if ($folderTitle != $row["title"]) {
        $query = "UPDATE folders SET title = ? WHERE id = ?";
        $query = $mysqli->prepare($query);
        $folderTitle = sanitizeInputVar($mysqli, $folderTitle);
        $query->bind_param("si", $folderTitle, $folderId);
        $query->execute();
    }
}

// Return array of members of a given folder
function getListOfFolderMemberById($folderId)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT * FROM profiles INNER JOIN `profiles-folders` ON profiles.Id = `profiles-folders`.profileId WHERE `profiles-folders`.folderId = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $folderId);
    $query->execute();
    $result = $query->get_result();
    $members = [];
    while ($row = $result->fetch_assoc()) {
        array_push($members, $row["username"]);
    }
    return $members;
}

// Remove member from link table
function removeMemberFromFolderByUsername($member, $folderId)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT * FROM profiles WHERE username = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("s", $member);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    $profileId = $row["id"];
    $query = "DELETE FROM `profiles-folders`
    WHERE `profiles-folders`.profileId = ? AND `profiles-folders`.folderId = ?;";
    $query = $mysqli->prepare($query);
    $query->bind_param("ii", $profileId, $folderId);
    $query->execute();
}

// Add members in array to link table
function addRemainingMembersToFolderMembers($toBeAddedMembersArray, $folderId)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    foreach ($toBeAddedMembersArray as $member) {
        $query = "SELECT * FROM profiles WHERE username = ?";
        $member = sanitizeInputVar($mysqli, $member);
        $query = $mysqli->prepare($query);
        $query->bind_param("s", $member);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        $profileId = $row["id"];
        $query = "INSERT INTO `profiles-folders` (profileId, folderId) VALUES (?, ?)";
        $query = $mysqli->prepare($query);
        $query->bind_param("ii", $profileId, $folderId);
        $query->execute();
    }
}

// Remove links between given folder and accounts
function removeAssociatedProfiles($folderId)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "DELETE FROM `profiles-folders` WHERE folderId = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $folderId);
    $query->execute();
}

// Remove links between folder and tasks

function removeAssociatedTasksAndFolder($folderId)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $relatedTasksArray = [];
    $query = "SELECT * FROM `folders-tasks` WHERE folderId = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $folderId);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        array_push($relatedTasksArray, $row['taskId']);
    }
    // Delete all links between given folder and tasks
    $query = "DELETE FROM `folders-tasks` WHERE folderId = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $folderId);
    $query->execute();
    $result = $query->get_result();
    // Loop through all task ids and delete them
    foreach ($relatedTasksArray as $taskId) {
        $query = "DELETE FROM tasks WHERE id = ?";
        $query = $mysqli->prepare($query);
        $query->bind_param("i", $taskId);
        $query->execute();
    }
    // Remove folder
    $query = "DELETE FROM folders WHERE id = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $folderId);
    $query->execute();
}

// Check for title
function titleValidation($str) {
    if (strlen($str) > 30) {
        return true;
    } else {
        return false;
    }
}

// Check if form is submitted for deleting
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["submit"] == "Delete Folder" && $_POST["folderId"]) {
    removeAssociatedProfiles($_POST["folderId"]);
    removeAssociatedTasksAndFolder($_POST["folderId"]);
    header("Location: main.php");
    exit();
}

// Check if form is submitted for modification
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["submit"] == "Modify Folder" && $_POST["folderId"]) {
    // Retrieve values from form
    $folderTitle = isset($_POST['folderTitle']) ? validateInput($_POST['folderTitle']) : null;
    // Check if members are provided, if not set to empty string
    $folderMembers = isset($_POST['folderMembers']) ? validateInput($_POST['folderMembers']) : null;
    $folderId = $_POST['folderId'];
    $folderOwner = $_SESSION["username"];
    $newMembersArray = [];

    if (titleValidation($folderTitle)) {
        header("Location: main.php?error=6");
        exit();
    }

    if (!($folderMembers == null)) {
        // Translate string to array
        $newMembersArray = explode(",", $folderMembers);

        // Check if input is empty
        if (empty($newMembersArray)) {
            header("Location: main.php?error=2");
            exit();
        }

        // Check if given members exist
        foreach ($newMembersArray as $member) {
            if (!checkUsernameExists($member)) {
                header("Location: main.php?error=2");
                exit();
            }
        }
    }

    if (!in_array($folderOwner, $newMembersArray)) {
        array_push($newMembersArray, $folderOwner);
    }

    if ($folderTitle) {
        // Change folder name if there was a name change
        validateFolderNameChange($folderId, $folderTitle);
    }

    // Get list of current members
    $toBeAddedMembersArray = $newMembersArray;
    $currentFolderMembers = getListOfFolderMemberById($folderId);


    /*
    Add new members and remove unwanted members
    */
    foreach ($currentFolderMembers as $member) {
        if (in_array($member, $newMembersArray)) {
            $index = array_search($member, $newMembersArray);
            array_splice($toBeAddedMembersArray, $index, null);
            continue;
        } else {
            removeMemberFromFolderByUsername($member, $folderId);
            $index = array_search($member, $newMembersArray);
            array_splice($toBeAddedMembersArray, $index, null);
        }
    }
    if ($toBeAddedMembersArray) {
        addRemainingMembersToFolderMembers($toBeAddedMembersArray, $folderId);
    }
    header("Location: main.php");
    exit();

} else if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["submit"] == "Modify Folder") {
    header("Location: main.php?error=5");
    exit();
}
