<?php

$_db_host = "localhost";
$_db_datenbank = "ssprojekt22";
$_db_username = "server";
$_db_passwort = "abcdefgh";

//https://stackoverflow.com/questions/8945879/how-to-get-body-of-a-post-in-php
$data = json_decode(file_get_contents('php://input'));

$username = $data->username;
$email = $data->email;
$gender = $data->gender;
$password = $data->password;

echo "Username: " . $username;
echo "email: " . $email;
echo "gender: " . $gender;
echo "password: " . $password;

