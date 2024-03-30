<?php

require_once 'lib/eng.tpl.php';

session_start();

$template = new Template('templates/index_tpl.php');

if (isset($_SESSION["isLoggedInUser"])) {
    $welcomeMessage = "<h2>Welcome Back " . $_SESSION['username'] . '!</h2>';
    $template->assign('logged_in', true);
    $template->assign('welcome_message', $welcomeMessage);
} else {
    $template->assign('LOGGED_IN', false);
    $welcomeMessage = null;
}

echo $template->render();

?>