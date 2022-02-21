<?php
$configPath = '../../files/config.json';


$config = json_decode(file_get_contents($configPath));

$data = json_decode(file_get_contents('php://input'));
$username = $data->username;

//respond object:
$resp = new stdClass();
$resp->username = $username;
$resp->error = "";

//Database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    $resp->error = "Internal Server error(E004)";
    echo json_encode($resp);
    exit;
}

//Database sql:
$dbusername = $db->real_escape_string($username);

$sql = "select username from user where username like '$dbusername' limit 1;";

if($res = $db->query($sql)) {
    if($res->num_rows > 0) {
        $resp->exists = true;
    } else {
        $resp->exists = false;
    }
} else {
    $resp->error = "Internal Server error(E002)";
}

echo json_encode($resp);
$res->close();
$db->close();