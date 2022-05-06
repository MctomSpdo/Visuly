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

$search = "";

if(isset($_GET['search'])) {
    $search = $_GET['search'];
}
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="keywords" content="visuly, social Media, images, memes">
        <meta name="description" content="Visuly start page">
        <meta name="author" content="MctomSpdo">

        <title>Search - Visuly</title>

        <link rel="stylesheet" href="./files/css/main.css">

        <!-- icon library -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

        <script src="files/js/main.js" defer></script>
        <script src="files/js/search.js" defer></script>
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
        <div id="content">
            <div id="content-hidden">
                <p id="search-words"><?php echo $search?></p>
            </div>
            <div id="search-rootbox">
                <h1 id="search-header">Search results for: <?php echo $search?></h1>

                <div>
                    <div id="search-user-result">
                        <h2>Users</h2>

                        <div id="search-user-content">

                        </div>
                    </div>

                    <div id="search-post-result">
                        <h2>Posts</h2>

                        <div id="search-post-content" class="post-3-wrapper">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div></div>
    </main>
    </body>
    </html>

<?php
$db->close();