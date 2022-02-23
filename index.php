<?php
$configPath = 'files/config.json';
$config = json_decode(file_get_contents($configPath));

//checks if user is logged in:
if (!isset($_COOKIE[$config->loginTokenName])) {
    header("Location: ./login.php");
    exit();
}
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