<?php

$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../../assets/user.php';
require_once '../../assets/token.php';
require_once '../../assets/post.php';

//check request:
if(!(isset($_POST['post']) && isset($_POST['comment']))) {
    $resp = new stdClass();
    $resp->error = "Invalid Request";
    exit(json_encode($resp));
}

$postId = $_POST['post'];
$comment = $_POST['comment'];

if(strlen($comment) < 0 || strlen($comment) > 300) {
    $resp = new stdClass();
    $resp->error = "Comment text invalid";
    exit(json_encode($resp));
}

//check login:
if (!isset($_COOKIE[$config->token->name])) {
    $resp = new stdClass();
    $resp->error = "No Permission";
    exit(json_encode($resp));
}
$token = $_COOKIE[$config->token->name];

//db connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error (004)";
    exit(json_encode($resp));
}

//check token in DB:
$userId = checkTokenWRedirect($token, $config, $db);
$user = new User();
$user->DBLoadFromUserID($userId, $db);

//get post
$post = new Post();
$post->ImgPath = $postId;

if(!$post->DBLoadFromPath($db)) {
    $resp = new stdClass();
    $resp->error = "Post does not exist";
    $db->close();
    exit(json_encode($resp));
}

//check if user can post comment:
if(!$user->canComment) {
    $resp = new stdClass();
    $resp->error = "No permission to comment";
    $db->close();
    exit(json_encode($resp));
}

//save comment to db:
$resp = new stdClass();

if($post->addComment($comment, $user->UserID, $db)) {
    $resp->comment = true;
} else {
    $resp->error = "Internal Server Error (002)";
}

echo json_encode($resp);

$db->close();
?>