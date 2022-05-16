<?php

require_once '../../assets/token.php';

function getError(string $errortext): string
{
    $err = new stdClass();
    $err->error = $errortext;
    return json_encode($err);
}

$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

//check login:
if (!isset($_COOKIE[$config->token->name])) {
    header("Location: ./login.php");
    exit();
}

//db connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if ($db->connect_error) {
    exit(getError("Internal Server Error (E004)"));
}

$token = $_COOKIE[$config->token->name];
//check token in db:
$userId = checkTokenWRedirect($token, $config, $db);

$pstmt = $db->prepare("update user set deleted = 1 where UserID like ?");

if($pstmt == false) {
    $db->close();
    exit(getError("Internal Server Error (E002)"));
}

if(!($pstmt->bind_param("i", $userId) && $pstmt->execute())) {
    $db->close();
    exit(getError("Internal Server Error (E002)"));
}

$resp = new stdClass();
$resp->success = true;
echo json_encode($resp);

$pstmt->close();
$db->close();

?>