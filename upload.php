<?php
$configPath = "files/config.json";
$config = json_decode(file_get_contents($configPath));

require_once './assets/token.php';
require_once './assets/user.php';

//check user token:
if (!isset($_COOKIE[$config->token->name])) {
    header("Location: ./login.php");
    exit();
}

//database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    header("Location: ./error.php");
    exit();
}

$token = $_COOKIE[$config->token->name];
//check token in db:
$userId = checkTokenWRedirect($token, $config, $db);

$user = new User();
$user->DBLoadFromUserID($userId, $db);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="visuly, social Media, upload">
    <meta name="description" content="Create a new post on Visuly">
    <meta name="author" content="MctomSpdo">
    <title>Upload - Visuly</title>

    <link rel="stylesheet" href="./files/css/main.css">
    <link rel="icon" type="image/x-icon" href="./files/img/icon.svg">

    <!-- icon library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="./files/js/upload.js" defer></script>
</head>

<body>
<?php include "assets/header.php"; ?>
<main>
    <?php
    $navActive = 'upload';
    include "assets/nav.php" ?>
    <div id="content">
        <div id="upload-header">
            <h2>Create a post</h2>
        </div>

        <!-- https://css-tricks.com/examples/DragAndDropFileUploading/ -->
        <div id="upload-main">
            <form id="upload">
                <div id="upload-file" ondrop="drop(event);" ondragover="dragOverHandler(event);">
                    <div id="file-upload">
                        <div id="upload-file-img-wrapper">
                            <div>
                                <img src="./files/img/upload.svg" id="upload-icon" alt="Upload Icon" width="50" height="50">
                            </div>

                        </div>

                        <input type="file" id="file"  accept="image/png, image/jpeg, image/gif">
                        <label for="file" id="file-label">
                            <strong>Choose a file</strong>
                            <span>or drag it here</span>
                        </label>
                    </div>
                    <div id="file-display">
                        <img src="" id="img-prev" alt="Uploaded Image">
                    </div>
                </div>
                <div id="upload-inputs">
                    <div>
                        <label for="upload-title">Title</label>
                        <br>
                        <input type="text" name="upload-title" placeholder="Title" id="upload-title"
                               autocomplete="off">
                    </div>

                    <div>
                        <label for="upload-description">Description</label>
                        <br>
                        <textarea id="upload-description" name="upload-description" autocomplete="off"
                                  placeholder="Description"></textarea>
                    </div>

                    <div>
                        <label for="upload-category">Category</label>
                        <br>
                        <input id="upload-category" name="upload-category" autocomplete="off" placeholder="Category"
                               multiple list="upload-category-list">

                        <datalist id="upload-category-list">
                            <option>Suggestions will appear as you type</option>
                        </datalist>
                    </div>

                    <div>
                        <label for="upload-location">Location</label>
                        <br>
                        <input id="upload-location" name="upload-location" autocomplete="off" placeholder="Location"
                               list="upload-location-list">

                        <datalist id="upload-location-list">
                            <option value="loc1">Location 1</option>
                            <option value="loc2">Location 2</option>
                            <option value="loc3">Location 3</option>
                        </datalist>
                    </div>

                    <p id="upload-error"></p>

                    <div id="upload-submit-wrapper">
                        <button type="submit" id="submit">Upload</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <div></div>
</main>
</body>

</html>

<?php
$db->close();
?>
