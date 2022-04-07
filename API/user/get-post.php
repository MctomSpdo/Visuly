<?php
$configPath = '../../files/config.json';

require_once '../../assets/token.php';
require_once '../../assets/user.php';
require_once '../../assets/post.php';

$config = json_decode(file_get_contents($configPath));

if(!isset($_GET['user'])) {
    $resp = new stdClass();
    $resp->error = "Invalid Request";
    exit(json_encode($resp));
}

$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error (E004)";
    exit(json_encode($resp));
}

$token = $_COOKIE[$config->token->name];
$userId = checkTokenWRedirect($token, $config, $db);
$user = new User();
$user->DBLoadFromUserID($userId, $db);

//requestUser:
$reqUser = new User();
$reqUser->UUID = $_GET['user'];
$reqUser->DBLoadFromUUID($db);

//get posts:
$posts = $reqUser->DBGetPostIds($db);

$resp = new stdClass();
$resp->posts = array();

foreach ($posts as $postUuid) {
    $postUuid = $postUuid[0];
    $post = new Post();
    $post->ImgPath = $postUuid;
    $post->DBLoadFromPath($db);

    $postRest = new stdClass();
    $postRest->title = $post->Title;
    $postRest->description = $post->Desc;
    $postRest->postId = $post->ImgPath;
    $postRest->path = $post->getImagePath();

    $postRest->hasLiked = $post->DBUserHasLiked($user->UserID, $db);
    $postRest->likes = $post->getLikes($db);
    $postRest->comments = $post->getCommentAmount($db);

    $postRest->postedFrom = $reqUser->username;
    $postRest->postedFromImage = $reqUser->profilePic;
    $postRest->postedFromID = $reqUser->UUID;

    array_push($resp->posts, $postRest);
}

echo json_encode($resp);

$db->close();
?>