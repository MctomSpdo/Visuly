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

/**
 * @param $new_width int new width for image
 * @param $new_height int new height for image
 * @param $image image
 * @param $width int old with
 * @param $height int new height
 * @return false|GdImage|resource
 */
function resize_image(int $new_width, int $new_height, $image, int $width, int $height)
{
    $new_image = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    return $new_image;
}

/**
 * @param $filename string filename to load the image from
 * @param $type string imagetype
 * @return false|GdImage|resource
 */
function load_image(string $filename, string $type)
{
    $image = null;
    if ($type == IMAGETYPE_JPEG) {
        $image = imagecreatefromjpeg($filename);
    } elseif ($type == IMAGETYPE_PNG) {
        $image = imagecreatefrompng($filename);
    } elseif ($type == IMAGETYPE_GIF) {
        $image = imagecreatefromgif($filename);
    }
    return $image;
}

function resize_image_to_height($new_height, $image, $width, $height) {
    $ratio = $new_height / $height;
    $new_width = $width * $ratio;
    return resize_image($new_width, $new_height, $image, $width, $height);
}

function save_image($new_image, $new_filename, $new_type='jpeg', $quality=80) {
    if($new_type == 'jpeg') {
        imagejpeg($new_image, $new_filename, $quality);
    }
    elseif( $new_type == 'png' ) {
        imagepng($new_image, $new_filename);
    }
    elseif( $new_type == 'gif' ) {
        imagegif($new_image, $new_filename);
    }
    elseif($new_type == "jpg") {
        imagejpeg($new_image, $new_filename, $quality);
    }
}

//validate all inputs:
if (!(isset($_POST['title']) && isset($_POST['desc']) && isset($_FILES['image']))) {
    $resp = new stdClass();
    $resp->error = "Invalid Request";
    exit(json_encode($resp));
}

//file size upload Limit:
if ($_FILES['image']['size'] > $config->post->maxSize) {
    $resp = new stdClass();
    $resp->error = "File is too big";
    exit(json_encode($resp));
}

//check if file is image:
$check = getimagesize($_FILES['image']['tmp_name']);
if ($check == false) {
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

if ($db->connect_error) {
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

$target_file = null;

do {
    $target_fileName = generateRandomString($config->post->nameLength);
    $target_file = $target_dir . $target_fileName . "." . $config->post->imgType;
} while (file_exists($target_file));

//size and compress image:
$imageLocation = $_FILES['image']['tmp_name'];
list($width, $height, $type) = getimagesize($imageLocation);
$old_image = load_image($imageLocation, $type);

if($old_image === null) {
    $resp = new stdClass();
    $resp->error = "Invalid File type";
    $db->close();
    exit(json_encode($resp));
}
if($old_image === false) {
    $resp = new stdClass();
    $resp->error = "Image codec not supported or invalid!";
    $db->close();
    exit(json_encode($resp));
}

$newImage = resize_image_to_height($config->post->imgHeight, $old_image, $width, $height);

//save image file:
var_dump($target_file);
save_image($newImage, $target_file, $config->post->imgType, $config->post->imgQuality);

//save in system (db):
$post = new Post();
if (!$post->createPost($target_fileName, $_POST['title'], $_POST['desc'], $user->UserID, $config->post->imgType, $db)) {
    $resp = new stdClass();
    $resp->error = "Internal Server Error (E002)";
    $db->close();
    exit(json_encode($resp));
}

$post->ImgPath = $target_fileName;
$post->DBLoadFromPath($db);
$postId = $post->PostId;

$categories = explode(",", $_POST['category']);

foreach ($categories as $cat) {
    $cat = trim($cat);
    if($cat == "" || $cat == " ") {
        continue;
    }
    $sql = $db->prepare("insert ignore into category (name) values (?);");
    $sql->bind_param("s", $cat);

    if (!$sql->execute()) {
        $resp = new stdClass();
        $resp->error = "Internal Server Error (E002)";
        $db->close();
        $sql->close();
        exit(json_encode($resp));
    }
    $sql->close();

    $dbCat = $db->real_escape_string($cat);
    $sql = "select CategoryID from category where name like '$dbCat';";
    if (!$res = $db->query($sql)) {
        $resp = new stdClass();
        $resp->error = "Internal Server Error (E002)";
        $db->close();
        exit(json_encode($resp));
    }
    $result = $res->fetch_all();
    $res->close();

    $catId = $result[0][0];

    $sql = $db->prepare("insert into post_category (PostID, CategoryID) values (?, ?)");
    $sql->bind_param("ss", $post->PostId, $catId);

    if (!$sql->execute()) {
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