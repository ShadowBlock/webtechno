<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Account Details</title>
    <link rel="stylesheet" href="styles/profile.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Change your profile here.">
    <meta name="author" content="geolee">
    <meta name="author" content="stlill">
    <meta name="author" content="inviin">
    <script src="scripts/profile.js" defer></script>
    <script src="scripts/logout.js" defer></script>
</head>
<body>
    <header>
        <a href="index.php" class="logo"></a>
        <nav class="header-right">
            <a href="index.php">Home</a>
            <a href="main.php">Main</a>
            <a class="active" href="profile.php">Account Details</a>
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
        </nav>
    </header>
    <main>
    <div class="profile-container">
        <div class="profile-info">
            <h1>Your Profile</h1>
            <div class="fields">
                <label>Username:</label>
                <span id="name">{USERNAME}</span>
            </div>
            <div class="fields">
                <label>Email:</label>
                <span id="email">{EMAIL}</span>
            </div>


            <div>
                <form id="update-profile-form" method="post" action="profile.php">
                    <input type="hidden" name="selected-profile-id" id="selected-profile-id" value="">

                    <h2>Select your profile image:</h2>
                    <div class="profile-images">
                        {PROFILE_IMAGES}
                    </div>
                </form>
            </div>
            <button id="delete-profile-button" class="delete-profile" onclick="showDelete()">Delete Profile</button>

            <div id="delete-profile-prompt" class="confirm">
                <div class="confirm-content">
                    <p>Are you sure you want to delete your profile? This process cannot be reversed.</p>
                    <table>
                        <tr>
                            <form method="post" class="button-container">
                                <input type="hidden" id="delete" name="deleteAction" value="true">
                            <td><input type="submit" id="confirm-delete" value="Yes"></td>
                            </form>
                            <td><button id="cancel-delete" onclick="hideDelete()">No</button></td>
                        </tr>
                    </table>
                </div>
            </div>
            <button id="confirm-changes" class="confirm-button" onclick="confirmInput()">Confirm Changes</button>
        </div>
    </div>
    </main>
    <footer>
        <p>Copyright © 2024 OurSite</p>
        <a href="privacy_policy.php">Privacy policy</a>
        <a href="terms_and_conditions.php">Terms & Conditions</a>
    </footer>

</body>
</html>
