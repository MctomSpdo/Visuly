<?php
$configPath = 'files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once './assets/token.php';
require_once './assets/user.php';

//check user token:
if (!isset($_COOKIE[$config->token->name])) {
    header("Location: ./login.php");
    exit();
}

//database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if ($db->connect_error) {
    header("Location: ./error.php");
}

$token = $_COOKIE[$config->token->name];
//check token in db:
$userId = checkTokenWRedirect($token, $config, $db);

$user = new User();
$user->DBLoadFromUserID($userId, $db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Visuly</title>

    <link rel="stylesheet" href="./files/css/main.css">

    <!-- icon library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="files/js/main.js" defer></script>
    <script src="files/js/settings.js" defer></script>
</head>

<body>
<?php
include "assets/header.php";
?>
<main>
    <nav id="setting-nav">
        <a id="nav-active" onclick="loadEditUser(this);">
            <div>
                <div class="nav-img">
                    <img src="files/img/users/user.png" alt="Home">
                </div>
                <div class="nav-txt">
                    <p>Edit User</p>
                </div>
            </div>
        </a>
        <a onclick="loadPassword(this)">
            <div>
                <div class="nav-img">
                    <img src="files/img/key.svg" alt="Home">
                </div>
                <div class="nav-txt">
                    <p>Change Password</p>
                </div>
            </div>
        </a>
    </nav>
    <div id="content">
        <div id="content-hidden">
            <p id="data-username"><?php echo $user->username?></p>
            <p id="data-email"><?php echo $user->email?></p>
            <p id="data-phoneNumber"><?php echo $user->phoneNumber?></p>
            <p id="data-desc"><?php echo $user->desc?></p>
        </div>

        <div id="current-setting-wrapper">
            <!-- this will be loaded from JS (see settings.js) -->
        </div>
    </div>
    <div>

    </div>
</main>
</body>
</html>