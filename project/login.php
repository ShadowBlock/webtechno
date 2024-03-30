<?php
require_once 'lib/eng.tpl.php';

session_start();
//If user is logged in, redirect to main
if (isset ($_SESSION["isLoggedInUser"])) {
    header("Location: main.php");
}

$errors = [];
$data = [];
$errorMessage = "";

$template = new Template('templates/login_tpl.php');

function sanitizeInput($data)
{
    $data = htmlspecialchars($data);
    return $data;
}

function checkUserCredentials($username, $password)
{
    //Loop through users and find a match
    $file = fopen("data/profiles.csv", "r");
    while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
        if ($username === $row[1] && password_verify($password, $row[2])) {
            return true;
        }
    }
    fclose($file);
    return false;
}

if (isset ($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

    // Validating and sanitizing every element
    if (empty ($_POST) && !isset ($_POST["submit"])) {
        $errors[] = "Fields are empty";
    } else {
        $username = sanitizeInput($_POST["username"]);
        $password = sanitizeInput($_POST["password"]);
        //Check if credentials are correct
        if (checkUserCredentials($username, $password)) {
            $_SESSION["isLoggedInUser"] = "true";
            $_SESSION["username"] = $username;
            header('Location: main.php');
            exit;
            //If user decides to be remembered, create a cookie with user info and update database entry
            if (isset ($_POST["rememberMe"])) {
                $cookieHash = $username . $password;
                $cookieHash = hash('sha256', $cookieHash);
                // The cookie is a combination of username and password
                setcookie("rememberMe", $cookieHash, time() + 1209600); // 2 cookie with user credentials will be valid for 2 weeks

                //Until project doesn't have SQL database CSV files have to be modified like this
                $file = fopen("data/profiles.csv", "r");
                $newFileRows = [];
                while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
                    if ($username === $row[1] && password_verify($password, $row[2])) {
                        $input = $row;
                        $input[5] = $cookieHash;
                        array_push($newFileRows, $input);
                        continue;
                    }
                    //IDE says unreachable, but is reachable
                    array_push($newFileRows, $row);
                }
                fclose($file);
                $newProfiles = fopen("data/profiles.csv", "w");
                foreach ($newFileRows as $row) {
                    fputcsv($newProfiles, $row, ";");
                }
                fclose($newProfiles);
            }
        } else {
            $errors[] = "Wrong username or password.";
        }
    }
    $errorMessage = '<div id="confirmedError">' . implode(' ', $errors) . '</div>';
}

$template->assign('error_message', $errorMessage);

echo $template->render();

?>