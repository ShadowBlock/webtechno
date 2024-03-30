<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Oursite - Login</title>
    <link rel="stylesheet" href="styles/login.css">
    <meta name="description" content="OurSite - Login">
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
            <a class="active" href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </header>
    <form action="login.php" method="POST">
        <table style="border: 1px solid black;">
            <tr>
                <td>
                    <h2>Login to Our Site!</h2>
                </td>
            </tr>
            <tr>
                <td>
                    <p>Username:</p><input type="text" id="username" name="username" placeholder="Username"
                        class="input">
                </td>
            </tr>
            <tr>
                <td>
                    <p>Password:</p><input type="password" id="password" name="password" placeholder="Password"
                        class="input">
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" id="rememberMe" name="rememberMe">Remember me</td>
            </tr>
            <tr>
                <td>
                    <input type="submit" id="loginSubmit" name="loginSubmit" class="button" value="Login">
                </td>
            <tr>
                <td colspan=2 style="text-align: center;">
                    <p style="text-align: center;">Not registered yet? <a href="register.html" class="link_reg">Do it
                            here!</a></p>
                </td>
            </tr>
        </table>
        {IF !empty(ERROR_MESSAGE)}
        {ERROR_MESSAGE}
        {ENDIF}
    </form>
    <footer>
        <p>Copyright © 2024 OurSite</p>
        <a href="privacy_policy.html">Privacy policy</a>
        <a href="terms_and_conditions.html">Terms & Conditions</a>
    </footer>
</body>

</html>