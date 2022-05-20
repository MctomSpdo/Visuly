<?php
$configPath = 'files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once './assets/token.php';
require_once './assets/user.php';
require_once './assets/post.php';

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


//define user to show:
$userDisplayId = $user->UUID;
if(isset($_GET['user'])) {
    $userDisplayId = $_GET['user'];
}

$followers = User::DBGetFollowsList($db, $userDisplayId);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="visuly, social Media, images, memes, user">
    <meta name="description" content="Follows of user">
    <meta name="author" content="MctomSpdo">
    <title>Follows - Visuly</title>

    <link rel="stylesheet" href="./files/css/main.css">
    <link rel="icon" type="image/x-icon" href="./files/img/icon.svg">

    <!-- icon library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="files/js/main.js" defer></script>
    <script src="files/js/user.js" defer></script>
</head>

<body">
<?php
include "assets/header.php";
?>
<main>
    <?php
    include "assets/nav.php";
    ?>
    <div id="content">
        <h1>Follows:</h1>
        <div class="user-list-display">
            <script>
                function redirectUser(element) {
                    window.location.href = './user.php?user=' + element.id;
                }
            </script>
            <?php
            if($followers === false) {
                echo ":/ An unknown error occurred, please try again later";
            } else {
                if(sizeof($followers) == 0) {
                    echo "This user has no followers yet!";
                } else {
                    foreach($followers as $display) {
                        echo ('<div class="list-userelement" id="' . $display['uuid'] . '" onclick="redirectUser(this)">
                                <div>
                                    <img src="./files/img/users/' . $display['profilePic'] . '" alt="User image">
                                </div>
                                <div>
                                    <h2>' . $display['username'] . '</h2>
                                </div>
                            </div>');
                    }
                }
            }
            ?>
        </div>
    </div>
    <div></div>
</main>
</body>
</html>
<?php
$db->close();
?>
