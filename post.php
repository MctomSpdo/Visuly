<?php
$configPath = 'files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once './assets/user.php';
require_once  './assets/token.php';
require_once './assets/post.php';

//check user token:
if (!isset($_COOKIE[$config->token->name])) {
    header("Location: ./login.php");
    exit();
}

if(!isset($_GET['post'])) {
    header("Location: ./");
    exit;
}

//database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    header("Location: ./error.php");
    exit;
}

$token = $_COOKIE[$config->token->name];
//check token in db:
$userId = checkTokenWRedirect($token, $config, $db);

$user = new User();
$user->DBLoadFromUserID($userId, $db);

$postFound = false;
$post = new Post();
$postUser = new User();

$post->ImgPath = $_GET['post'];
$postFound = $post->DBLoadFromID($db);
$postUser->DBLoadFromUserID($post->fromUser, $db);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post->Title?> - Visuly</title>

    <link rel="stylesheet" href="./files/css/main.css">

    <!-- icon library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="./files/js/main.js" defer></script>
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
        <div class="post" id="<?php echo $post->ImgPath?>">
            <div class="post-header">
                <a href="./user.php?user=<?php echo $postUser->UUID?>" class="post-user-wrapper">
                    <div class="post-user-image">
                        <img src=".<?php echo $config->userImageFolder . "/" . $postUser->profilePic?>" alt="User">
                    </div>
                    <div class="post-user-name">
                        <p><?php echo $postUser->username?></p>
                    </div>
                    <div></div>
                </a>

                <div class="post-title-wrapper">
                    <h3 onclick="redirectPost(this);"><?php echo $post->Title?></h3>
                </div>
            </div>
            <div class="post-img">
                <div class="post-img-wrapper">
                    <img src=".<?php echo $config->post->defaultDir . "/" . $post->getImagePath()?>" alt="<?php echo $post->Title?>">
                </div>
            </div>
            <div class="post-body">
                <div class="post-interaction-wrapper">
                    <div class="post-like">
                        <div class="post-interaction-imgwrapper">
                            <?php $userHasLiked = $post->DBUserHasLiked($user->UserID, $db); ?>
                            <img src="./files/img/<?php echo ($userHasLiked) ? "heart_filled" : "heart"?>.svg" alt="Likes" onclick="likeButtonPress(this);" <?php echo ($userHasLiked) ? 'class="liked"' : ""?>>
                        </div>
                        <div class="post-interaction-textwrapper">
                            <p><?php
                                $likes =  $post->getLikes($db);
                                if($likes === false) {
                                    echo "?";
                                } else if ($likes == 0) {
                                    echo "no likes";
                                } else if($likes == 1) {
                                    echo $likes . " like";
                                } else {
                                    echo $likes . " likes";
                                }
                                ?></p>
                        </div>
                    </div>
                    <div class="post-comment">
                        <div class="post-interaction-imgwrapper">
                            <img src="./files/img/comment.svg" alt="Comment">
                        </div>
                        <div class="post-interaction-textwrapper">
                            <p><?php
                                $comments = $post->getCommentAmount($db);
                                if($comments === false) {
                                    echo "?";
                                } else if ($comments == 0) {
                                    echo "no comments";
                                } else if ($comments == 1) {
                                    echo $comments . "comments";
                                } else {
                                    echo $comments;
                                }
                                ?></p>
                        </div>
                    </div>
                    <div class="post-share" onclick="shareEventHandler(this);">
                        <div class="post-interaction-imgwrapper">
                            <img src="./files/img/share.svg" alt="Share">
                        </div>
                        <div class="post-interaction-textwrapper">
                            <p>Share</p>
                        </div>
                    </div>
                </div>
                <div class="post-descr">
                    <p><?php echo $post->Desc?></p>
                </div>
            </div>
            <div class="post-footer"></div>
        </div>
    </div>
    <div></div>
</main>
</body>
</html>
