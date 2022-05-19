<?php
$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../../assets/token.php';
require_once '../../assets/user.php';
require_once '../../assets/post.php';
require_once '../../assets/util.php';

//check request:
if(!(isset($_POST['username']) && isset($_POST['email']) && isset($_POST['phonenumber']) && isset($_POST['description']))) {
    exit(Util::invalidRequestError());
}

//check values:
$username = $_POST['username'];
$phoneNumber  = $_POST['phonenumber'];
$email = $_POST['email'];
$desc = $_POST['description'];

if(strlen($desc) > 300) {
    exit(Util::getErrorJSON("Invalid Description"));
}

if(strlen($username) < 4 || strlen($username) > 30) {
    exit(Util::getErrorJSON("Invalid Username"));
}

if(!Util::isEmail($email)) {
    exit(Util::getErrorJSON("Invalid email"));
}

if(strlen($phoneNumber) > 15 && Util::hasNumeric($phoneNumber)) {
    exit(Util::getErrorJSON("Invalid phone number"));
}

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

$pstmt = $db->prepare("update user set username = ?, phoneNumber = ?, email = ?, description = ? where UserID = ?");

if($pstmt == false) {
    exit(Util::getDBRequestError());
}

$pstmt->bind_param("sissi", $username, $phoneNumber, $email,$desc, $user->UserID);
if(!$pstmt->execute()) {
    exit(Util::getDBRequestError());
}

$resp = new stdClass();
$resp->success = true;
echo json_encode($resp);

$db->close();