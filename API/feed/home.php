<?php
$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../../assets/token.php';
require_once '../../assets/user.php';
require_once '../../assets/post.php';
require_once '../../assets/util.php';

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

$offset = 0;
if (isset($_GET['offset'])) {
    $offset = $_GET['offset'];
}

$sql = "select p.uuid as postId,
       p.title,
       p.description,
       p.postedOn as date,
       u.uuid as postedFromID,
       u.username as postedFrom,
       u.profilePic as postedFromImage,
       concat(p.uuid, '.', p.extention) as path,
       (select count(*) from postliked pl where pl.PostID = p.PostID) as likes,
       (select count(*) from comment cm where cm.PostID = p.PostID) as comments,
       (if((select count(*) from postliked pl2 where pl2.UserID = ? and pl2.PostID = p.PostID) = 1, 'true', 'false')) as hasLiked       
from post p
         inner join user u using (UserID)
where UserID in (select Follows from follow where UserID = ?) and p.isDeleted = 0
order by postedOn desc
limit ? offset ?";

$pstmt = $db->prepare($sql);
if (!($pstmt->bind_param("iiii", $user->UserID, $user->UserID, $config->respLength, $offset) && $pstmt->execute())) {
    exit(Util::getDBRequestError());
}
$result = $pstmt->get_result();

$resp = new stdClass();
$resp->posts = Util::resToJson($result);

if (sizeof($resp->posts) < 5) {
    $randomReq = $db->prepare("select p.uuid as postId,
                   p.title,
                   p.description,
                   p.postedOn as date,
                   u.uuid as postedFromID,
                   u.username as postedFrom,
                   u.profilePic as postedFromImage,
                   concat(p.uuid, '.', p.extention) as path,
                   (select count(*) from postliked pl where pl.PostID = p.PostID) as likes,
                   (select count(*) from comment cm where cm.PostID = p.PostID) as comments,
                   (if((select count(*) from postliked pl2 where pl2.UserID = ? and pl2.PostID = p.PostID) = 1, 'true', 'false')) as hasLiked
            from post p
                     inner join user u using (UserID)
            where p.isDeleted = 0
            order by rand()
            limit ? offset ?");
    if($randomReq === false) {
        exit(Util::getDBRequestError());
    }
    if (!($randomReq->bind_param("iii", $user->UserID, $config->respLength, $offset) && $randomReq->execute())) {
        exit(Util::getDBRequestError());
    }
    $resp->posts = Util::resToJson($randomReq->get_result());
}

echo json_encode($resp);

$result->close();
$pstmt->close();
$db->close();;
?>