<?php

//https://github.com/PHPMailer/PHPMailer/blob/master/examples/smtp.phps

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require '../../vendor/autoload.php';

require_once '../../assets/token.php';
require_once '../../assets/user.php';
require_once '../../assets/post.php';
require_once '../../assets/util.php';

function getSuccess():string {
    $resp = new stdClass();
    $resp->success = true;
    return json_encode($resp);
}

$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

//check request:
if(!isset($_GET['username'])) {
    exit(Util::invalidRequestError());
}

//check values:
$username = $_GET['username'];

if(strlen($username) < 4) {
    exit(Util::getErrorJSON("Invalid Username"));
}

//check if email is even enabled:
if($config->email->enable !== true) {
    exit(Util::getErrorJSON("Emailing is not enabled!"));
}

//database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    exit(Util::getDBErrorJSON());
}

//get Userid
$pstmt = null;
if(!($pstmt = $db->prepare("select UserID from user where username = ?"))) {
    exit(Util::getDBRequestError());
}

if(!($pstmt->bind_param("s", $username)
    && $pstmt->execute())) {
    exit(Util::getDBRequestError());
}

$result = $pstmt->get_result();
if($result == false) {
    $pstmt->close();
    $db->close();
    exit(Util::getDBRequestError());
}

//if there is no valid user, respond with a success (for security reasons)
if($result->num_rows == 0 || $result->num_rows == 1) {
    //sleep for 100 - 400 to compensate for less db requests
    sleep(rand(100, 100) / 400);
}

//save userID:
$userID = $result->fetch_all()[0][0];
//free up resources for next request:
$result->close();
$pstmt->close();

//get values for the password reset:
$uuid = Util::generateRandomString(60);
$code = rand(1000, 9999);

//insert into database:
if(($pstmt = $db->prepare("insert into passwordreset (UserID, uuid, code, created, validUntil)
values (?, ?, ?, now(), (now() + INTERVAL 10 MINUTE))")) === false) {
    exit(Util::getDBRequestError());
}

if(!($pstmt->bind_param("isi", $userID, $uuid, $code) | $pstmt->execute())) {
    exit(Util::getDBRequestError());
}

//*************************************** SEND EMAIL

//set timezone to UTC
date_default_timezone_set('Etc/UTC');

$mail = new PHPMailer();
//set SMTP as protocol:
$mail->isSMTP();

//enable debugging based on config file:
if($config->email->debugging == "SERVER") {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
} else if ($config->email->debugging == "CLIENT") {
    $mail->SMTPDebug = SMTP::DEBUG_CLIENT;
} else if ($config->email->debugging == "OFF") {
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
} else {
    $mail->SMTPDebug = $config->email->debugging;
}

//set host settings:
$mail->Host = $config->email->host;
$mail->Port = $config->email->port;

//smtp auth:
$smtpAuth = $config->email->SMTPAuth;
if($smtpAuth === true) {
    $mail->SMTPAuth = true;
    $mail->Username = $config->email->username;
    $mail->Password = $config->email->password;
} else {
    $mail->SMTPAuth = false;
}

//set from
$mail->setFrom($config->email->fromEmail, $config->email->fromName);

//set to
//TODO: get Email from User!!
try {
    $mail->addAddress('', '');
} catch (\PHPMailer\PHPMailer\Exception $e) {
    $db->close();
    exit(Util::getErrorJSON("Invalid Email / Something went wrong during adding the email!"));
}

//add subject:
$mail->Subject = $config->email->subject;

//add message:
$mail->msgHTML(file_get_contents($config->email->HTMLMessageFile));
$mail->AltBody = file_get_contents($config->email->altMessageFile);

//send email:
if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message sent!';
}

$db->close();
$pstmt->close();

?>