<?php
$configPath = '../../files/config.json';

require_once('../../assets/token.php');
require_once('../../assets/util.php');

//read config file:
$config = json_decode(file_get_contents($configPath));

//get request header data
$data = json_decode(file_get_contents('php://input'));

$username = $data->username;
$password = $data->password;

//database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

$dbusername = $db->real_escape_string($username);
$dbpassword = $db->real_escape_string($config->passwordSalt . $password);

$sql = "select UserID, username
from user
where (username like '$dbusername'
    or email like '$dbusername')
  and password like md5('$dbpassword') and deleted = 0";

$response = new stdClass();

if($res = $db->query($sql)) {
    if($res->num_rows > 1) {
        exit(Util::getDBRequestError());
    } else if ($res->num_rows == 0) {
        $response = new stdClass();
        $response->userExists = false;
        $db->close();
        exit(json_encode($response));
    } else {
        //log user in:
        $dbUserId = $res->fetch_all()[0][0];

        //generate new Token for user:
        $token = newToken($db, $config, $dbUserId);
        if($token == false) {
            $response->successfull = false;
            $response->error = "Internal Server error (E002)";
            $res->close();
            $db->close();
            exit(json_encode($response));
        }
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
