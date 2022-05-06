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

if (!isset($_FILES['profilepic'])) {
    $resp = new stdClass();
    $resp->error = "Invalid Request";
    exit(json_encode($resp));
}

//file size upload limit
if($_FILES['profilepic']['size'] > $config->user->maxImgSize) {
    $resp = new stdClass();
    $resp->error = "File is too big";
    exit(json_encode($resp));
}

//check if file is imag:
$check = getimagesize($_FILES['profilepic']['tmp_name']);
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

//new filename:
$extention = pathinfo($_FILES['profilepic']['name'], PATHINFO_EXTENSION);
$target_dir = "../.." . $config->user->imageFolder . "/";

$target_file = null;

do {
    $target_fileName = generateRandomString($config->user->imgNameLength);
    $target_file = $target_dir . $target_fileName . "." . $config->user->imgType;
} while (file_exists($target_fileName));

//size and compress image:
$imageLocation = $_FILES['profilepic']['tmp_name'];
list($width, $height, $type) = getimagesize($imageLocation);

if($width != $height) {
    $resp = new stdClass();
    $resp->error="Image has to be square";
    $db->close();
    exit(json_encode($resp));
}

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

$newImage = resize_image_to_height($config->user->imgHeight, $old_image, $width, $height);

//save image file:
save_image($newImage, $target_file, $config->user->imgType, $config->user->imgQuality);

//save in system (db)
$dbFileName = $target_fileName . "." . $config->user->imgType;
$pstmt = $db->prepare("update user set profilePic = ? where UserID = ?");
$pstmt->bind_param("si", $dbFileName, $user->UserID);
if(!$pstmt->execute()) {
    $resp = new stdClass();
    $resp->error = "Internal Server error (E002)";

    $pstmt->close();
    $db->close();
    exit(json_encode($resp));
}

$resp = new stdClass();
$resp->success = true;
echo json_encode($resp);

$pstmt->close();
$db->close();
?>

