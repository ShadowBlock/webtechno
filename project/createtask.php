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

// Function to check if the status is in the array
function isValidStatus($status)
{
    $validStatus = array("urgent", "medium", "low");
    return in_array($status, $validStatus);
}

// Function to get the last folder ID
function getLastID($csvFile)
{
    $file = fopen($csvFile, 'r');
    $lastID = 0;
    while (($row = fgetcsv($file, 1000, ";")) !== false) {
        $lastID = max($lastID, intval($row[0]));
    }
    fclose($file);
    return $lastID;
}

// Removes command characters
function validateInput($input)
{
    return htmlspecialchars(trim($input));
}

// Check if date is valid
function validDate($date)
{
    // Date is valid when year starts with 20 and ends with 2 numbers, month is max 12 and days is max 31.
    $pattern = '/^20\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/';

    // Make the year, month, day separate variables and check the validity with checkdate().
    list($year, $month, $day) = explode('-', $date);

    // Check if the input date is valid.
    if (preg_match($pattern, $date) && checkdate($month, $day, $year)) {
        return true;
    } else {
        return false;
    }
}

$tasksCsvFile = "data/tasks.csv";
$foldersCsvFile = "data/folders.csv";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset ($_POST["submit"]) && $_POST["submit"] == "Create Task") {
    $taskTitle = validateInput($_POST['taskTitle']);
    $taskDescription = validateInput($_POST['taskDescription']);
    $status = validateInput($_POST['status']);
    $taskDate = validateInput($_POST['taskDate']);
    $folderId = $_SESSION["folderid"];

    if (!isValidStatus($status)) {
        echo "<p>Error: Invalid status.</p>";
    } elseif (!validDate($taskDate)) {
        echo "<p>Error: Date is invalid.</p>";
    } else {
        // Get the next ID
        $nextId = getLastID($tasksCsvFile) + 1;

        $newTaskEntry = [$nextId, $folderId, $taskTitle, $taskDescription, $status, $taskDate];

        // Add the new task entry to the CSV file
        $file = fopen($tasksCsvFile, 'a');
        fputcsv($file, $newTaskEntry, ";");
        fclose($file);

        // Update folders.csv
        $folderLines = file($foldersCsvFile);
        foreach ($folderLines as &$line) {
            $fields = explode(";", $line);
            if ($fields[0] == $folderId) {
                // Append the new task id to the existing tasks in the folder
                if (empty ($fields[3])) {
                    $fields[3] = $nextId;
                } else {
                    $fields[3] .= "," . $nextId;
                }
                $line = implode(";", $fields);
                break;
            }
        }
        file_put_contents($foldersCsvFile, implode("", $folderLines));

        echo "<p>Task created successfully!</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporary Create Task</title>
</head>

<body>
    <header>
        <nav>
            <a href="folder.php">Go back</a>
        </nav>
    </header>

    <h2>Create Task</h2>

    <form method="post">
        <label for="taskTitle">Task Title:</label><br>
        <input type="text" id="taskTitle" name="taskTitle" placeholder="New design" required><br><br>

        <label for="taskTitle">Task Description:</label><br>
        <textarea rows="5" cols="20" id="taskDescription" name="taskDescription"
            placeholder="Add new elements to design." required></textarea><br><br>

        <label for="status">Choose the status:</label><br>
        <select id="status" name="status" required>
            <option value="urgent">Urgent</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select><br><br>

        <label for="taskDate">Task deadline:</label><br>
        <input type="date" name="taskDate" id="taskDate" required><br><br>

        <input type="submit" name="submit" id="submit" value="Create Task">
    </form>

</body>

</html>