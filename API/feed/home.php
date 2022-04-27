<?php
$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../../assets/token.php';
require_once '../../assets/user.php';
require_once '../../assets/post.php';

//check user token:
if(!isset($_COOKIE[$config->token->name])) {
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

$offset = 0;
if(isset($_GET['offset'])) {
    $offset = $_GET['offset'];
}

$postArr = Post::loadNewestPosts($db, $offset);


$resp = "no posts yet";

switch ($postArr) {
    case null:
        $resp = new stdClass();
        $resp->posts = "no posts yet";
        break;
    case -1:
        header("Location: ./error.php");
        break;
    default:
        $resp = new stdClass();
        $resp->posts = array();

        foreach ($postArr as $posts) {
            $userPost = new User();
            $userPost->DBLoadFromUserID($posts->fromUser, $db);

            //response item:
            $postRest = new stdClass();
            $postRest->title = $posts->Title;
            $postRest->description = $posts->Desc;
            $postRest->postId = $posts->ImgPath;
            $postRest->path = $posts->getImagePath();

            $postRest->hasLiked = $posts->DBUserHasLiked($user->UserID, $db);
            $postRest->likes = $posts->getLikes($db);

            $postRest->postedFrom = $userPost->username;
            $postRest->postedFromImage = $userPost->profilePic;
            $postRest->postedFromID = $userPost->UUID;

            $postRest->comments = $posts->getCommentAmount($db);

            $resp->posts[] = $postRest;
        }



        break;
}

echo json_encode($resp);
$db->close();
?>