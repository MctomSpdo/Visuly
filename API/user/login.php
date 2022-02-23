<?php
$configPath = '../../files/config.json';

//functions:
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

//read config file:
$config = json_decode(file_get_contents($configPath));

//get request header data
$data = json_decode(file_get_contents('php://input'));

$username = $data->username;
$password = $data->password;

//database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

$dbusername = $db->real_escape_string($username);
$dbpassword = $db->real_escape_string($password);

$sql = "select UserID, username
from user
where (username like '$dbusername'
    or email like '$dbusername')
  and password like md5('$dbpassword')";

$response = new stdClass();

if($res = $db->query($sql)) {
    if($res->num_rows > 1) {

        $response->error = "Internal Server error(E006)";
        exit(json_encode($response));
    } else if ($res->num_rows == 0) {
        $response = new stdClass();
        $response->userExists = false;
        $db->close();
        exit(json_encode($response));
    } else {
        //log user in:
        $result = $res->fetch_all()[0];
        $dbUserId = $result[0];

        //generate new Token for user:
        $token = generateRandomString(30);
        $dbToken = $db->real_escape_string($config->tokenSalt . $token);

        //insert token to DB:
        $sqlNewToken = "insert into token (Token, Owner, Created, ValidUntil)
        values (md5('$dbToken'), $dbUserId, now(), date_add(now(), INTERVAL 2 year));";

        if(!$tokenRes = $db->query($sqlNewToken)) {
            $response->successfull = false;
            $response->error = "Internal Server error (E002)";
            $res->close();
            $db->close();
            exit(json_encode($response));
        }

        //save token on user and respond:
        setcookie($config->loginTokenName, $token, time() + (31536000) * 1, "/", "", true);//expires in x year (x = 1)
        $response->successfull = true;
        $response->userExists = true;
        echo json_encode($response);
    }
} else {
    $response = new stdClass();
    $response->successfull = false;
    $response->error = "Internal Server error (E004)";
    $db->close();
    exit(json_encode($response));
}

$res->close();
$db->close();

