<?php
require_once 'lib/eng.tpl.php';

session_start();
//If user is logged in, redirect to main
if (isset ($_SESSION["isLoggedInUser"])) {
    header("Location: main.php");
}

$userFile = 'data/profiles.csv';
$handle = fopen($userFile, 'a');
$errors = array();
$data = array();
$id = 0;
$success = false;
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
    public $id;
    public $folders;

    public function __construct($username, $hashedPassword, $email, $id, $folders)
    {
        $this->username = $username;
        $this->hashedPassword = $hashedPassword;
        $this->email = $email;
        $this->id = $id;
        $this->folders = $folders;
    }
}

class UserManager
{
    private $userFile;

    public function __construct($userFile)
    {
        $this->userFile = $userFile;
    }

    public function addUser(User $user)
    {
        $handle = fopen($this->userFile, "a");
        if ($handle !== FALSE) {
            fputcsv($handle, [
                $user->id,
                $user->username,
                $user->hashedPassword,
                $user->email,
                $user->folders
            ], ';');
            fclose($handle);
            return true;
        } else {
            return false;
        }
    }

    public function sanitizeInput($data)
    {
        $data = htmlspecialchars($data);
        return $data;
    }

    public function validateUsername($username)
    {
        $start_row = 2;
        $i = 1;
        $file = fopen($this->userFile, "r");
        while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
            if ($i >= $start_row && isset ($row[1])) { // Check if row is not empty and index 1 is set
                if ($row[1] == $username) { // Assuming username is in the second column
                    fclose($file);
                    return true;
                }
            }
            $i++;
        }
        fclose($file);
        return false;
    }
    public function termsChecker()
    {
        if (isset ($_POST['termsConfirm'])) {
            return true;
        } else {
            return false;
        }
    }

    public function privChecker()
    {
        if (isset ($_POST['privacyConfirm'])) {
            return true;
        } else {
            return false;
        }
    }

    public function passwordSyntax($password)
    {
        return strlen($password) >= 6 && preg_match("/[0-9]+/", $password) && preg_match("/[[a-zA-Z]+/", $password);
    }

    public function checkPasswordMatches($password, $repeatPassword)
    {
        if (empty ($_POST[$password])) {
            $errors[] = 'No password provided';
        }
        if (empty ($_POST[$repeatPassword])) {
            $errors[] = 'Please confirm your password';
        }
        if ($password != $repeatPassword) {
            return false;
        } else {
            return $password;
        }
    }

    public function validateEmail($email)
    {
        $file = fopen($this->userFile, "r");
        if ($file) {
            while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
                if (isset ($row[3])) {
                    if ($row[3] == $email) {
                        fclose($file);
                        return 'This email is already in use';
                    }
                }
            }
            fclose($file);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email address';
        }

        return true; // Email is valid and unique
    }

    public function addId()
    {
        $file = fopen($this->userFile, 'r');
        if ($file) {
            $idCount = 0;
            while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
                if (isset ($row)) {
                    $idCount++;
                }
            }
            fclose($file);
            return $idCount;
        }
        return 0;
    }

    public function validateInput($username, $email, $password, $confirmPassword)
    {
        $errors = array();
        if ($this->validateUsername($username)) {
            $errors[] = "This username is already taken";
        } elseif (empty ($username)) {
            $errors[] = 'No username provided';
        }

        if (empty ($email)) {
            $errors[] = 'No email address provided';
        }

        if (!$this->passwordSyntax($password)) {
            $errors[] = "Your password does not qualify for our requirements";
        } elseif (empty ($password)) {
            $errors[] = "You must provide a password";
        }

        if (!$confirmPassword) {
            $errors[] = "Confirmation password field was left blank";
        } elseif (!$this->checkPasswordMatches($password, $confirmPassword)) {
            $errors[] = "The passwords you entered do not match";
        }
        return $errors;
    }
}

$userManager = new UserManager('data/profiles.csv');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = $userManager->sanitizeInput($_POST["username"]);
    $email = $userManager->sanitizeInput($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["repeatPassword"];
    $errors = $userManager->validateInput($username, $email, $password, $confirmPassword);

    if (!$userManager->termsChecker()) {
        $errors[] = "You must agree to the terms of service.";
    }

    if (!$userManager->privChecker()) {
        $errors[] = "You must agree to the privacy policy.";
    }

    $emailValidation = $userManager->validateEmail($email);

    if ($emailValidation !== true) {
        $errors[] = $emailValidation;
    }

    if (empty ($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $id = $userManager->addId();
        $user = new User($username, $hashedPassword, $email, $id, '');

        if ($userManager->addUser($user)) {
            $success = true;
        } else {
            $errors[] = "Error writing to file.";
        }
    } else {
        foreach ($errors as $error) {
            $errorMessage .= '<li>' . $error . '</li>';
        }
        $errorMessage = '<div id="confirmedError"><ul>' . $errorMessage . '</ul></div>';
    }
}

$template->assign('success', $success);
$template->assign('success_message', $successMessage);
$template->assign('requirements', $requirements->printRequirements());
$template->assign('error_message', $errorMessage);

echo $template->render();
?>
