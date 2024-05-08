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
    $query->bind_param("s", $username); // Binding parameters
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}

// Insert folder information into db
function insertFolderIntoDb($folderOwnerUsername, $folderTitle, $membersArray)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    // Get id of username for folder ownership
    $query = "SELECT id FROM profiles WHERE username = ?";
    $query = $mysqli->prepare($query);
    $username = sanitizeInputVar($mysqli, $folderOwnerUsername);
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    $ownerId = $row["id"];

    // Insert new entry into folders db
    $query = "INSERT INTO folders (title, owner) VALUES (?, ?)";
    $query = $mysqli->prepare($query);
    $folderTitle = sanitizeInputVar($mysqli, $folderTitle);
    $query->bind_param("si", $folderTitle, $ownerId);
    $query->execute();
    if (!$query->get_result()) {
        echo "Error associated with inserting folder into db";
    }
    $idOfFolder = $query->insert_id;
    // Insert links into link table
    foreach ($membersArray as $member) {
        $query = "SELECT id FROM profiles WHERE username = ?";
        $query = $mysqli->prepare($query);
        $query->bind_param("s", $member);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();

        $query = "INSERT INTO `profiles-folders` (profileId, folderId) VALUES (?, ?)";
        $query = $mysqli->prepare($query);
        $query->bind_param("ii", $row["id"], $idOfFolder);
        $query->execute();
        if (!$query->get_result()){
            echo "Error associated with inserting link value";
        }
    }
}

// Check for title
function titleValidation($str) {
    if (strlen($str) > 30) {
        return true;
    } else {
        return false;
    }
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["submit"] == "Create Folder" && !empty($_POST['folderTitle'])) {
    // Retrieve values from form

    $folderTitle = validateInput($_POST['folderTitle']);
    // Check if members are provided, if not set to empty string
    $folderMembers = isset($_POST['folderMembers']) ? validateInput($_POST['folderMembers']) : '';
    $folderOwner = $_SESSION['username'];

    // Translate string to array
    $membersArray = explode(",", $folderMembers);
    $membersArray = array_map('trim', $membersArray);

    if (titleValidation($folderTitle)) {
        header("Location: main.php?error=6");
        exit();
    }

    // Check if member input is empty
    if (empty($membersArray) || empty($folderMembers)) {
        $membersArray = [$folderOwner];
        insertFolderIntoDb($folderOwner, $folderTitle, $membersArray);
        header("Location: main.php");
        exit();
    }

    // Check if given members exist
    foreach ($membersArray as $member) {
        if (!checkUsernameExists($member)) {
            header("Location: main.php?error=2");
            exit();
        }
    }

    // Check if owner exists in the array of members, add it
    if (!in_array($folderOwner, $membersArray)) {
        $membersArray[] = $folderOwner;
    }

    insertFolderIntoDb($folderOwner, $folderTitle, $membersArray);
    header("Location: main.php");
    exit();

} else {
    header("Location: main.php?error=1");
    exit();
}