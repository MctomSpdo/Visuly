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

if (isset($_GET['user'])) {
    $userDisplay = new User();
    $userDisplay->UUID = $_GET['user'];
    $userDisplay->DBLoadFromUUID($db);
} else {
    $userDisplay = $user;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="visuly, social Media, images, memes, user">
    <meta name="description" content="User: <?php echo $userDisplay->username?>">
    <meta name="author" content="MctomSpdo">
    <title><?php echo $userDisplay->username ?> - Visuly</title>

    <link rel="stylesheet" href="./files/css/main.css">

    <!-- icon library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="files/js/main.js" defer></script>
    <script src="files/js/user.js" defer></script>
</head>

<body id="<?php echo $userDisplay->UUID?>">
<?php
include "assets/header.php";
?>
<main>
    <?php
    include "assets/nav.php";
    ?>
    <div id="content">
        <div id="user">
            <div id="user-header">
                <div id="user-header-pfp">
                    <img src="./files/img/users/<?php echo $userDisplay->profilePic ?>"
                         alt="<?php echo $userDisplay->username ?>">
                </div>
                <div id="user-header-info">
                    <div id="user-info-username">
                        <h1><?php echo $userDisplay->username ?></h1>
                    </div>
                    <div id="user-info-body">
                        <p><?php
                            if ($userDisplay->desc == "") {
                                echo "This User doesn't have a bio yet!";
                            } else {
                                echo $userDisplay->desc;
                            } ?></p>
                        <div id="user-advancedInfo">
                            <div>
                                <p><?php echo $userDisplay->DBGetPosts($db)?> Posts</p>
                            </div>
                            <div>
                                <p><?php
                                    $followers = $userDisplay->DBGetFollowers($db);
                                    switch ($followers) {
                                        case -1:
                                            header("Location: ./error.php");
                                            break;
                                        case 0:
                                            echo "no Followers";
                                            break;
                                        case 1:
                                            echo "1 Follower";
                                            break;
                                        default:
                                            echo $followers . "Followers";

                                    } ?></p>
                            </div>
                            <div>
                                <p><?php
                                    $follows = $userDisplay->DBGetFollows($db);
                                    switch ($followers) {
                                        case -1:
                                            header("Location: ./error.php");
                                            break;
                                        case 0:
                                            echo "Follows none";
                                            break;
                                        default:
                                            echo $follows . " Follows";
                                            break;
                                    }
                                    ?></p>
                            </div>
                            <div>
                                <p><?php echo $userDisplay->DBJoinDateFormat("%M %Y", $db) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="user-posts" class="post-3-wrapper">

            </div>
        </div>
    </div>
    <div></div>
</main>
</body>
</html>
<?php
$db->close();
?>
