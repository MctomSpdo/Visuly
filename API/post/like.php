<?php

$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../../assets/user.php';
require_once '../../assets/token.php';
require_once '../../assets/post.php';

//check request:
if(!(isset($_POST['post']) && isset($_POST['like']))) {
    $resp = new stdClass();
    $resp->error = "Invalid Request";
    exit(json_encode($resp));
}

//check login:
if (!isset($_COOKIE[$config->token->name])) {
    header("Location: ./login.php");
    exit();
}

//db connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error (E004)";
    exit(json_encode($resp));
}

$token = $_COOKIE[$config->token->name];
//check token in db and load user:
$userId = checkTokenWRedirect($token, $config, $db);
$user = new User();
$user->DBLoadFromUserID($userId, $db);

//get post to like:
$post = new Post();
$post->ImgPath = $_POST['post'];
if(!$post->DBLoadFromPath($db)) {//when post does not exist
    $resp = new stdClass();
    $resp->error = "Post does not exist";
    $db->close();
    exit(json_encode($resp));
}

//set like:
if($_POST['like'] == 'like') {
    if(!$post->DBSaveLike($post->PostId, $user->UserID, $db)) {
        $resp = new stdClass();
        $resp->error = "Internal Server Error (E002)";
        $db->close();
        exit(json_encode($resp));
    }
} else if($_POST['like'] == 'unlike') {
    if(!$post->DBDeleteLike($post->PostId, $user->UserID, $db)) {
        $resp = new stdClass();
        $resp->error = "Internal Server Error (E002)";
        $db->close();
        exit(json_encode($resp));
    }
} else {
    $resp = new stdClass();
    $resp->error = "Invalid Request";
    $cause = $_GET['like'];
    $resp->cause = "like: $cause";
    $db->close();
    exit(json_encode($resp));
}
$resp = new stdClass();
$resp->success = true;
$resp->likes = $post->getLikes($db);
echo json_encode($resp);

$db->close();
