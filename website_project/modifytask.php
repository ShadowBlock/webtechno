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

// Retrieve info about task by id
function getTaskById($taskId)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT * FROM tasks WHERE id = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $taskId);
    $query->execute();
    $result = $query->get_result();
    return $result->fetch_assoc();
}

// Check if task name needs to be changed
function validateTaskNameChange($taskId, $currentTaskTitle, $newTaskTitle)
{
    if ($$currentTaskTitle != $newTaskTitle) {
        global $server, $user, $password, $database;
        $mysqli = new mysqli($server, $user, $password, $database);
        if ($mysqli->connect_error) {
            die("DB connection failed: " . $mysqli->connect_error);
        }
        $query = "UPDATE tasks SET title = ? WHERE id = ?";
        $query = $mysqli->prepare($query);
        $newTaskTitle = sanitizeInputVar($mysqli, $newTaskTitle);
        $query->bind_param("si", $newTaskTitle, $taskId);
        $query->execute();
    }
}

// Check if the task description needs to be changed
function valdiateTaskDescriptionChange($taskId, $currentTaskDescription, $newTaskDescription)
{
    if ($currentTaskDescription != $newTaskDescription) {
        global $server, $user, $password, $database;
        $mysqli = new mysqli($server, $user, $password, $database);
        if ($mysqli->connect_error) {
            die("DB connection failed: " . $mysqli->connect_error);
        }
        $query = "UPDATE tasks SET description = ? WHERE id = ?";
        $query = $mysqli->prepare($query);
        $newTaskDescription = sanitizeInputVar($mysqli, $newTaskDescription);
        $query->bind_param("si", $newTaskDescription, $taskId);
        $query->execute();
    }
}

// Check if task status needs to be changed
function validateTaskStatusChange($taskId, $currentTaskStatus, $newTaskStatus)
{
    if ($currentTaskStatus != $newTaskStatus) {
        global $server, $user, $password, $database;
        $mysqli = new mysqli($server, $user, $password, $database);
        if ($mysqli->connect_error) {
            die("DB connection failed: " . $mysqli->connect_error);
        }
        $query = "UPDATE tasks SET status = ? WHERE id = ?";
        $query = $mysqli->prepare($query);
        $newTaskStatus = sanitizeInputVar($mysqli, $newTaskStatus);
        $query->bind_param("si", $newTaskStatus, $taskId);
        $query->execute();
    }
}

function validateTaskDateChange($taskId, $currentTaskDate, $newTaskDate)
{
    if ($currentTaskDate != $newTaskDate) {
        global $server, $user, $password, $database;
        $mysqli = new mysqli($server, $user, $password, $database);
        if ($mysqli->connect_error) {
            die("DB connection failed: " . $mysqli->connect_error);
        }
        $query = "UPDATE tasks SET date = ? WHERE id = ?";
        $query = $mysqli->prepare($query);
        $newTaskDate = sanitizeInputVar($mysqli, $newTaskDate);
        $query->bind_param("si", $newTaskDate, $taskId);
        $query->execute();
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

// Check for description
function descriptionValidation($str) {
    if (strlen($str) > 150) {
        return true;
    } else {
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"]) && $_POST["submit"] == "Modify Task") {
    $newTaskTitle = isset($_POST['taskTitle']) ? validateInput($_POST['taskTitle']) : null;
    $newTaskDescription = isset($_POST['taskDescription']) ? validateInput($_POST['taskDescription']) : null;
    $newTaskStatus = isset($_POST['status']) ? validateInput($_POST['status']) : null;
    $newTaskDate = isset($_POST['taskDate']) ? validateInput($_POST['taskDate']) : null;
    $taskId = $_POST['taskId'];
    if (!isValidStatus($newTaskStatus)) {
        header("Location: folder.php?error=3");
        exit();
    } elseif (!($newTaskDate == null)) {
        if (!validDate($newTaskDate)) {
            header("Location: folder.php?error=4");
            exit();
        }
    } elseif (titleValidation($newTaskTitle)) {
        header("Location: folder.php?error=6");
        exit();
    } elseif (descriptionValidation($newTaskDescription)) {
        header("Location: folder.php?error=7");
        exit();
    }
    $currentTaskInfo = getTaskById($taskId);
    if (!($newTaskTitle == null)) {
        validateTaskNameChange($taskId, $currentTaskInfo["title"], $newTaskTitle);
    }
    if (!($newTaskDescription == null)) {
        valdiateTaskDescriptionChange($taskId, $currentTaskInfo["description"], $newTaskDescription);
    }
    if (!($newTaskStatus == null)) {
        validateTaskStatusChange($taskId, $currentTaskInfo["status"], $newTaskStatus);
    }
    if (!($newTaskDate == null)) {
        validateTaskDateChange($taskId, $currentTaskInfo["date"], $newTaskDate);
    }
    header("Location: folder.php");
    exit();

} else {
    header("Location: folder.php?error=1");
    exit();
}