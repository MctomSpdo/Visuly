<?php
$configPath = '../../files/config.json';

require_once '../../assets/token.php';
require_once '../../assets/user.php';
require_once '../../assets/util.php';

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
    exit(Util::invalidRequestError());
}

$username = $data->username;
$email = $data->email;
$gender = $data->gender;
$password = $data->password;

//checkValues:

if(strlen($username) < 4 || strlen($username) > 30) {
    exit(Util::getErrorJSON("Invalid Username"));
}

if(!Util::isEmail($email)) {
    exit(Util::getErrorJSON("Invalid Email"));
}

if(!(strcmp($gender, 'male') == 0 || strcmp($gender, 'female') == 0 || strcmp($gender, 'divers') == 0)) {
    exit(Util::getErrorJSON("Invalid Gender"));
}

if(strlen($password) < 8 && Util::hasLowerCase($password) && Util::hasUpperCase($password) && !Util::hasNumeric($password)) {
    exit(Util::getErrorJSON("Invalid Password"));
}

//Db connection:

$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    exit(Util::getDBErrorJSON());
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