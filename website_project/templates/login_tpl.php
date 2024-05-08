<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Oursite - Login</title>
    <link rel="stylesheet" href="styles/login.css">
    <meta name="description" content="OurSite - Login">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <main>
    <form action="login.php" method="POST">
        <table>
            <tr>
                <td>
                    <h2>Login</h2>
                </td>
            </tr>
            <tr>
                <td>
                    <p>Username:</p><input type="text" id="username" name="username" placeholder="Username"
                        class="input" pattern="[A-Za-z0-9!@#$%^&*()_+-]+">
                </td>
            </tr>
            <tr>
                <td>
                    <p>Password:</p><input type="password" id="password" name="password" placeholder="Password"
                        class="input" pattern="[A-Za-z0-9!@#$%^&*()_+-]+">
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" id="rememberMe" name="rememberMe"> Remember me</td>
            </tr>
            <tr>
                <td>
                    <input type="submit" id="loginSubmit" name="loginSubmit" class="button" value="Login">
                </td>
            <tr>
                <td>
                    <p>Not registered yet? <a href="register.php" class="link_reg">Do it here!</a></p>
                </td>
            </tr>
        </table>
        {IF !empty(ERROR_MESSAGE)}
        {ERROR_MESSAGE}
        {ENDIF}
    </form>
    </main>
    <footer>
        <p>Copyright © 2024 OurSite</p>
        <a href="privacy_policy.php">Privacy policy</a>
        <a href="terms_and_conditions.php">Terms & Conditions</a>
    </footer>
</body>

</html>
