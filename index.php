<?php
$configPath = 'files/config.json';
$config = json_decode(file_get_contents($configPath));

require_once('./assets/token.php');

//database connection:
$db = new mysqli($config->database->host, $config->database->username, $config->database->password, $config->database->database);

if($db->connect_error) {
    header("Location: ./error.php");
}

//check if user cookies exists, if not redirect them to login
if (!isset($_COOKIE[$config->loginTokenName])) {
    header("Location: ./login.php");
    exit();
}

$token = $_COOKIE[$config->loginTokenName];
//check token in db:
$userId = checkTokenWRedirect($token, $config, $db);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    <link rel="stylesheet" href="./files/css/main.css">

    <!-- icon library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
<?php
include "assets/header.html";
?>
<main>
    <?php
    include "assets/nav.html";
    ?>
    <div></div>
    <div></div>
</main>
</body>
</html>

<?php
$db->close();