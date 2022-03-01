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

if($db->connect_error) {
    header("Location: ./error.php");
}

$token = $_COOKIE[$config->token->name];
//check token in db:
$userId = checkTokenWRedirect($token, $config, $db);

$user = new User();
$user->DBLoadFromUserID($userId, $db);

if(isset($_GET['user'])) {
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
    <title>Home</title>

    <link rel="stylesheet" href="./files/css/main.css">

    <!-- icon library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
<?php
include "assets/header.php";
?>
<main>
    <?php
    include "assets/nav.html";
    ?>
    <div id="content">
        <div id="user">
            <div id="user-header">
                <div id="user-header-pfp">
                    <img src="./files/img/users/<?php echo $userDisplay->profilePic?>" alt="<?php echo $userDisplay->username?>">
                </div>
                <div id="user-header-info">
                    <div id="user-info-username">
                        <h1><?php echo $userDisplay->username?></h1>
                    </div>
                    <div id="user-info-body">
                        <p><?php
                            if($userDisplay->desc == "") {
                                echo "This User doesn't have a bio yet!";
                            } else {
                                echo $userDisplay->desc;
                            }?></p>
                        <div id="user-advancedInfo">
                            <div>
                                <p>12 Posts</p>
                            </div>
                            <div>
                                <p>XXX Followers</p>
                            </div>
                            <div>
                                <p>XXXX Following</p>
                            </div>
                            <div>
                                <p><?php echo $userDisplay->DBJoinDateFormat("%M %Y", $db)?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="user-posts">

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
