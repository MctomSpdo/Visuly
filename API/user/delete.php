<?php

require_once '../../assets/token.php';
require_once '../../assets/util.php';

$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

//check login:
if (!isset($_COOKIE[$config->token->name])) {
    exit(Util::getLoginError());
}

//db connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if ($db->connect_error) {
    exit(Util::getDBErrorJSON());
}

$token = $_COOKIE[$config->token->name];
//check token in db:
$userId = checkTokenWRedirect($token, $config, $db);

$pstmt = $db->prepare("update user set deleted = 1 where UserID = ?");

if($pstmt == false) {
    $db->close();
    exit(Util::getDBRequestError());
}

if(!($pstmt->bind_param("i", $userId) && $pstmt->execute())) {
    $db->close();
    exit(Util::getDBRequestError());
}

//delete all posts related to the user:
$postpstmt = $db->prepare("update post set isDeleted = 1 where UserID = ?");

if($postpstmt == false) {
    $db->close();
    exit(Util::getDBRequestError());
}

if(!($postpstmt->bind_param("i", $userId) && $postpstmt->execute())) {
    $db->close();
    exit(Util::getDBRequestError());
}

$resp = new stdClass();
$resp->success = true;
echo json_encode($resp);

$postpstmt->close();
$pstmt->close();
$db->close();

?>