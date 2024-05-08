<?php
// If user trying to access the register page while logged in, then redirect them into the main page
session_start();
if (isset($_SESSION["isLoggedInUser"])) {
    header("Location: main.php");
}

include_once 'data/db.connection.php';
require_once 'lib/eng.tpl.php';

$errors = [];
$errorMessage = "";

$template = new Template('templates/login_tpl.php');

// Retrieves user credentials from database on successful query
function checkUserCredentials($username, $password, $mysqli)
{
    $query = "SELECT username, password FROM profiles WHERE username=?";
    $query = $mysqli->prepare($query);
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if ($row["username"] == $username && password_verify($password, $row["password"])) {
            // Successfully retrieved user
            return true;
        } else {
            return false;
        }
    } else {
        // No matching user found
        return false;
    }

}

// Insert cookie to database
function insertCookieToUserProfile($username, $cookieHash, $mysqli)
{
    $query = "UPDATE profiles SET cookie=? WHERE username=?";
    $query = $mysqli->prepare($query);
    $query->bind_param("ss", $cookieHash, $username);
    if ($query->execute()) {
        // Cookie was successfully inserted
        return true;
    } else {
        return false;
    }
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

    // Validating and sanitizing every element
    if (empty($_POST) && !isset($_POST["submit"])) {
        $errors[] = "Fields are empty";
    } else {
        // Create db connection
        $mysqli = new mysqli($server, $user, $password, $database);
        if ($mysqli->connect_error) {
            die("DB connection failed: " . $mysqli->connect_error);
        }
        $username = sanitizeInputVar($mysqli, $_POST["username"]);
        $password = sanitizeInputVar($mysqli, $_POST["password"]);
        // Check if credentials are correct
        if (checkUserCredentials($username, $password, $mysqli)) {
            $_SESSION["isLoggedInUser"] = "true";
            $_SESSION["username"] = $username;
            // If user decides to be remembered, create a cookie with user info and update database entry
            if (isset($_POST["rememberMe"])) {
                $cookieHash = $username . $password;
                $cookieHash = hash('sha256', $cookieHash);
                // The cookie is a combination of username and password hash
                setcookie("rememberMe", $cookieHash, time() + 1209600); // 2 cookie with user credentials will be valid for 2 weeks
                insertCookieToUserProfile($username, $cookieHash, $mysqli);
            }
            header('Location: main.php');
            exit;
        } else {
            $errors[] = "Wrong username or password.";
        }
    }
    $errorMessage = '<div id="confirmedError">' . implode(' ', $errors) . '</div>';
}

$template->assign('error_message', $errorMessage);

echo $template->render();