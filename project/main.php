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

//When the user clicks the logout button, destroy session and remove cookies, throw user to index page
if (isset ($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset ($_POST["logoutAction"]) && $_POST["logoutAction"] == "true") {
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
    <link rel="stylesheet" href="styles/main.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <meta name="description" content="OurSite - Look at your or your team's currents tasks for today
        and make yourself free productive every day!">
    <meta name="author" content="geolee">
    <meta name="author" content="stlill">
    <meta name="author" content="inviin">
</head>

<body>
    <header>
        <a href="index.php" class="logo"></a>
        <form actin="main.php" method="post">
            <input type="hidden" id="logout" name="logoutAction" value="true">
            <input type="submit" name="logout" value="Log out" class="logout-button">
        </form>
    </header>
    <main>
        <div>
            <h1>Take a look at your tasks for today</h1>
        </div>
        <div class="add-folder-button">
            <a href="createfolder.php">
                <button>+</button>
            </a>
        </div>
        <div class="folder-container">
            <?php
            $folderIdArray = array();
            $username = $_SESSION["username"];
            //Loop for getting folder ids from profile
            $file = fopen("data/profiles.csv", "r");
            while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
                if ($row[1] == $username) {
                    $folderIdArray = explode(",", $row[4]);
                    break;
                }
            }
            fclose($file);

            //Special case when user has no folders, echo error and exit script
            if (count($folderIdArray) == 0) {
                echo "<h1>Nothing here</h1>";
                die();
            }

            $folderContentsArray = array();
            //Loop for putting folder contents into an array
            $file = fopen("data/folders.csv", "r");
            while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
                if (in_array($row[0], $folderIdArray)) {
                    array_push($folderContentsArray, $row);
                }
            }
            fclose($file);

            //Main loop for creating folder containers for the user
            $file = fopen("data/tasks.csv", "r");
            foreach ($folderContentsArray as $folder) {
                rewind($file);
                $folderTitle = $folder[1];
                $folderMembers = explode(",", $folder[2]);
                $showMembers = '';
                foreach ($folderMembers as $member) {
                    $showMembers .= '<img class="folder-icon" src="img/user-icon.png" alt="' . $member . '">';
                }
                $noData = false;
                $folderId = $folder[0];
                $tasks = array();
                //Loop for tasks associated with folder
                while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
                    if ($row[1] == $folderId && $taskCount < 2) {
                        $taskDetails = array(
                            'title' => $row[2],
                            'deadline' => $row[5],
                            'status' => $row[4]
                        );
                        array_push($tasks, $taskDetails);
                        $taskCount++;
                    }
                    $taskListHtml = '';
                    foreach ($tasks as $task) {
                        $taskListHtml .= "<li>{$task['title']} | {$task['deadline']} | {$task['status']}</li>";
                    }
                }
                $taskTitles = array();
                $taskDeadlines = array();
                $taskStatuses = array();
                foreach ($tasks as $task) {
                    array_push($taskTitles, $task[2]);
                    array_push($taskDeadlines, $task[5]);
                    array_push($taskStatuses, $task[4]);
                }
                $tasks = 0;
                //Depending on the amount of tasks, show correct amount
                if (count($taskTitles) == 0) {
                    $tasks = 0;
                } else if (count($taskTitles) == 1) {
                    $tasks = 1;
                } else {
                    $tasks = 2;
                }
                //Template variables are filled
                $template = new Template('templates/folder_tpl.php');
                $template->assign("nothing", $noData);
                $template->assign("members", $showMembers);
                $template->assign("title", $folderTitle);
                $template->assign("tasks", $tasks);
                switch ($tasks) {
                    case 1:
                        $template->assign("task_title_1", $taskTitles[0]);
                        $template->assign("deadline_1", $taskDeadlines[0]);
                        $template->assign("status_1", $taskStatuses[0]);
                        break;
                    case 2:
                        $template->assign("task_title_1", $taskTitles[0]);
                        $template->assign("deadline_1", $taskDeadlines[0]);
                        $template->assign("status_1", $taskStatuses[0]);
                        $template->assign("task_title_2", $taskTitles[1]);
                        $template->assign("deadline_2", $taskDeadlines[1]);
                        $template->assign("status_2", $taskStatuses[1]);
                        break;
                }
                //Folder id connected with folder and display the folder to the user
                $template->assign("folder_id", $folder[0]);
                
                echo $template->render();
            }
            fclose($file);
            ?>
        </div>
    </main>
    <footer>
        <p>Copyright © 2024 OurSite</p>
        <a href="privacy_policy.html">Privacy policy</a>
        <a href="terms_and_conditions.html">Terms & Conditions</a>
    </footer>
</body>

</html>