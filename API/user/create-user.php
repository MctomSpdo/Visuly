<?php
$configPath = '../../files/config.json';
$emailReg = '/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';

function hasLowerCase($str) {
    return strtoupper($str) != $str;
}

function hasUpperCase($str) {
    return strtolower($str) != $str;
}

function generateRandomString($length = 30): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
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

$dbusername = $db->real_escape_string($username);
$dbemail = $db->real_escape_string($email);
$dbpassword = $db->real_escape_string($config->passwordSalt . $password);
$dbgender = '';

if(strcmp($gender, 'male') == 0) {
    $dbgender = $db->real_escape_string('m');
} else if (strcmp($gender, 'female') == 0) {
    $dbgender = $db->real_escape_string('f');
} else {
    $dbgender = $db->real_escape_string('d');
}
$profilePic = $config->userDefaultImage;
$defaultPermission = $config->userDefaultPermission;

//insert to db:
$sql = "insert into USER
    (username, gender, profilePic, createdOn, email, password,deleted, permission)
    values ('$dbusername', '$dbgender', '$profilePic', now(), '$dbemail', md5('$dbpassword'), 0, $defaultPermission);";

$resp = new stdClass();

if($res = $db->query($sql)) {


    $token = generateRandomString(30);
    $dbtoken = $db->real_escape_string($config->tokenSalt . $token);


    $sqlToken = "insert into token (Token, Owner, Created, ValidUntil) 
    values (md5('$dbtoken'), 
        (select UserID from user where email like '$dbemail'), 
        now(), date_add(now(), INTERVAL 2 year));";

    if($resToken = $db->query($sqlToken)) {
        setcookie($config->loginTokenName, $token, time() + (31536000) * 1, "/", "", true);//expires in x year (x = 1)
        $resp->error = "";
        $resp->result = "success";
        $resp->created = true;
    } else {
        $resp->error = "Internal Server error (E005)";
    }
    echo json_encode($resp);
} else {
    $sqlExists = "select username from user where username like '$dbusername' or email like '$dbemail' limit 1;";

    if($resExists = $db->query($sqlExists)) {
        if($resExists->num_rows > 0) {
            $resp->error = "User already exists";
            $resp->result = "exists";
            echo json_encode($resp);
        } else {
            $resp->error = "Internal Server error (E002)";
            $resp->result = "ERROR";
            echo json_encode($resp);
            $db->close();
            exit();
        }
    } else {
        $resp->error = "Internal Server error(E002)";
        $resp->result = "ERROR";
        echo json_encode($resp);
    }
    $resExists->close();
}
$db->close();