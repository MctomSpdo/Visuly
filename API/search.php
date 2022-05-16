<?php

function getError(string $errortext): string
{
    $err = new stdClass();
    $err->error = $errortext;
    return json_encode($err);
}

/**
 *
 * https://stackoverflow.com/questions/38437306/return-json-from-mysql-with-column-name
 * @param mysqli_result $result
 * @return array
 */
function resToJson(mysqli_result $result): array
{
    $jsonData = array();
    if (mysqli_num_rows($result) > 0) {
        while ($array = mysqli_fetch_assoc($result)) {
            $jsonData[] = $array;
        }
    }
    return $jsonData;
}

//check values:
if (!isset($_GET['search'])) {
    $resp = new stdClass();
    $resp->error = "Invalid Request";
    exit(json_encode($resp));
}

$configPath = '../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../assets/token.php';
require_once '../assets/user.php';
require_once '../assets/post.php';

//check user token:
if (!isset($_COOKIE[$config->token->name])) {
    header("Location: ./login.php");
    exit();
}

//database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if ($db->connect_error) {
    header("Location: ./error.php");
}

$token = $_COOKIE[$config->token->name];
//check token in db:
$userId = checkTokenWRedirect($token, $config, $db);

$user = new User();
$user->DBLoadFromUserID($userId, $db);

//prepare searches:
$needle = $_GET['search'];
$response = new stdClass();
$response->searchTerm = $needle;

//search users:
$userSearchPstmt = $db->prepare("select uuid, username, description, profilePic,
       (select round((length(username) - length(replace(lower(username), lower(?), ''))) / length(?))) * 2 +
       (select round((length(description) - length(replace(lower(description), lower(?), ''))) / length(?))) +
       (if(lower(?) = username, 1000, 0)) as revelance
from user
where (lower(username) like lower(concat('%', ?, '%'))
   or lower(description) like lower(concat('%', ?, '%'))
   or uuid = ?)
and deleted = 0
order by revelance desc
limit ?");
$userSearchPstmt->bind_param("ssssssssi", $needle, $needle, $needle, $needle, $needle, $needle, $needle, $needle, $config->respLength);

if (!$userSearchPstmt->execute()) {
    $userSearchPstmt->close();
    $db->close();
    exit(getError("Internal Server Error (E002)"));
}
$response->user = resToJson($userSearchPstmt->get_result());
$userSearchPstmt->close();

//search posts:
$postSearchPstmt = $db->prepare("select p.uuid as postId,
       concat(p.uuid, '.', p.extention)                    as path,
       p.title,
       p.description,
       p.postedOn                                          as date,
       u.uuid                                              as postedFromID,
       u.username                                          as postedFrom,
       u.profilePic                                        as postedFromImage,
       ((select round((length(lower(p.title)) - length(replace(lower(p.title), lower(?), ''))) /
                      length(?))) * 2 +
        (select round((length(lower(p.description)) - length(replace(lower(p.description), lower(?), ''))) /
                      length(?))) +
        (IF(lower(?) = lower(p.uuid), 1000, 0))) as relevance,
       (select count(*) from postliked liked where liked.PostID = p.PostID) as likes,
       (select count(*) from comment com where com.PostID = p.PostID)       as comments,
       replace(replace((select count(*) from postliked liked2 where liked2.UserID = ? and liked2.PostID = p.PostID), 1,
                       'true'), 0, 'false')                                 as hasLiked
from post p
         inner join user u using (UserID)
where (lower(p.title) like lower(concat('%', ?, '%'))
   or lower(p.description) like lower(concat('%', ?, '%'))
   or lower(p.uuid) = lower(?))
    and deleted = 0 and p.isDeleted = 0
order by relevance desc
limit ?");
$postSearchPstmt->bind_param("sssssisssi", $needle, $needle, $needle, $needle, $needle, $user->UserID, $needle, $needle, $needle, $config->respLength);

if (!$postSearchPstmt->execute()) {
    $postSearchPstmt->close();
    $db->close();
    exit(getError("Internal Server Error (E002)"));
}
$response->post = resToJson($postSearchPstmt->get_result());
$postSearchPstmt->close();

//category search:
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
    exit(getError("Internal Server Error (E002)"));
}
$response->category = resToJson($categorySearchPstmt->get_result());
$categorySearchPstmt->close();

//send response:
echo json_encode($response);

//close resources:
$db->close();