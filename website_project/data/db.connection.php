<?php
// Connection details to enos server

$server =  "localhost";
$user = "root";
$password = "root";
$database = "oursite";

// Universal sanitization function
function sanitizeInputVar($link, $var){
    $var = stripslashes($var);
    $var = htmlentities($var);
    $var = strip_tags($var);
    $var = mysqli_real_escape_string($link, $var);
    return $var;
}
