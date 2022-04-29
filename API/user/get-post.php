<?php
$configPath = '../../files/config.json';

require_once '../../assets/token.php';
require_once '../../assets/user.php';
require_once '../../assets/post.php';

$config = json_decode(file_get_contents($configPath));

if(!isset($_GET['user'])) {
    $resp = new stdClass();
    $resp->error = "Invalid Request";
    exit(json_encode($resp));
}

$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error (E004)";
    exit(json_encode($resp));
}

$token = $_COOKIE[$config->token->name];
$userId = checkTokenWRedirect($token, $config, $db);
$user = new User();
$user->DBLoadFromUserID($userId, $db);

$offset = 0;

//get posts:
$pstmt = $db->prepare("select p.uuid, p.title, p.description, p.postedOn, concat(p.uuid, '.', p.extention) as path, u.username, u.uuid, u.profilePic,
       (select count(*) from postliked pl where p.PostID = pl.PostID) as likes,
       (select replace(replace(count(*), 0, 'false'), 1, 'true')
        from postliked pl2
        where p.PostID = pl2.PostID
          and ? = pl2.UserID)                                  as hasliked,
       (select count(*) from comment ct where p.PostID = ct.PostID)   as comments
from post p
         inner join user u using (UserID)
where p.isDeleted = 0
  and p.UserID = (select UserID from user where uuid = ? and deleted = 0)
order by postedOn
        desc
limit ? offset ?");
$pstmt->bind_param("isii", $user->UserID, $_GET['user'], $config->respLength, $offset);
$pstmt->execute();
$dbReq = $pstmt->get_result();
$posts = $dbReq->fetch_all();
$pstmt->close();

$resp = new stdClass();
$resp->posts = array();

foreach ($posts as $post) {
    $postRes = new stdClass();
    $postRes->postId = $post[0];
    $postRes->title = $post[1];
    $postRes->description = $post[2];
    $postRes->postedOn = $post[3];
    $postRes->path = $post[4];
    $postRes->likes = $post[8];
    $postRes->postedFrom = $post[5];
    $postRes->postedFromID = $post[6];
    $postRes->postedFromImage = $post[7];
    $postRes->hasLiked = filter_var($post[9], FILTER_VALIDATE_BOOLEAN);
    $postRes->comments = $post[10];
    array_push($resp->posts, $postRes);
}

echo json_encode($resp);

$db->close();
?>