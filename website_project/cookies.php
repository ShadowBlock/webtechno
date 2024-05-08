<?php
include_once 'data/db.connection.php';


// Create db connection
$mysqli = new mysqli($server, $user, $password, $database);
if ($mysqli->connect_error) {
    die("DB connection failed: " . $mysqli->connect_error);
}

/*
Check if a cookie matching a user exists
*/
function checkCookieForRememberMe()
{
    if (!isset($_COOKIE["rememberMe"])) {
        return false;
    }
    $rememberMeHash = $_COOKIE["rememberMe"];
    global $mysqli;
    $queryVariable = sanitizeInputVar($mysqli, $rememberMeHash);
    $query = "SELECT cookie FROM profiles WHERE cookie=?";
    $query = $mysqli->prepare($query);
    $query->bind_param("s", $queryVariable);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    //Check if serverside cookie matches client side cookie
    if ($row["cookie"] == $rememberMeHash) {
        return true;
    } else {
        return false;
    }
}

/*
Remove cookies which are invalid during checking
*/
function removeUserCookie()
{
    if (isset($_COOKIE["rememberMe"])) {
        $rememberMeHash = $_COOKIE["rememberMe"];
        setcookie("rememberMe", "", time() - 3600);
        global $mysqli;
        $queryVariable = sanitizeInputVar($mysqli, $rememberMeHash);
        $query = "UPDATE profiles SET cookie = null WHERE cookie=?";
        $query = $mysqli->prepare($query);
        $query->bind_param("s", $queryVariable);
        $query->execute();
    }
}


/*
Find username from given cookie
*/
function getUsernameFromCookie($rememberMeHash)
{
    global $mysqli;
    $queryVariable = sanitizeInputVar($mysqli, $rememberMeHash);
    $query = "SELECT username FROM profiles WHERE cookie=?";
    $query = $mysqli->prepare($query);
    $query->bind_param("s", $queryVariable);
    if ($query->execute()) {
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        return $row["username"];
    } else {
        return null;
    }
}
?>