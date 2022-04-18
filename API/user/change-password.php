<?php
function getDBErrorMessage() {
    $resp = new StdClass();
    $resp->error = "Internal Server error (E004)";
    return json_encode($resp);
}

function hasLowerCase($str) {
    return strtoupper($str) != $str;
}

function hasUpperCase($str) {
    return strtolower($str) != $str;
}

//check request:
if(!isset($_POST['currentPassword']) && !isset($_POST['newPassword'])) {
    $resp = new stdClass();
    $resp->error = "Invalid Request";
    exit(json_encode($resp));
}

//check passwords:
if($_POST['currentPassword'] == $_POST['newPassword']) {
    $resp = new stdClass();
    $resp->error = "New Password can't be the same as the old one";
    exit(json_encode($resp));
}

$password = $_POST['newPassword'];

if(strlen($password) < 8 && hasLowerCase($password) && hasUpperCase($password) && preg_match('~[0-9]~', $password) == 0) {
    $resp = new stdClass();
    $resp->error = "password invalid";
    exit(json_encode($resp));
}

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
    exit();
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
    $resp = new stdClass();
    $resp->error = "Current password invalid!";
    $pstmtCurrentPw->close();
    $db->close();
    exit(json_encode($resp));
}

$pstmtNewPw = $db->prepare("update user set password = md5(?) where UserID = ?");
$pstmtNewPw->bind_param("si", $newPassword, $user->UserID);

if(!$pstmtNewPw->execute()) {
    $pstmtCurrentPw->close();
    $pstmtNewPw->close();
    $db->close();
    exit(getDBErrorMessage());
}

$resp = new stdClass();
$resp->success = true;
echo json_encode($resp);

$pstmtNewPw->close();
$pstmtCurrentPw->close();
$db->close();