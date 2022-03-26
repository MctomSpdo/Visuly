<?php

echo "<h1>CONFIG</h1>";

$configPath = '../files/config.json';
$config = json_decode(file_get_contents($configPath));
var_dump($config);

echo "<hr>";

//database:

echo "<h1>Database</h1>";

$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    echo "Database connection failed!";
} else {
    var_dump($db);
}


?>