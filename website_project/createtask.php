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

// Function to check if the status is in the array
function isValidStatus($status)
{
    $validStatus = array("urgent", "medium", "low");
    return in_array($status, $validStatus);
}

// Removes command characters
function validateInput($input)
{
    return htmlspecialchars(trim($input));
}

// Check if date is valid
function validDate($date)
{
    // Date is valid when year starts with 20 and ends with 2 numbers, month is max 12 and days is max 31.
    $pattern = '/^20\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/';

    // Make the year, month, day separate variables and check the validity with checkdate().
    list($year, $month, $day) = explode('-', $date);

    // Check if the input date is valid.
    if (preg_match($pattern, $date) && checkdate($month, $day, $year)) {
        return true;
    } else {
        return false;
    }
}

/*
Inserts task to tasks table and returns new task id
*/
function addTaskToDb($taskTitle, $taskDescription, $status, $taskDate){
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    // Add task to tasks table
    $query = "INSERT INTO tasks (title, description, status, date) VALUES (?, ?, ?, ?)";
    $query = $mysqli->prepare($query);
    $taskTitle = sanitizeInputVar($mysqli, $taskTitle);
    $taskDescription = sanitizeInputVar($mysqli, $taskDescription);
    $status = sanitizeInputVar($mysqli, $status);
    $taskDate = sanitizeInputVar($mysqli, $taskDate);
    $query->bind_param("ssss", $taskTitle, $taskDescription, $status, $taskDate);
    if (!$query->execute()){
        echo "Error occured while adding task";
    }
    return $query->insert_id;
}

/*
Inserts an entry into link table for folderId and taskId
*/
function addEntryToLinkTable($folderId, $taskId){
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    // Add ids into link table
    $query = "INSERT INTO `folders-tasks` (folderId, taskId) VALUES (?, ?)";
    $query = $mysqli->prepare($query);
    $query->bind_param("ii", $folderId, $taskId);
    if (!$query->execute()){
        echo "Error occured while adding to folders-tasks";
    }
    return;
}

// Check for title
function titleValidation($str) {
    if (strlen($str) > 30) {
        return true;
    } else {
        return false;
    }
}

// Check for description
function descriptionValidation($str) {
    if (strlen($str) > 150) {
        return true;
    } else {
        return false;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"]) && $_POST["submit"] == "Create Task" && !empty($_POST['taskTitle']) && !empty($_POST['taskDescription']) && !empty($_POST['taskDate']) && !empty($_POST['status'])) {
    $newTaskTitle = validateInput($_POST['taskTitle']);
    $newTaskDescription = validateInput($_POST['taskDescription']);
    $newTaskStatus = validateInput($_POST['status']);
    $newTaskDate = validateInput($_POST['taskDate']);
    $folderId = $_SESSION["folderid"];

    if (!isValidStatus($newTaskStatus)) {
        header("Location: folder.php?error=3");
        exit();
    } elseif (!validDate($newTaskDate)) {
        header("Location: folder.php?error=4");
        exit();
    } elseif (titleValidation($newTaskTitle)) {
        header("Location: folder.php?error=6");
        exit();
    } elseif (descriptionValidation($newTaskDescription)) {
        header("Location: folder.php?error=7");
        exit();
    } else {
        // Add task entry
        $taskId = addTaskToDb($newTaskTitle, $newTaskDescription, $newTaskStatus, $newTaskDate);

        // Add entry to link table
        addEntryToLinkTable($folderId, $taskId);

        header("Location: folder.php");
        exit();
    }
} else {
    header("Location: folder.php?error=1");
    exit();
}