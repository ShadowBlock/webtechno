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

// Find out the folderid based on post or session, else return user back to main
$folderId = 0;
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["action"]) && isset($_POST["taskid"])) {
        // Deletes the task if the user clicked "close"
        if ($_POST["action"] == "delete") {
            deleteTask($_POST["taskid"], $_SESSION["folderid"]);
        }
    } else if (isset($_POST["folderid"])) {
        $folderId = $_POST["folderid"];
        $_SESSION["folderid"] = $folderId;
    } else if (isset($_SESSION["folderid"])) {
        $folderId = $_SESSION["folderid"];
    } else {
        // No folder is active, return user to main.php
        header("Location: main.php");
    }
}
$folderId = $_SESSION["folderid"];


/*
Find tasks from folderid and create task objects for display
*/
function getTasksFromFolderId($folderId)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT * FROM tasks INNER JOIN `folders-tasks` ON tasks.Id = `folders-tasks`.taskId WHERE `folders-tasks`.folderId = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $folderId);
    $query->execute();
    $tasksArray = [];
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()){
        array_push($tasksArray, new Task($row["title"], $row["description"], $row["status"], $row["date"], $row["id"]));
    }
    return $tasksArray;
}

class Task
{
    public $title;
    public $description;
    public $status;
    public $date;
    public $id;

    function __construct($title, $description, $status, $date, $id)
    {
        $this->title = $title;
        $this->description = $description;
        $this->status = $status;
        $this->date = $date;
        $this->id = $id;
    }
}


/*
Task deleting function
Called when user has decided to close a task
*/
function deleteTask($taskId, $folderId)
{
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }
    // Remove tasks from folders-tasks link table
    $query = "DELETE FROM `folders-tasks` WHERE taskId = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $taskId);
    if (!$query->execute()){
        echo "Error occured while deleting from folders-tasks";
    }
    // Remove task from tasks table
    $query = "DELETE FROM tasks WHERE id = ?";
    $query = $mysqli->prepare($query);
    $query->bind_param("i", $taskId);
    if (!$query->execute()){
        echo "Error occured while deleting task";
    }
}

// Get border color based on task status
function getBorderColor($status) {
    switch ($status) {
        case "urgent":
            return "darkred";
        case "medium":
            return "orange";
        case "low":
            return "green";
        default:
            return "black";
    }
}

// Get background color based on task status
function getBackgroundColor($status) {
    switch ($status) {
        case "urgent":
            return "#FFB6C1";
        case "medium":
            return "#FFE7AA";
        case "low":
            return "#D1FFC7";
        default:
            return "white";
    }
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

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>OurSite - Main Menu</title>
    <link rel="stylesheet" href="styles/folder.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <script src="scripts/script.js" defer></script>
    <script src="scripts/errormessage.js" defer></script>
    <script src="scripts/logout.js" defer></script>
    <meta name="description" content="OurSite - Folder tasks">
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
            <a href="main.php">Main</a>
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
        <div class="add-task-button">
            <button onclick="showPopup()">+</button>
        </div>

        <div class="error-popup" id="error-popup">
            <span class="error-close" onclick="hideError()">&times;</span>
            <div class="error-message" id="error-message"></div>
        </div>

        <div id="popup" class="popup">
            <span class="close" onclick="hidePopup()">&times;</span>
            <form method="post" action="createtask.php">
                <label for="taskTitle" class="required">Task Title</label><br>
                <input type="text" id="taskTitle" name="taskTitle" placeholder="New design" required pattern="[A-Za-z0-9!@#$%^&*()_+-]+" maxlength="30"><br><br>

                <label for="taskTitle" class="required">Task Description</label><br>
                <textarea rows="5" cols="20" id="taskDescription" name="taskDescription"
                    placeholder="Add new elements to design." required maxlength="150"></textarea><br><br>

                <label for="status" class="required">Choose the status</label><br>
                <select id="status" name="status" required>
                    <option value="urgent">Urgent</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select><br><br>

                <label for="taskDate" class="required">Task deadline</label><br>
                <input type="date" name="taskDate" id="taskDate" required><br><br>

                <input type="submit" name="submit" id="submit" value="Create Task" class="submit-button">
            </form>
        </div>

        <div class="task-container">
            <?php
            //Check if folderId was successfully set
            if ($folderId != 0) {

                //Get tasks related to folder
                $tasksArray = getTasksFromFolderId($folderId);

                //Task creation loop
                if (empty($tasksArray)) {
                    echo "<h3>No tasks found.</h3>";
                } else {
                    foreach ($tasksArray as $task) {
                        //Create template and fill in variables
                        $newTaskTitle = $task->title;
                        $newTaskDescription = $task->description;
                        $taskStatus = $task->status;
                        $newTaskDate = $task->date;
                        $taskId = $task->id;
                        $borderColor = getBorderColor($taskStatus);
                        $backgroundColor = getBackgroundColor($taskStatus);
                        $template = new Template('templates/task_tpl.php');
                        $template->assign("border_color", $borderColor);
                        $template->assign("background_color", $backgroundColor);
                        $template->assign("task_title", $newTaskTitle);
                        $template->assign("task_date", $newTaskDate);
                        $template->assign("task_description", $newTaskDescription);
                        $template->assign("task_id", $taskId);
                        //Display task container to user
                        echo $template->render();
                    }
                }
            } else {
                echo "<h1>Could not find folder</h1>";
            }
            ?>
        </div>
    </main>
    <footer>
        <p>Copyright © 2024 OurSite</p>
        <a href="/privacy_policy.php">Privacy policy</a>
        <a href="/terms_and_conditions.php">Terms & Conditions</a>
    </footer>
</body>

</html>