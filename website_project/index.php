<?php

require_once 'lib/eng.tpl.php';
require_once 'cookies.php';

session_start();

$template = new Template('templates/index_tpl.php');

if (isset($_SESSION["isLoggedInUser"])) {
    $welcomeMessage = "<h2>Welcome Back " . $_SESSION['username'] . '!</h2>';
    $template->assign('logged_in', true);
    $template->assign('welcome_message', $welcomeMessage);
} else {
    $template->assign('logged_in', false);
    $welcomeMessage = null;
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["logoutAction"]) && $_POST["logoutAction"] == "true") {
        session_unset();
        session_destroy();
        removeUserCookie();
        header("Location: index.php");
        exit;
    }
}

echo $template->render();
