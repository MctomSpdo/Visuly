<?php

function error(string $message) {
    $resp = new stdClass();
    $resp->status = "error";
    $resp->message = $message;
    exit(json_encode($resp));
}

//check database:
$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    error("Could not connect to DB");
}

if(!$db->close()) {
    error("Could not close DB");
}

//check warnings:
$resp = new stdClass();
$resp->status = "passed";
$resp->warning = false;

//echo json
echo json_encode($resp);