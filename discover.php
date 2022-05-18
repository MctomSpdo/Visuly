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
        <meta name="keywords" content="visuly, social Media, images, memes, discover">
        <meta name="description" content="Discover newest images">
        <meta name="author" content="MctomSpdo">

        <title>Discover - Visuly</title>

        <link rel="stylesheet" href="./files/css/main.css">

        <!-- icon library -->
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

        <script src="files/js/main.js" defer></script>
        <script src="files/js/discover.js" defer></script>
    </head>

    <body>
    <?php
    include "assets/header.php";
    ?>
    <main>
        <?php
        $navActive = 'discover';
        include "assets/nav.php";
        ?>
        <div id="suggested-root">
            <div id="discover-tab-nav">
                <div id="discover-selected" onclick="loadSuggested()"><a>suggested</a></div>
                <div onclick="loadDiscoverNewest()"><a>newest</a></div>
                <div onclick="loadDiscoverTop(this)"><a>top posts</a></div>
            </div>

            <div id="content" class="post-3-wrapper">

            </div>
        </div>
        <div></div>
    </main>
    </body>
    </html>

<?php
$db->close();