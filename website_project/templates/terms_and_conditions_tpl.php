<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>OurTasks - Terms and Conditions</title>
    <link rel="stylesheet" href="styles/terms_and_conditions.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <meta name="description" content="Terms and Conditions page for OurTasks.">
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
            <h1>Terms and Conditions</h1>
            <p><strong>Last Updated:</strong> 23/02/2024</p>

            <h2>1. Acceptance of Terms</h2>
            <p>By accessing or using OurSite's services for OurTasks, you agree to comply with and be bound by these
                terms and conditions. If you do not agree, do not use our services.</p>

            <h2>2. User Accounts</h2>
            <p>a. To access certain features, you may need to create an account. You are responsible for maintaining the
                confidentiality of your account information.</p>
            <p>b. Provide accurate and complete information when creating an account.</p>

            <h2>3. Privacy Policy</h2>
            <p>Our privacy policy explains how we collect, use, and protect your personal information. By using our
                services, you agree to our privacy policy.</p>

            <h2>4. Use of Services</h2>
            <p>a. Use our services for lawful purposes only.</p>
            <p>b. You are responsible for all activities conducted under your account.</p>
            <p>c. We may modify or discontinue any part of our services without notice.</p>

            <h2>5. Intellectual Property</h2>
            <p>a. All content and materials on OurTasks are the property of OurSite and are protected by intellectual
                property laws.</p>
            <p>b. Do not reproduce, distribute, or create derivative works without explicit permission.</p>

            <h2>6. User-Generated Content</h2>
            <p>a. Users may submit content to OurTasks. By doing so, you grant OurSite a non-exclusive, worldwide,
                royalty-free license to use, modify, and distribute the content.</p>
            <p>b. Ensure your content does not violate third-party rights or laws.</p>

            <h2>7. Limitation of Liability</h2>
            <p>a. OurSite is not liable for any direct, indirect, incidental, consequential, or punitive damages arising
                from your use of our services.</p>
            <p>b. We do not warrant that our services will be error-free or uninterrupted.</p>

            <h2>8. Termination</h2>
            <p>We may terminate or suspend your account without notice if you violate these terms and conditions.</p>

            <h2>9. Governing Law and European Laws</h2>
            <p>a. These terms are governed by the laws of Estonia. Any legal action must be brought in the courts
                located within Tallinn, Estonia.</p>
            <p>b. These terms comply with applicable European Union laws, including the General Data Protection
                Regulation (GDPR). For any data-related concerns, contact our Data Protection Officer at <a
                    href="mailto:dpo@ourtasks.ee">dpo@ourtasks.ee</a>.</p>

            <h2>10. Contact Information</h2>
            <p>OurSite<br>
                Raja 4c, Tallinn, Estonia<br>
                Email: <a href="mailto:info@ourtasks.ee">info@ourtasks.ee</a></p>

            <h2>11. Changes to Terms</h2>
            <p>We reserve the right to update or modify these terms at any time. The date of the last update will be
                displayed at the beginning of the document.</p>
        </div>
    </main>
    <footer>
        <p>Copyright © 2024 OurSite</p>
        <a href="privacy_policy.php">Privacy policy</a>
        <a class="active" href="terms_and_conditions.php">Terms & Conditions</a>
    </footer>
    <script src="scripts/logout.js" defer></script>
</body>

</html>