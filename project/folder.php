<?php
require 'cookies.php';
require 'lib/eng.tpl.php';

session_start();
if (isset ($_SESSION["isLoggedInUser"])) {
    //Check if session has username associated with session
    if (!isset ($_SESSION["username"])) {
        //Faulty session -> destroy it
        session_unset();
        session_destroy();
        header("Location: index.php");
    }
} else if (checkCookieForRememberMe()) {
    //Set session data based on cookie
    $_SESSION["isLoggedInUser"] = "true";
    $_SESSION["username"] = getUsernameFromCooke($_COOKIE["rememberMe"]);
} else {
    //User is redirected to index page
    header("Location: index.php");
}

//Find out the folderid based on post or session, else return user back to main
$folderId = 0;
if (isset ($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset ($_POST["action"]) && isset ($_POST["taskid"])) {
        //Deletes the task if the user clicked "close"
        if ($_POST["action"] == "delete") {
            deleteTask($_POST["taskid"], $_SESSION["folderid"]);
        }
    } else if (isset ($_POST["folderid"])) {
        $folderId = $_POST["folderid"];
        $_SESSION["folderid"] = $folderId;
    } else if (isset ($_SESSION["folderid"])) {
        $folderId = $_SESSION["folderid"];
    } else {
        //No folder is active, return user to main.php
        header("Location: main.php");
    }
}

/*
Task deleting function
Called when user has decided to close a task and php is processing
*/
function deleteTask($taskId, $folderId)
{
    //Remove task from tasks.csv
    $file = fopen("data/tasks.csv", "r");
    $newFileRows = [];
    while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
        if ($row[1] == $taskId) {
            continue;
        }
        array_push($newFileRows, $row);
    }
    fclose($file);
    $newTasksFile = fopen("data/tasks.csv", "w");

    //Rewrite tasks file
    foreach ($newFileRows as $row) {
        fputcsv($newTasksFile, $row, ";");
    }
    fclose($newTasksFile);

    //Remove task from folders.csv
    $file = fopen("data/folders.csv", "r");
    $newFileRows = [];
    while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
        if ($row[1] == $folderId) {
            $tasksArray = explode(",", $row[3]);
            $key = array_search($taskId, $tasksArray);
            unset($tasksArray[$key]);
            $tasksArray = implode(",", $tasksArray);
            $row[3] = $tasksArray;
        }
        array_push($newFileRows, $row);
    }
    fclose($file);
    $newFoldersFile = fopen("data/folders.csv", "w");

    //Rewrite folders file
    foreach ($newFileRows as $row) {
        fputcsv($newFoldersFile, $row, ";");
    }
    fclose($newFoldersFile);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>OurSite - Main Menu</title>
    <link rel="stylesheet" href="styles/folder.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <meta name="description" content="OurSite - Folder tasks">
    <meta name="author" content="geolee">
    <meta name="author" content="stlill">
    <meta name="author" content="inviin">
</head>

<body>
    <header>
        <a href="./main.php" class="logo"></a>
        <div class="header-right">
            <a href="main.php">BACK</a>
        </div>
    </header>
    <main>
        <div class="add-task-button">
            <a href="createtask.php">
                <button>+</button>
            </a>
        </div>
        <div class="task-container">
            <?php
            //Check if folderId was successfully set
            if ($folderId == 0) {
                $tasks = array();
                //Get tasks related to folder
                $file = fopen("data/tasks.csv", "r");
                while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
                    if ($row[1] == $folderId) {
                        array_push($tasks, $row);
                    }
                }
                fclose($file);

                //Task creation loop
                if (empty ($tasks)) {
                    echo "No tasks found.";
                } else {
                    foreach ($tasks as $task) {
                        //Create template and fill in variables
                        $taskTitle = $task[2];
                        $taskDescription = $task[3];
                        $taskStatus = $task[4];
                        $taskDate = $task[5];
                        $taskId = $task[0];
                        $template = new Template('templates/task_tpl.php');
                        $template->assign("task_title", $taskTitle);
                        $template->assign("task_date", $taskDate);
                        $template->assign("task_description", $taskDescription);
                        $template->assign("task_id", $taskId);
                        //Display task container to user
                        echo $template->render();
                    }
                }
            }
            else {
                echo "<h1>Could not find folder</h1>";
            }
            ?>
        </div>
    </main>
    <footer>
        <p>Copyright © 2024 OurSite</p>
        <a href="/privacy_policy.html">Privacy policy</a>
        <a href="/terms_and_conditions.html">Terms & Conditions</a>
    </footer>
</body>

</html>