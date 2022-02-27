<?php
$configPath = '../../files/config.json';

include_once '../../assets/user.php';

$config = json_decode(file_get_contents($configPath));

$data = json_decode(file_get_contents('php://input'));
$email = $data->email;

//respond object:
$resp = new stdClass();
$resp->email = $email;
$resp->error = "";

//Database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    $resp->error = "Internal Server error(E004)";
    echo json_encode($resp);
    exit;
}

$user = new User();
$res = $user->DBExistsFromEmail($email, $db);

if($res === -1) {
    $resp->error = "Internal Server error(E002)";
} else {
    $resp->exists = $res;
}

echo json_encode($resp);
$db->close();