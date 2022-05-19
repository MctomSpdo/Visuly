<?php

$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../../assets/user.php';
require_once '../../assets/token.php';
require_once '../../assets/post.php';
require_once '../../assets/util.php';

//check requests:
if (!(isset($_GET['user']) && isset($_GET['follow']))) {
    exit(Util::invalidRequestError());
}

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
//check token in db and load user:
$userId = checkTokenWRedirect($token, $config, $db);
$user = new User();
$user->DBLoadFromUserID($userId, $db);

$followUser = $_GET['user'];
$follow = $_GET['follow'];

//check if the user tries to follow itsself:
if ($followUser == $user->UUID) {
    $db->close();
    exit(Util::getErrorJSON("You can't follow yourself"));
}

//check if user exists (and is not deleted), if not send error
$pstmt = $db->prepare("select count(*) from user where uuid = ? and deleted = 0");
if (!$pstmt->bind_param("s", $followUser) || !$pstmt->execute()) {
    $db->close();
    exit(Util::getDBRequestError());
}
$userExistsRes = $pstmt->get_result();

if (!$userExistsRes) {
    $pstmt->close();
    $db->close();
    exit(Util::getDBRequestError());
}

if ($userExistsRes->num_rows == 0) {
    $pstmt->close();
    $db->close();
    exit(Util::getErrorJSON("User does not exist!"));
}
$userExistsRes->close();
$pstmt->close();

//if user wants to follow:
if ($follow == "true") {
    $pstmt = $db->prepare("insert into follow(UserID, Follows) value (?, (select UserID from user where uuid = ?))");
    if (!$pstmt->bind_param("is", $user->UserID, $followUser) || !$pstmt->execute()) {
        $pstmt->close();
        $db->close();
        exit(Util::getDBRequestError());
    }
} else if ($follow == "false") {//if user wants to unfollow
    $pstmt = $db->prepare("delete from follow where UserID = ? and Follows = (select UserID from user where uuid = ?)");
    if (!$pstmt->bind_param("is", $user->UserID, $followUser) || !$pstmt->execute()) {
        $pstmt->close();
        $db->close();
        exit(Util::getDBRequestError());
    }
} else {//invalid
    $db->close();
    exit(Util::invalidRequestError());
}

$resp = new stdClass();
$resp->success = true;
echo json_encode($resp);

$db->close();
