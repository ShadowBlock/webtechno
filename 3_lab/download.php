<?php
    // Reset fileNotFound counter.
    $fileNotFound = 0;

    // Check if user has pushed button.
    if (isset($_POST['download'])) {
        if (file_exists("./data.csv")) {
        // Set headers to force download.
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="registrations.csv"');

        // Read data file content.
        readfile("./data.csv");

        exit();
        } else {
            // File not found.
            $fileNotFound = 1;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAB 5 - PHP Info</title>
    <meta name="author" content="stlill">
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Home</a>
            <a href="download.php">Download</a>
        </nav>
    </header>
    <div id="reservedText">
    <?php
        // Check if the file exists.
        if (file_exists("./data.csv")) {

            // Read the contents of the CSV file into an array.
            $data = file("./data.csv", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            // Count the number of lines, which equals to registrations.
            $registrations = count($data);
            
            echo "<p>Number of successful registrations: $registrations</p>";
            
        } else {
            // File not found, therefore no registrations.
            echo "<p>Number of successful registrations: 0 </p>";
        }

    ?>
    </div>
    <div id="downloadData">
        <form action="download.php" method="post">
            <input type="submit" name="download" value="Download registration data"></input>
        </form>
        <?php
            // Check if file exists using predefined variable.
            if ($fileNotFound) {
                echo "<p>File does not exist!</p>";
            }
        ?>
    </div>
</body>
</html>