<?php
$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../../assets/user.php';
require_once '../../assets/token.php';
require_once '../../assets/post.php';
require_once '../../assets/util.php';

//check request
if(!(isset($_POST['post']) && isset($_POST['offset']))) {
    exit(Util::invalidRequestError());
}

$postId = $_POST['post'];
$offset = $_POST['offset'];

if(strlen($postId) == 0)  {
    exit(Util::invalidRequestError());
}

if (!isset($_COOKIE[$config->token->name])) {
    exit(Util::getLoginError());
}
$token = $_COOKIE[$config->token->name];

//db connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    exit(Util::getDBErrorJSON());
}

$userId = checkTokenWRedirect($token, $config, $db);
$user = new User();
$user->DBLoadFromUserID($userId, $db);

//get post
$post = new Post();
$post->ImgPath = $postId;

if(!$post->DBLoadFromPath($db)) {
    exit(Util::getDBErrorJSON("Post does not exist"));
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