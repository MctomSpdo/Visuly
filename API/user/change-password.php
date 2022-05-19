<?php

require_once '../../assets/token.php';
require_once '../../assets/user.php';
require_once '../../assets/post.php';
require_once '../../assets/util.php';

//check request:
if(!isset($_POST['currentPassword']) && !isset($_POST['newPassword'])) {
    exit(Util::invalidRequestError());
}

//check passwords:
if($_POST['currentPassword'] == $_POST['newPassword']) {
    exit(Util::getErrorJSON("New Password can't be the same as the old one"));
}

$password = $_POST['newPassword'];

if(strlen($password) < 8 && Util::hasLowerCase($password) && Util::hasUpperCase($password) && preg_match('~[0-9]~', $password) == 0) {
    exit(Util::getErrorJSON("password invalid"));
}

$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

//check user token:
if(!isset($_COOKIE[$config->token->name])) {
    exit(Util::getLoginError());
}

//database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    exit(Util::getDBErrorJSON());
}

$token = $_COOKIE[$config->token->name];
//check token in db:
$userId = checkTokenWRedirect($token, $config, $db);

$user = new User();
$user->DBLoadFromUserID($userId, $db);

//values from post:
$currentPassword = $config->passwordSalt . $_POST['currentPassword'];
$newPassword = $config->passwordSalt . $_POST['newPassword'];

//check current password:
$pstmtCurrentPw = $db->prepare("select UserID, username from user where UserID = ? and password like md5(?)");
$pstmtCurrentPw->bind_param("is", $user->UserID, $currentPassword);

if(!$pstmtCurrentPw->execute()) {
    $db->close();
    $pstmtCurrentPw->close();
    exit(getDBErrorMessage());
}

$resultCurrentPw = $pstmtCurrentPw->get_result();

if($resultCurrentPw->num_rows == 0) {//if current password is wrong
    $pstmtCurrentPw->close();
    $db->close();
    exit(Util::getErrorJSON("Current password invalid!"));
}

$pstmtNewPw = $db->prepare("update user set password = md5(?) where UserID = ?");
$pstmtNewPw->bind_param("si", $newPassword, $user->UserID);

if(!$pstmtNewPw->execute()) {
    $pstmtCurrentPw->close();
    $pstmtNewPw->close();
    $db->close();
    exit(Util::invalidRequestError());
}

$resp = new stdClass();
$resp->success = true;
echo json_encode($resp);

$pstmtNewPw->close();
$pstmtCurrentPw->close();
$db->close();