<?php
session_start();

if (isset ($_SESSION["isLoggedInUser"])) {
} else if (checkCookieForRememberMe()) {
    $_SESSION["isLoggedInUser"] = "true";
    //User's cookie has been checked
} else {
    //User is redirected to index page
    header("Location: index.php");
}

// Removes command characters
function validateInput($input)
{
    return htmlspecialchars(trim($input));
}

// Check if a username exists in our database
function usernameExists($username, $foldersCsvFile)
{
    $file = fopen($foldersCsvFile, 'r');
    while (($row = fgetcsv($file, 1000, ";")) !== false) {
        if ($row[1] === $username) {
            fclose($file);
            return true;
        }
    }
    fclose($file);
    return false;
}

// Function to update folders for a specific user
function updateFolders($username, $folderID, $csvFile)
{
    $lines = file($csvFile);
    $updated = false;

    foreach ($lines as &$line) {
        $data = str_getcsv($line, ";");
        if ($data[1] === $username) {
            // Check if folders already exist
            if (empty ($data[4])) {
                $data[4] = "\"$folderID\"";
            } else {
                // If it exists, add the folderid to what already is there
                $folders = explode(",", $data[4]);
                if (!in_array($folderID, $folders)) {
                    $folders[] = $folderID;
                    $data[4] = "\"" . implode(",", $folders) . "\"";
                }
            }
            $line = implode(";", $data) . PHP_EOL;
            $updated = true;
            break;
        }
    }

    file_put_contents($csvFile, implode("", $lines));
    return $updated;
}

// Function to get the last folder ID
function getLastFolderID($csvFile)
{
    $file = fopen($csvFile, 'r');
    $lastID = 0;
    while (($row = fgetcsv($file, 1000, ";")) !== false) {
        $lastID = max($lastID, intval($row[0]));
    }
    fclose($file);
    return $lastID;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["submit"] == "Create Folder") {
    // Retrieve values from form
    $folderTitle = validateInput($_POST['folderTitle']);
    $folderMembers = isset ($_POST['folderMembers']) ? validateInput($_POST['folderMembers']) : ''; // Check if members are provided, if not set to empty string
    $folderOwner = $_SESSION['username'];

    // Path to the CSV files
    $foldersCsvFile = 'data/folders.csv';
    $profilesCsvFile = 'data/profiles.csv';

    // Check if the folder title already exists for the user
    $file = fopen($foldersCsvFile, 'r');
    $folderExists = false;
    while (($row = fgetcsv($file, 1000, ";")) !== false) {
        // Check if folder title exists for the user
        if ($row[1] === $folderTitle && $row[4] === $folderOwner) {
            $folderExists = true;
            break;
        }
    }
    fclose($file);

    if ($folderExists) {
        echo "<p>Error: Folder already exists</p>";
    } else {
        // Check if all members exist in profiles.csv
        $membersArray = explode(",", $folderMembers);
        $missingMembers = [];
        foreach ($membersArray as $member) {
            if (!usernameExists(trim($member), $profilesCsvFile)) {
                $missingMembers[] = trim($member);
            }
        }

        if (!empty ($missingMembers) && empty ($membersArray)) {
            echo "<p>Error: The following members do not exist in our database: " . implode(", ", $missingMembers) . "</p>";
        } else {
            $lastID = getLastFolderID($foldersCsvFile);
            $newID = $lastID + 1;
            $newFolderEntry = [$newID, $folderTitle, $folderMembers, '', $folderOwner];

            // Add the new folder entry
            $file = fopen($foldersCsvFile, 'a');
            fputcsv($file, $newFolderEntry, ";");
            fclose($file);

            // Update folders for owner
            updateFolders($folderOwner, $newID, $profilesCsvFile);

            // Update folders for members
            $membersArray = explode(",", $folderMembers);
            foreach ($membersArray as $member) {
                updateFolders(trim($member), $newID, $profilesCsvFile);
            }

            echo "<p>Folder created successfully!</p>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporary Create Folder</title>
</head>

<body>
    <header>
        <nav>
            <a href="main.php">Go back</a>
        </nav>
    </header>

    <h2>Create Folder</h2>

    <form method="post">
        <label for="folderTitle">Folder Title:</label><br>
        <input type="text" id="folderTitle" name="folderTitle" placeholder="Group project" required><br><br>

        <label for="folderMembers">Folder Members:</label><br>
        <input type="text" id="folderMembers" name="folderMembers" placeholder="geolee, inviin"><br><br>

        <input type="submit" name="submit" id="submit" value="Create Folder">
    </form>

</body>

</html>