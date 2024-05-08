<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>OurTasks - The Only Task Manager You Need!</title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <meta name="description" content="Index page of OurSite Task Manager.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="geolee">
    <meta name="author" content="stlill">
    <meta name="author" content="inviin">
</head>

<body>
    <header>
        <a href="index.php" class="logo"></a>
        <div class="header-right">
            <a class="active" href="index.php">Home</a>
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
        <div id="first-section">
            <h1>Start organizing your work today!</h1>
            <p>Organizing your tasks has never been easier. With our platform your team members will thank you for
                helping them.</p>
            {IF LOGGED_IN === false}
            <a href="register.php">REGISTER TODAY</a>
            {ENDIF}
            {IF LOGGED_IN === true}
            {WELCOME_MESSAGE}
            {ENDIF}
        </div>
        <h2>Why choose us?</h2>
        <div class="container">
            <div class="box">
                <img src="img/cash.png" alt="" width="50">
                <h3>Free Service</h3>
                <p>OurTasks offers a free service to users, allowing them to create, modify and assign tasks. This
                    service is provided without any monetary cost to users. By using our free service, you acknowledge
                    and agree to the terms. While the service is free, certain features or functionalities may be
                    limited, and additional premium services may be available for purchase.</p>
            </div>
            <div class="box">
                <img src="img/customer.png" alt="" width="50">
                <h3>Fast Customer Support</h3>
                <p>At OurTasks, we take pride in providing fast and responsive customer support to address your
                    inquiries and concerns promptly. Our dedicated support team is committed to ensuring a seamless
                    experience for our users. If you have any questions, encounter issues, or simply need assistance,
                    our customer support team is here to help.</p>
            </div>
            <div class="box">
                <img src="img/website.png" alt="" width="50">
                <h3>Easy to Use</h3>
                <p>OurTasks is designed to be user-friendly, providing a seamless and intuitive experience for all
                    users. Whether you are a first-time user or a seasoned professional, our platform is straightforward
                    and easy to navigate. We have invested in creating a clean and simple interface, ensuring that you
                    can quickly access and utilize all the features without unnecessary complexity.</p>
            </div>
        </div>
    </main>
    <footer>
        <p>Copyright © 2024 OurSite</p>
        <a href="privacy_policy.php">Privacy policy</a>
        <a href="terms_and_conditions.php">Terms & Conditions</a>
    </footer>
    <script src="scripts/logout.js" defer></script>
</body>

</html>