<?php
$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../../assets/user.php';
require_once '../../assets/token.php';
require_once '../../assets/post.php';
require_once '../../assets/util.php';

$sql = "select p.title as title,
       p.description as description,
       p.uuid as postId,
       concat(p.uuid, '.', p.extention) as path,
       count(pl.UserID) as likes,
       u.username as postedFrom,
       u.profilePic as postedFromImage,
       u.uuid as postedFromID,
       (if((select count(*) from postliked pl2 where pl2.UserID = ? and pl2.PostID = p.PostID) = 1, 'true', 'false')) as hasLiked,
       (select count(*) from comment c where c.PostID = p.PostID) as comments
from post p
         inner join postliked pl on p.PostID = pl.PostID
         inner join user u on p.UserID = u.UserID
where p.isDeleted = 0 and u.deleted = 0
group by p.uuid, p.title, p.description, p.extention, p.UserID, u.username, u.uuid, u.profilePic
order by likes desc
limit ? offset ?";

//check request:

$offset = 0;
if(isset($_GET['offset'])) {
    $offset = $_GET['offset'];
}
$limit = $config->respLength;

if(!is_numeric($offset)) {
    $resp = new stdClass();
    $resp->error = "Offset has to be numeric";
    exit(json_encode($resp));
}

//check login:
if (!isset($_COOKIE[$config->token->name])) {
    $resp = new stdClass();
    $resp->error = "No Permission";
    exit(json_encode($resp));
}
$token = $_COOKIE[$config->token->name];

//db connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error (004)";
    exit(json_encode($resp));
}

$userId = checkTokenWRedirect($token, $config, $db);

$pstmt = $db->prepare($sql);

if($pstmt === false) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error (E002)";
    $db->close();
    exit(json_encode($resp));
}

$pstmt->bind_param("iii", $userId, $limit, $offset);

if(!$pstmt->execute()) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error (E002)";
    $db->close();
    exit(json_encode($resp));
}

$res = $pstmt->get_result();
echo json_encode(Util::resToJson($res));

$res->close();
$pstmt->close();
$db->close();
?>