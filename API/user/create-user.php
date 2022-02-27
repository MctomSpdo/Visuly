<?php
$configPath = '../../files/config.json';
$emailReg = '/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';

require_once('../../assets/token.php');
require_once('../../assets/user.php');

function hasLowerCase($str) {
    return strtoupper($str) != $str;
}

function hasUpperCase($str) {
    return strtolower($str) != $str;
}


$config = json_decode(file_get_contents($configPath));

//https://stackoverflow.com/questions/8945879/how-to-get-body-of-a-post-in-php
$data = json_decode(file_get_contents('php://input'));

if((isset($data->username) && isset($data->email) && isset($data->gender) && isset($dat->password))) {
    $resp = new stdClass();
    $resp->error = "values inconsistent";
    $resp->result = "invalid Request";
    echo json_encode($resp);
    exit;
}

$username = $data->username;
$email = $data->email;
$gender = $data->gender;
$password = $data->password;

//checkValues:

if(strlen($username) < 4 || strlen($username) > 30) {
    $resp = new stdClass();
    $resp->error = "username";
    $resp->result = "invalid Request";
    echo json_encode($resp);
    exit;
}

if(preg_match($emailReg, $email) == 0) {
    $resp = new stdClass();
    $resp->error = "email";
    $resp->result = "invalid Request";
    echo json_encode($resp);
    exit;
}

if(!(strcmp($gender, 'male') == 0 || strcmp($gender, 'female') == 0 || strcmp($gender, 'divers') == 0)) {
    $resp = new stdClass();
    $resp->error = "gender";
    $resp->result = "invalid Request";
    echo json_encode($resp);
    exit;
}

if(strlen($password) < 8 && hasLowerCase($password) && hasUpperCase($password) && preg_match('~[0-9]~', $password) == 0) {
    $resp = new stdClass();
    $resp->error = "password";
    $resp->result = "invalid Request";
    echo json_encode($resp);
    exit;
}

//Db connection:

$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error (E004)";
    $resp->result = "ERROR";
    echo json_encode($resp);
    exit;
}

//create user:
$user = new User();
$userDB = $user->createUser($username, $email, $password, $gender, $config, $db);
$resp = new stdClass();

if($userDB) {
    $token = newTokenEmail($db, $config, $email);

    if($token == false) {
        $resp->error = "Internal Server error (E005)";
    } else {
        $resp->result = "success";
        $resp->created = true;
    }
} else {
    $resExists = $user->DBExistsFromUsernameOrEmail($email, $username, $db);

    if($resExists === -1) {
        $resp->error = "Internal Server error (E002)";
        $resp->result = "ERROR";
    }

    if($resExists) {
        $resp->error = "User already exists";
        $resp->result = "exists";
    } else {
        $resp->error = "Internal Server error (E002)";
        $resp->result = "ERROR";
    }

}
echo json_encode($resp);
$db->close();