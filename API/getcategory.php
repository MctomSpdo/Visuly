<?php
$configPath = '../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../assets/token.php';
require_once '../assets/user.php';
require_once '../assets/post.php';
require_once '../assets/util.php';

//check values:
if (!isset($_GET['search'])) {
    exit(Util::invalidRequestError());
}

//check user token:
if (!isset($_COOKIE[$config->token->name])) {
    exit(Util::getLoginError());
}

//database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if ($db->connect_error) {
    exit(Util::getDBErrorJSON());
}

$token = $_COOKIE[$config->token->name];
//check token in db:
$userId = checkTokenWRedirect($token, $config, $db);

$user = new User();
$user->DBLoadFromUserID($userId, $db);

//prepare searches:
$needle = $_GET['search'];

$categorySearchPstmt = $db->prepare("select name, description,
       ((select round((length(lower(name)) - length(replace(lower(name), lower(?), ''))) /
                     length(?))) * 2 +

       (select round((length(lower(description)) - length(replace(lower(description), lower(?), ''))) /
                     length(?)))
           + IF(lower(?) = lower(name), 1000, 0)) as relevance
from category
where name like concat('%', ?, '%')
   or description like concat('%', ?, '%')
order by relevance desc
limit ?");
$categorySearchPstmt->bind_param("sssssssi", $needle, $needle, $needle, $needle, $needle, $needle, $needle,$config->respLength);

if (!$categorySearchPstmt->execute()) {
    $categorySearchPstmt->close();
    $db->close();
    exit(Util::getDBRequestError());
}
$response = Util::resToJson($categorySearchPstmt->get_result());

echo json_encode($response);


$categorySearchPstmt->close();
$db->close();
