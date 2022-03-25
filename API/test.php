<?php

echo "<h1>CONFIG</h1>";

$configPath = '../files/config.json';
$config = json_decode(file_get_contents($configPath));
var_dump($config);

echo "<hr>";

echo "<h1>Config</h1>";



?>