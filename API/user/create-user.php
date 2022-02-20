<?php
$configPath = '../../files/config.json';
$emailReg = '/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';

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
    exit("Invalid Request: values inconsistent");
}

$username = $data->username;
$email = $data->email;
$gender = $data->gender;
$password = $data->password;

//checkValues:

if(strlen($username) < 4 || strlen($username) > 30) {
    exit("Invalid Request: username");
}

if(preg_match($emailReg, $email) == 0) {
    exit("Invalid Request: email");
}

if(!(strcmp($gender, 'male') == 0 || strcmp($gender, 'female') == 0 || strcmp($gender, 'divers') == 0)) {
    exit("Invalid Request: gender");
}

if(strlen($password) < 8 && hasLowerCase($password) && hasUpperCase($password) && preg_match('~[0-9]~', $password) == 0) {
    exit("Invalid Request: password");
}

//Db connection:

$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    exit("Internal Server error(E001)");
}

$dbusername = $db->real_escape_string($username);
$dbemail = $db->real_escape_string($email);
$dbpassword = $db->real_escape_string($password);
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

if($res = $db->query($sql)) {
    echo "successful!";
} else {
    $sqlExists = "select username from user where username like '$dbusername' or email like '$dbemail' limit 1;";

    if($resExists = $db->query($sqlExists)) {
        if($resExists->num_rows > 0) {
            echo "User already exists";
        } else {
            echo "Internal Server error (E003)";
        }
    } else {
        echo "Internal Server error(E002)";
    }
    $resExists->close();
}
$db->close();