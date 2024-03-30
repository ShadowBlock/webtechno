<?php
function checkCookieForRememberMe()
{
    if (!isset ($_COOKIE["rememberMe"])) {
        return false;
    }
    $rememberMeHash = $_COOKIE["rememberMe"];
    $file = fopen("data/profiles.csv", "r");
    while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
        if ($row[5] == $rememberMeHash) {
            return true;
        }
    }
    return false;
}


function removeUserCookie()
{
    if (isset ($_COOKIE["rememberMe"])) {
        $cookieHash = $_COOKIE["rememberMe"];
        setcookie("rememberMe", "", time() - 3600);
        $dataRows = [];
        $file = fopen("data/profiles.csv", "r");
        while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
            if ($row[5] == $cookieHash) {
                $input = $row;
                $input[5] = null;
                array_push($dataRows, $input);
                continue;
            }
            array_push($dataRows, $row);
        }
        fclose($file);
        $newProfiles = fopen("data/profiles.csv", "w");
        foreach ($dataRows as $row) {
            fputcsv($newProfiles, $row, ";");
        }
        fclose($newProfiles);
    }
}

function getUsernameFromCooke($cookieHash)
{
    $file = fopen("data/profiles.csv", "r");
    while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
        if ($row[5] == $cookieHash) {
            fclose($file);
            return $row[1];
        }
    }
    fclose($file);
}
?>