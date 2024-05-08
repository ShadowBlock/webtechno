<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>OurTasks - Privacy Policy</title>
    <link rel="stylesheet" href="styles/privacy_policy.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <meta name="description" content="Privacy policy page for OurTasks.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="geolee">
    <meta name="author" content="stlill">
    <meta name="author" content="inviin">
</head>

<body>
    <header>
        <a href="index.php" class="logo"></a>
        <div class="header-right">
            <a href="index.php">Home</a>
            {IF LOGGED_IN === false}
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
            {ENDIF}
            {IF LOGGED_IN === true}
            <a href="main.php">Main</a>
            <a href="profile.php">Account Details</a>
            <button id="logout-button" class="logout-button" onclick="showLogOut()">Log Out</button>

            <div id="logout-prompt" class="confirm">
                <div class="confirm-content">
                    <p>Are you sure you want to log out?</p>
                    <table>
                        <form method="post" class="button-container">
                            <input type="hidden" id="logout" name="logoutAction" value="true">
                            <td><input type="submit" id="confirm-logout" value="Yes"></td>
                        </form>
                        <td><button id="cancel-logout" onclick="hideLogOut()">No</button></td>
                    </table>
                </div>
            </div>
            {ENDIF}
        </div>
    </header>
    <main>
        <div class="text">
            <h1>Privacy Policy</h1>
            <p><strong>Last Updated:</strong> 23/02/2024</p>

            <h2>1. Introduction</h2>
            <p>Welcome to OurTasks. This Privacy Policy explains how we collect, use, and protect your personal
                information. By using our services, you agree to the terms outlined in this policy.</p>

            <h2>2. Information We Collect</h2>
            <p>We collect both personal and non-personal information. Personal information may include legal name,
                birthday, password, IP address, email. Non-personal information includes username.</p>

            <h2>3. How We Use Your Information</h2>
            <p>We use the collected information for purposes such as creating tasks, modifying tasks, assigning tasks,
                signing in, signing up.</p>

            <h2>4. Information Sharing</h2>
            <p>We may share your information with the university of TalTech and their associates.</p>

            <h2>5. Your Choices</h2>
            <p>You have the right to decline of any personal data collection by not signing up and using the website.
            </p>

            <h2>6. Security</h2>
            <p>We take appropriate measures to secure your information. However, no method of transmission over the
                internet or electronic storage is 100% secure.</p>

            <h2>7. Changes to Privacy Policy</h2>
            <p>We reserve the right to update or modify this Privacy Policy at any time. The date of the last update
                will be displayed at the beginning of the document.</p>

            <h2>8. Contact Information</h2>
            <p>OurSite, Raja 4c, Tallinn, Estonia<br>Email: <a href="mailto:info@ourtasks.ee">info@ourtasks.ee</a></p>
        </div>
    </main>
    <footer>
        <p>Copyright © 2024 OurSite</p>
        <a class="active" href="privacy_policy.php">Privacy policy</a>
        <a href="terms_and_conditions.php">Terms & Conditions</a>
    </footer>
    <script src="scripts/logout.js" defer></script>
</body>

</html>
