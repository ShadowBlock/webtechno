<?php
    // Set variables for errorData and error messages. errorData = 0 is default value.
    $errorData = 0;
    $errorMessage = "";

    // Take away special characters, which allow scripting and injecting and trim whitespaces.
    function validateInput($input) {
        return htmlspecialchars(trim($input));
    }
    function validateInputName($input) {
        return htmlspecialchars(trim($input), ENT_NOQUOTES);
    }

    // Check if user has submitted the form and if submit button has been pressed.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['submitReservation']) && $_POST['submitReservation'] === 'Submit') {
            // errorData = 1 is value for form submitted. Everything over 1 is considered an error.
            $errorData = 1;

            // Check if provided string has numbers inside it.
            function hasNumbers($string) {
                return preg_match('#[0-9]#', $string);
            }

            // Check if a string starts or ends with a dot.
            function dotCheck($string) {
                return (substr($string, 0, 1) === '.' || substr($string, -1) === '.');
            }

            // Make sure the name only contains letters, Estonian letters, -, space and '. Also specify the usage of unicode. Limit to 50 chars.
            function correctName($string) {
                $pattern = "/^[A-Za-zÜüÕõÖöÄäŠšŽž\'\’ -]{1,50}$/u";
                return preg_match($pattern, $string);
            }

            // Make sure tel. number contains only numbers, +, spaces or hyphen.
            function correctPhone($string) {
                $pattern = '/^[0-9+ -]+$/';
                return preg_match($pattern, $string);
            }

            function singleSymbols($string) {
                $pattern = '/^[\'\’ -]{1}$/u';
                return preg_match($pattern, $string);
            }

            /* Validate email address using filter_var and if it has double dots and starts or ends  
            with dots somewhere in the local part. */
            function validEmail($email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL) && strpos($email, '..') === false) {
                    // Check for the local part of the email if it has dotCheck.
                    $localPart = explode('@', $email)[0];
                    if (!dotCheck($localPart)) {
                        return true;
                    }
                }
                return false;
            }

            // Check if date is in range and if date is valid.
            function validDate($date) {
                // Date is valid when year starts with 20 and ends with 2 numbers, month is max 12 and days is max 31.
                $pattern = '/^20\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/';
                $minDate = '2023-01-01';
                $maxDate = '2033-01-01';

                // Make the year, month, day separate variables and check the validity with checkdate().
                list($year, $month, $day) = explode('-', $date);
            
                // Check if the input date is valid and in the specified range.
                if (preg_match($pattern, $date) && checkdate($month, $day, $year) && $date >= $minDate && $date <= $maxDate) {
                    return true;
                } else {
                    return false;
                }
            }
            
            // Check if the name has any numbers or other symbols.
            function validName($nameFirst, $nameLast, $nameMiddle) {
                if (isset($nameFirst) && isset($nameLast)) {

                    // Check if first or last name contains numbers or other symbols.
                    if (!hasNumbers($nameFirst) && !hasNumbers($nameLast) && correctName($nameFirst) && correctName($nameLast) && !singleSymbols($nameFirst) && !singleSymbols($nameLast)) {

                        // Check if middle name is set and if it has numbers or other symbols.
                        if (!empty($nameMiddle)) {
                            if (!hasNumbers($nameMiddle) && correctName($nameMiddle) && !singleSymbols($nameMiddle)) {
                                return true;
                            } else {
                                return false;
                            }
                        } else {
                            return true;
                        }
                    } else {
                        // At least one of the required names contains numbers or other symbols.
                        return false;
                    }
                }
            }

            // Check if salutation is from our radio select variants.
            function validSalutation($salutation) {
                $salutations = array("mr", "mrs", "ms", "miss");
                foreach ($salutations as $sal) {
                    if ($sal == $salutation) {
                        return true;
                    }
                }
                // Return false if salutation not found in our system.
                return false;
            }

            // Grab the name elements from POST and give error if needed.
            $nameFirst = validateInputName($_POST['nameFirst']);
            $nameLast = validateInputName($_POST['nameLast']);
            $nameMiddle = validateInputName($_POST['nameMiddle']);
            if (!validName($nameFirst, $nameLast, $nameMiddle)) {
                $errorMessage = "Names must not include special characters or numbers.<br>In names are allowed: letters, special letters of Estonian language, hyphen, space and apostrophe.<br>Names can only be maximum 50 characters long.<br>";
                $errorData++;
            }

            // Grab the salutation from POST and give error if needed.
            if (!empty($_POST['salute'])) {
                $salute = validateInput($_POST['salute']);
                if (!validSalutation($salute)) {
                    $errorMessage .= "Salutation must be either Mr, Mrs, Ms or Miss.<br>";
                    $errorData++;
                }
            } else {
                $salute = "";
            }

            // Grab the age from POST and make sure it is an integer and in the range accepted.
            $age = validateInput((int)$_POST['age']);
            if ($age < 18 || $age > 98) {
                $errorMessage .= "Age out of range or invalid number. Must be over 17 and under 99.<br>";
                $errorData++;
            }

            /* Grab the email from POST and make sure the email is valid.
            This validation uses both my own validation and PHP filter_var validation. Just to make sure :) */
            $email = validateInput($_POST['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !validEmail($email)) {
                $errorMessage .= "Invalid email address.<br>";
                $errorData++;
            }
            // Grab the date from POST and make sure the date is valid and show error message if needed.
            $date = validateInput($_POST['dateArrive']);
            if (!validDate($date)) {
                $errorMessage .= "Invalid date or outside range.<br>";
                $errorData++;
            }

            // Checks the pattern and if it is in range.
            if (!empty($_POST["phone"])) {
                $phone = validateInput($_POST["phone"]);
                if (correctPhone($phone) === 0 || strlen($phone) > 11 || strlen($phone) < 7) {
                    $errorMessage .= "Phone number can only contain numbers, plus sign, hyphen or spaces.<br> Phone number cannot be longer than 11 nor shorter than 7.<br>";
                    $errorData++;
                }
            } else {
                $phone = "";
            }

            // Does not allow more than 200 character comments.
            if (!empty($_POST["comment"])) {
                $comment = validateInput($_POST["comment"]);
                if (!empty($comment) && strlen($comment) > 200) {
                    $errorMessage .= "Comment section must be under 200 characters.<br>";
                    $errorData++;
                }
            } else {
                $comment = "";
            }

            if ($errorData == 1) {
                // Open or create the data.csv file.
                $file = fopen('data.csv', 'a');
                // Write registration details to the CSV file.
                fputcsv($file, [$nameFirst, $nameMiddle, $nameLast, $salute, $age, $email, $phone, $date, $comment], ';');
                fclose($file);
            }
        }
        // If download button pressed.
        if (!empty($_POST['download']) && $_POST['download'] === 'Download') {
            // Create CSV data.
            $nameFirst = validateInputName($_POST['nameFirst']);
            $nameLast = validateInputName($_POST['nameLast']);
            $nameMiddle = validateInputName($_POST['nameMiddle']);
            $salute = validateInput($_POST['salute']);
            $age = validateInput($_POST['age']);
            $email = validateInput($_POST['email']);
            $phone = validateInput($_POST['phone']);
            $date = validateInput($_POST['dateArrive']);
            $comment = validateInput($_POST['comment']);
            $array = [$nameFirst, $nameMiddle, $nameLast, $salute, $age, $email, $phone, $date, $comment];
            $csv_data = implode(';', $array);
            $filename = "user_details.csv";

            // Set headers for download.
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            // Output CSV data.
            echo $csv_data;
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAB 5 - PHP FORM</title>
    <meta name="author" content="stlill">
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Home</a>
            <a href="download.php">Download</a>
        </nav>
    </header>
    <h1>Welcome to LAB5!</h1>
    <div id="confirmedText">
        <?php
            // If registration successful, display details.
            if ($errorData == 1) {
                echo "<p id=\"successful\">Registration successful, here are your details:<br>
                    Name: $nameFirst $nameMiddle $nameLast<br>
                    Salutation: $salute<br>
                    Age: $age<br>
                    E-mail: $email<br>
                    Phone: $phone<br>
                    Date of arrival: $date<br>
                    Comment: $comment</p>";

                echo "<form action=\"index.php\" method=\"post\" id=\"download\">
                        <input type=\"hidden\" name=\"nameFirst\" value=\"$nameFirst\">
                        <input type=\"hidden\" name=\"nameMiddle\" value=\"$nameMiddle\">
                        <input type=\"hidden\" name=\"nameLast\" value=\"$nameLast\">
                        <input type=\"hidden\" name=\"salute\" value=\"$salute\">
                        <input type=\"hidden\" name=\"age\" value=\"$age\">
                        <input type=\"hidden\" name=\"email\" value=\"$email\">
                        <input type=\"hidden\" name=\"phone\" value=\"$phone\">
                        <input type=\"hidden\" name=\"dateArrive\" value=\"$date\">
                        <input type=\"hidden\" name=\"comment\" value=\"$comment\">
                        <input type=\"submit\" name=\"download\" value=\"Download Data\"></input>
                    </form>";
            }
        ?>
    </div>
    <div id="confirmedError">
        <?php
            // If some errors occured, notify user.
            if($errorData > 1) {
                echo "<h3 id=\"errorHeading\">Your input data has some <span style=\"color:red;\">errors:</span></h3>";
                echo "<p id=\"errorMessage\">$errorMessage</p>";
                echo "<form action=\"index.php\" method=\"post\" id=\"download\">
                        <input type=\"hidden\" name=\"nameFirst\" value=\"$nameFirst\">
                        <input type=\"hidden\" name=\"nameMiddle\" value=\"$nameMiddle\">
                        <input type=\"hidden\" name=\"nameLast\" value=\"$nameLast\">
                        <input type=\"hidden\" name=\"salute\" value=\"$salute\">
                        <input type=\"hidden\" name=\"age\" value=\"$age\">
                        <input type=\"hidden\" name=\"email\" value=\"$email\">
                        <input type=\"hidden\" name=\"phone\" value=\"$phone\">
                        <input type=\"hidden\" name=\"dateArrive\" value=\"$date\">
                        <input type=\"hidden\" name=\"comment\" value=\"$comment\">
                        <input type=\"submit\" name=\"download\" value=\"Download Data\"></input>
                    </form>";
            }
        ?>
    </div>
    <form action="index.php" id="formReserve" method="post" autocomplete="off">
        <label for="nameFirst" class="required" >First name</label><br>
        <input type="text" id="nameFirst" name="nameFirst" pattern="/^[A-Za-zÜüÕõÖöÄäŠšŽž\'\’ -]{1,50}$/u" placeholder="Stiven" maxlength="50" minlength="1" required><br>
        <label for="nameMiddle">Middle name</label><br>
        <input type="text" id="nameMiddle" name="nameMiddle" pattern="/^[A-Za-zÜüÕõÖöÄäŠšŽž\'\’ -]{1,50}$/u" maxlength="50" minlength="1"><br>
        <label for="nameLast" class="required">Last name</label><br>
        <input type="text" id="nameLast" name="nameLast" pattern="/^[A-Za-zÜüÕõÖöÄäŠšŽž\'\’ -]{1,50}$/u" maxlength="50" minlength="1" placeholder="Lille" required><br>
        <label for="saluteMr">Salutation</label><br>
        <input type="radio" id="saluteMr" name="salute" value="mr">
        <label for="saluteMr">mr</label><br>
        <input type="radio" id="saluteMs" name="salute" value="ms">
        <label for="saluteMs">ms</label><br>
        <input type="radio" id="saluteMrs" name="salute" value="mrs">
        <label for="saluteMrs">mrs</label><br>
        <input type="radio" id="saluteMiss" name="salute" value="miss">
        <label for="saluteMiss">miss</label><br>
        <label for="age" class="required">Age</label><br>
        <input type="number" name="age" id="age" min="18" max="98" placeholder="18-98" required><br>
        <label for="email" class="required">E-mail</label><br>
        <input type="text" name="email" id="email" placeholder="stlill@taltech.ee" required><br>
        <label for="phone">Phone</label><br>
        <input type="tel" name="phone" id="phone" minlength="7" maxlength="11" placeholder="+3725555555" pattern="/^[0-9+ -]+$/"><br>
        <label for="dateArrive" class="required">Date of arrival</label><br>
        <input type="date" name="dateArrive" id="dateArrive" required><br>
        <label for="comment">Comment</label><br>
        <textarea name="comment" id="comment" cols="25" rows="8" maxlength="200" placeholder="200 characters maximum"></textarea><br>
        <input type="submit" value="Submit" id="submitReservation" name="submitReservation"><br>
    </form>
</body>
</html>
