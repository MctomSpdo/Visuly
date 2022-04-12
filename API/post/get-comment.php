<?php
$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../../assets/user.php';
require_once '../../assets/token.php';
require_once '../../assets/post.php';

//check request
if(!(isset($_POST['post'])) && isset($_POST['offset'])) {
    $resp = new stdClass();
    $resp->error = "Invalid Request";
    exit(json_encode($resp));
}

$postId = $_POST['post'];
$offset = $_POST['offset'];

if(strlen($postId) == 0)  {
    $resp = new stdClass();
    $resp->error = "invalid Request";
    exit(json_encode($resp));
}

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

$res = $post->getComments($offset, $db);

$result = array();

foreach ($res as $comment) {
    $newComment = new stdClass();
    $newComment->content = $comment[1];
    $newComment->user = $comment[0];
    $newComment->userImage = $comment[2];
    $newComment->userId = $comment[3];
    array_push($result, $newComment);
}

echo json_encode($result);

$db->close();

?>