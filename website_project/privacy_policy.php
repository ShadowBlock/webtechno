<?php

require_once 'lib/eng.tpl.php';
require_once 'cookies.php';

session_start();

$template = new Template('templates/privacy_policy_tpl.php');

if (isset($_SESSION["isLoggedInUser"])) {
    $template->assign('logged_in', true);
} else {
    $template->assign('logged_in', false);
    $welcomeMessage = null;
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

echo $template->render();