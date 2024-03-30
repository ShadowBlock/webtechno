<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Oursite - Register</title>
    <link rel="stylesheet" href="styles/register.css">
    <meta name="description" content="OurSite - Register">
    <meta name="author" content="geolee">
    <meta name="author" content="stlill">
    <meta name="author" content="inviin">
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
</head>

<body>
    <header>
        <a href="index.php" class="logo"></a>
        <nav class="header-right">
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
            <a class="active" href="register.php">Register</a>
        </nav>
    </header>
    <form action="register.php" method="post">
        <table>
            <tr>
                <td>
                    <h2>Registration Form</h2>
                </td>
                <td>
                    <h2>Requirements</h2>
                </td>
            </tr>
            <tr>
                <td>
                    <p>Username:</p><input type="text" id="username" name="username" placeholder="Username"
                        class="input" required>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <p>Email:</p><input type="email" id="email" name="email" placeholder="Email" class="input" required>
                </td>
                <td>

                    {IF SUCCESS === false}
                    <ul>
                        {REQUIREMENTS}
                    </ul>
                    {ENDIF}
                    {IF SUCCESS === true}
                        {SUCCESS_MESSAGE}
                    {ENDIF}
                </td>
            </tr>
            <tr>
                <td>
                    <p>Password:</p><input type="password" id="password" name="password" placeholder="Password"
                        class="input" required>
                </td>
                <td rowspan="4">
                    {IF !empty(ERROR_MESSAGE)}
                    {ERROR_MESSAGE}
                    {ENDIF}
                </td>
            </tr>
            <tr>
                <td>
                    <p>Confirm Password:</p><input type="password" id="repeatPassword" name="repeatPassword"
                        placeholder="Repeat Password" class="input" required>
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" id="termsConfirm" name="termsConfirm" required> I have read and agree to the
                    <a href="terms_and_conditions.html" class="link_term_priv">terms of service</a>.
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" id="privacyConfirm" name="privacyConfirm" required> I have read and agree to
                    the <a href="privacy_policy.html" class="link_term_priv">privacy policy</a>.</td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" id="submitRegistration" name="submitRegistration" class="button"
                        value="Register"></td>
            </tr>
            <tr>
                <td colspan="2" class="cell_center">
                    <p>Already have an account? <a href="login.php" class="link_log">Login here.</a></p>
                </td>
            </tr>
        </table>
    </form>
    <footer>
        <p>Copyright © 2024 OurSite</p>
        <a href="privacy_policy.html" class="link_term_priv">Privacy policy</a>
        <a href="terms_and_conditions.html" class="link_term_priv">Terms & Conditions</a>
    </footer>
</body>

</html>