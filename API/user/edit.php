<?php
$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));
$emailReg = '/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';


require_once '../../assets/token.php';
require_once '../../assets/user.php';
require_once '../../assets/post.php';

//check request:
if(!(isset($_POST['username']) && isset($_POST['email']) && isset($_POST['phonenumber']))) {
    $resp = new stdClass();
    $resp->error = "Invalid Request";
    exit(json_encode($resp));
}

//check values:
$username = $_POST['username'];
$phoneNumber  = $_POST['phonenumber'];
$email = $_POST['email'];

if(strlen($username) < 4 || strlen($username) > 30) {
    $resp = new stdClass();
    $resp->error = "Invalid Username";
    exit(json_encode($resp));
}

if(preg_match($emailReg, $email) == 0) {
    $resp = new stdClass();
    $resp->error = "Invalid email";
    exit(json_encode($resp));
}

if(strlen($phoneNumber) < 4 || strlen($phoneNumber) > 15) {
    $resp = new stdClass();
    $resp->error = "Invalid phone number";
    exit(json_encode($resp));
}

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

$pstmt = $db->prepare("update user set username = ?, phoneNumber = ?, email = ? where UserID = ?");

if($pstmt == false) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error(E002)";
    exit(json_encode($resp));
}

$pstmt->bind_param("sisi", $username, $phoneNumber, $email, $user->UserID);
if(!$pstmt->execute()) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error(E002)";
    exit(json_encode($resp));
}

$resp = new stdClass();
$resp->success = true;
echo json_encode($resp);

$db->close();