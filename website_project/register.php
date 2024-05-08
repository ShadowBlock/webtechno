<?php
session_start();

// If user trying to access the register page while logged in, then redirect them into the main page
if (isset($_SESSION["isLoggedInUser"])) {
    header("Location: main.php");
}

include_once 'data/db.connection.php';
require_once 'lib/eng.tpl.php';

$errors = array();
$successFlag = false;
$errorMessage = "";
$successMessage = '<div id="confirmedText">Successfully registered!</div>';

$template = new Template('templates/register_tpl.php');

class Requirements
{
    public $requirements;

    public function __construct($requirements)
    {
        $this->requirements = $requirements;
    }

    public function printRequirements()
    {
        $requirementListHtml = '';

        foreach ($this->requirements as $requirement) {
            $requirementListHtml .= "<li>{$requirement}</li>";
        }

        return $requirementListHtml;
    }
}

$requirements = new Requirements([
    'Username must be unique',
    'Email must be valid',
    'Password must be at least 6 characters long',
    'Password must contain at least 1 number',
    'Passwords must match'
]);

class User
{
    public $username;
    public $hashedPassword;
    public $email;

    public function __construct($username, $hashedPassword, $email)
    {
        $this->username = $username;
        $this->hashedPassword = $hashedPassword;
        $this->email = $email;
    }
}

class UserManager
{

    public function __construct()
    {
    }

    public function addUser(User $user, $mysqli)
    {
        $query = "INSERT INTO profiles (username, password, email) VALUES (?, ?, ?)";
        $query = $mysqli->prepare($query);
        $query->bind_param("sss", $user->username, $user->hashedPassword, $user->email);
        if ($query->execute()) {
            // Successfully added new user to db
            return true;
        } else {
            return false;
        }
    }

    public function validateUsername($username, $mysqli)
    {
        $query = "SELECT * FROM profiles WHERE username=?";
        $query = $mysqli->prepare($query);
        $query->bind_param("s", $username);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            // Username already exists in the database
            return true;
        } else {
            return false;
        }
    }

    // Checks if the terms and conditions button is pressed
    public function termsChecker()
    {
        if (isset($_POST['termsConfirm'])) {
            return true;
        } else {
            return false;
        }
    }

    // Checks if the privacy button is pressed
    public function privChecker()
    {
        if (isset($_POST['privacyConfirm'])) {
            return true;
        } else {
            return false;
        }
    }

    // At least 6 characters, at least 1 number, must contain letters
    public function passwordSyntax($password)
    {
        return strlen($password) >= 6 && preg_match("/[0-9]+/", $password) && preg_match("/[[a-zA-Z]+/", $password);
    }

    // Checks if the two inputted passwords are the same
    public function checkPasswordsMatch($password, $repeatPassword)
    {
        if (empty($_POST[$password])) {
            $errors[] = 'No password provided';
        }
        if (empty($_POST[$repeatPassword])) {
            $errors[] = 'Please confirm your password';
        }
        if ($password != $repeatPassword) {
            return false;
        } else {
            return $password;
        }
    }

    public function validateEmail($email, $mysqli)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email address';
        }

        $query = "SELECT * FROM profiles WHERE email=?";
        $query = $mysqli->prepare($query);
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            return "User with this email already exists";
        } else {
            // Email is valid and unique
            return false;
        }
    }

    // Main validation function for all inputs
    public function validateInput($username, $email, $password, $confirmPassword, $mysqli)
    {
        $errors = array();
        if (empty($username)) {
            $errors[] = 'No username provided';
        } else if ($this->validateUsername($username, $mysqli)) {
            $errors[] = "This username is already taken";
        }

        if (empty($email)) {
            $errors[] = 'No email address provided';
        } else if ($error_msg = $this->validateEmail($email, $mysqli)) {
            $errors[] = $error_msg;
        }

        if (!$this->passwordSyntax($password)) {
            $errors[] = "Your password does not qualify for our requirements";
        } elseif (empty($password)) {
            $errors[] = "You must provide a password";
        }

        if (!$confirmPassword) {
            $errors[] = "Confirmation password field was left blank";
        } elseif (!$this->checkPasswordsMatch($password, $confirmPassword)) {
            $errors[] = "The passwords you entered do not match";
        }
        return $errors;
    }
}

$userManager = new UserManager();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create db connection
    global $server, $user, $password, $database;
    $mysqli = new mysqli($server, $user, $password, $database);
    if ($mysqli->connect_error) {
        die("DB connection failed: " . $mysqli->connect_error);
    }

    // Validation
    $username = sanitizeInputVar($mysqli, $_POST["username"]);
    $email = sanitizeInputVar($mysqli, $_POST["email"]);
    $password = sanitizeInputVar($mysqli, $_POST["password"]);
    $confirmPassword = sanitizeInputVar($mysqli, $_POST["repeatPassword"]);
    $errors = $userManager->validateInput($username, $email, $password, $confirmPassword, $mysqli);

    if (!$userManager->termsChecker()) {
        $errors[] = "You must agree to the terms of service.";
    }

    if (!$userManager->privChecker()) {
        $errors[] = "You must agree to the privacy policy.";
    }

    // If no errors, add user to database
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $user = new User($username, $hashedPassword, $email);

        if ($userManager->addUser($user, $mysqli)) {
            $successFlag = true;
            header('Location: login.php');
            exit;
        } else {
            $errors[] = "Registration was unsuccessful.";
        }
    } else {
        foreach ($errors as $error) {
            $errorMessage .= '<ul><li>' . $error . '</li></ul>';
        }
        $errorMessage = '<div id="confirmedError">' . $errorMessage . '</div>';
    }
}

$template->assign('success', $successFlag);
$template->assign('success_message', $successMessage);
$template->assign('requirements', $requirements->printRequirements());
$template->assign('error_message', $errorMessage);

echo $template->render();