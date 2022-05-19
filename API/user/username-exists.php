<?php
$configPath = '../../files/config.json';

require_once '../../assets/user.php';
require_once '../../assets/util.php';

$config = json_decode(file_get_contents($configPath));

$data = json_decode(file_get_contents('php://input'));
$username = $data->username;

//Respond object
$resp = new stdClass();
$resp->username = $username;

//Database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    exit(Util::getDBErrorJSON());
}

$user = new User();
$res = $user->DBExistsFromUsername($username, $db);

if($res === -1) {
    $resp->error = "Internal Server error(E002)";
} else {
    $resp->exists = $res;
}

echo json_encode($resp);
$db->close();