<?php
$configPath = '../../files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once '../../assets/token.php';
require_once '../../assets/user.php';
require_once '../../assets/post.php';

function generateString($length = 60): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

//validate all inputs:
if(!(isset($_POST['title']) && isset($_POST['desc']) && isset($_FILES['image']))) {
    $resp = new stdClass();
    $resp->error = "Invalid Request";
    exit(json_encode($resp));
}

//file size upload Limit:
if($_FILES['image']['size'] > $config->post->maxSize) {
    $resp = new stdClass();
    $resp->error = "File is too big";
    exit(json_encode($resp));
}

//check if file is image:
$check = getimagesize($_FILES['image']['tmp_name']);
if($check == false) {
    $resp = new stdClass();
    $resp->error = "File is not an Image";
    exit(json_encode($resp));
}

//check login:
if (!isset($_COOKIE[$config->token->name])) {
    header("Location: ./login.php");
    exit();
}
//db connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error (E004)";
    exit(json_encode($resp));
}

$token = $_COOKIE[$config->token->name];
//check token in db:
$userId = checkTokenWRedirect($token, $config, $db);
$user = new User();
$user->DBLoadFromUserID($userId, $db);

$data = json_decode(file_get_contents('php://input'));

//new filename:
$extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$target_dir = "../.." . $config->post->defaultDir . "/";
$target_fileName = generateRandomString($config->post->nameLength);

$target_file = $target_dir . $target_fileName . "." . $extension;

while(file_exists($target_file)) {
    $target_fileName = generateRandomString($config->post->nameLength);
    $target_file = $target_dir . $target_file . "." . $extension;
}

//save file:
$fileData = file_get_contents($_FILES['image']['tmp_name']);
if(file_put_contents($target_file, $fileData) == false) {
    $resp = new stdClass();
    $resp->error = "Error while saving file";
    $db->close();
    exit(json_encode($resp));
};



$post = new Post();
if(!$post->createPost($target_fileName, $_POST['title'], $_POST['desc'], $user->UserID, $extension, $db)) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error (E002)";
    $db->close();
    exit(json_encode($resp));
}

$post->ImgPath = $target_fileName;
$post->DBLoadFromPath($db);
$postId = $post->PostId;

$categories = explode(",", $_POST['category']);

foreach($categories as $cat) {
    $cat = trim($cat);
    $sql = $db->prepare("insert ignore into category (name) values (?);");
    $sql->bind_param("s", $cat);

    if(!$sql->execute()) {
        $resp = new stdClass();
        $resp->error = "Internal Server Error (E002)";
        $db->close();
        $sql->close();
        exit(json_encode($resp));
    }
    $sql->close();

    $dbCat = $db->real_escape_string($cat);
    $sql = "select CategoryID from category where name like '$dbCat';";
    if(!$res = $db->query($sql)) {
        $resp = new stdClass();
        $resp->error = "Internal Server Error (E002)";
        $db->close();
        exit(json_encode($resp));
    }
    $result = $res->fetch_all();
    $res->close();

    $catId = $result[0][0];

    $sql = $db->prepare("insert into post_category (Post, Category) values (?, ?)");
    $sql->bind_param("ss", $post->PostId, $catId);

    if(!$sql->execute()) {
        $resp = new stdClass();
        $resp->error = "Internal Server Error (E002)";
        $db->close();
        $sql->close();
        exit(json_encode($resp));
    }
    $sql->close();
}

$resp = new stdClass();
$resp->postid = $post->ImgPath;
echo json_encode($resp);

$db->close();
?>