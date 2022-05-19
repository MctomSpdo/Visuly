<?php

$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../../assets/user.php';
require_once '../../assets/token.php';
require_once '../../assets/post.php';
require_once '../../assets/util.php';

//check request:
if(!(isset($_POST['post']) && isset($_POST['comment']))) {
    exit(Util::invalidRequestError());
}

$postId = $_POST['post'];
$comment = $_POST['comment'];

if(strlen($comment) == 0 || strlen($comment) > 300) {
    exit(Util::getErrorJSON("Comment text invalid"));
}

//check login:
if (!isset($_COOKIE[$config->token->name])) {
    exit(Util::getError("No Permission"));
}
$token = $_COOKIE[$config->token->name];

//db connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    exit(Util::getDBErrorJSON());
}

//check token in DB:
$userId = checkTokenWRedirect($token, $config, $db);
$user = new User();
$user->DBLoadFromUserID($userId, $db);

//get post
$post = new Post();
$post->ImgPath = $postId;

if(!$post->DBLoadFromPath($db)) {
    $db->close();
    exit(Util::getErrorJSON("Post does not exist"));
}

//check if user can post comment:
if(!$user->canComment) {
    $db->close();
    exit(Util::getErrorJSON("No permission to comment"));
}

//save comment to db:
$resp = new stdClass();

if($post->addComment($comment, $user->UserID, $db)) {
    $resp->comment = true;
} else {
    $resp->error = Util::getDBErrorJSON();
}

echo json_encode($resp);

$db->close();
?>