<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="keywords" content="visuly, social Media, login">
    <meta name="description" content="Login - Visuly">
    <meta name="author" content="MctomSpdo">
    <title>Login - Visuly</title>

    <script src="./files/js/login.js" defer></script>

    <link rel="stylesheet" href="./files/css/main.css">
    <link rel="stylesheet" href="./files/css/login.css">
</head>
<body>
    <div id="login-wrapper">
        <div id="login-logo">
            <h1>VISULY</h1>
        </div>
        <div id="login-box">
            <h2>Login</h2>
            <form method="post" id="login-form">
                <input type="text" name="username" id="login-username" placeholder="Username or Email">
                <br>
                <input type="password" name="password" id="login-password" placeholder="Password">
                <p id="sign-in-error"></p>
                <button type="submit">Log in</button>
                
            </form>
            <a href="signup.php">Create Account</a>
            <a href="reset-password.php">Reset password</a>
        </div>
    </div>

    <div id="login-backdrop">
        <ul class="box-area">
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
		</ul>
    </div>
</body>
</html>