<?php

/**
 * Checks if the token is valid (exists in the database)
 *
 * THIS DOES CLOSE THE DB CONNECTION (if redirected)
 * @param String $token token to be checked
 * @param $config * parsed JSON
 * @param mysqli $db database connection (MySQL)
 * @return String usernumber
 */
function checkTokenWRedirect(String $token, $config, mysqli $db)
{
    $dbToken = $db->real_escape_string($config->token->salt . $token);
    $sql = "select * from token where Token like md5('$dbToken') and ValidUntil > current_date();";

    //if database statement fails, redirect to the error page:
    if (!$res = $db->query($sql)) {
        header("Location: ./error.html");
        $db->close();
        exit();
    }

    //if there is no valid token, redirect:
    if ($res->num_rows < 1) {
        header("Location: ./login.php");
        $res->close();
        $db->close();
        exit();
    }
    $result = $res->fetch_all()[0];

    $res->close();
    return $result[2];
}

/**
 * Checks if the token is valid
 *
 * Returns also false if the database connection failed.
 * @param String $token token to check
 * @param $config * Parsed JSON
 * @param mysqli $db Database connection
 * @return bool true if valid, false if invalid
 */
function checkToken(String $token, $config, mysqli $db)
{
    $dbToken = $db->real_escape_string($config->token->salt . $token);
    $sql = "select * from token where Token like md5('$dbToken') and ValidUntil > current_date();";

    //if database statement fails, redirect to the error page:
    if (!$res = $db->query($sql)) {
        return false;
    }

    //if there is no valid token, redirect:
    if ($res->num_rows < 1) {
        return false;
    }
    $res->close();
    return true;
}

/**
 * generates a new token
 * @param mysqli $db Database
 * @param $config * Config as JSON
 * @return false|string false if db does not work, token
 */
function generateToken(mysqli $db, $config)
{
    while(true) {
        $token = generateRandomString($config->token->length);
        $dbToken = $db->real_escape_string($config->token->salt . $token);
        $sql = "select TokenID from token where Token like md5('$dbToken');";

        if (!$res = $db->query($sql)) {
            return false;
        }
        //generate token again, if it already exists in the database:
        if ($res->num_rows > 0) {
            continue;
        }
        $res->close();
        return $token;
    }

}

function generateRandomString($length = 30): string
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
 * Saves a token to the database
 * @param String $token Token
 * @param mysqli $db Database
 * @param $config * Config as JSON
 * @return boolean true if worked
 */
function saveTokenDB(String $token, mysqli $db, int $userID, $config)
{
    $dbToken = $db->real_escape_string($config->token->salt . $token);
    $dbUserId = $db->real_escape_string($userID);
    $dbLifeSpan = $db->real_escape_string($config->token->lifespan);

    $sql = "insert into token (Token, Owner, Created, ValidUntil)
        values (md5('$dbToken'), $dbUserId, now(), date_add(now(), INTERVAL $dbLifeSpan month))";

    if(!$db->query($sql)) {
        return false;
    }
    return true;
}

/**
 * Saves a token to the database, with the user that has email like
 * @param String $token token to save
 * @param mysqli $db database
 * @param String email
 * @param $config * Config as JSON
 * @return boolean false if error, true if worked
 */
function saveTokenDBEmail(String $token, mysqli $db, String $email, $config) {
    $dbToken = $db->real_escape_string($config->token->salt . $token);
    $dbEmail = $db->real_escape_string($email);
    $dbLifeSpan = $db->real_escape_string($config->token->lifespan);

    $sql = "insert into token (Token, Owner, Created, ValidUntil) 
    values (md5('$dbToken'), 
        (select UserID from user where email like '$dbEmail'), 
        now(), date_add(now(), INTERVAL $dbLifeSpan month));";

    if(!$db->query($sql)) {
        return false;
    }
    return true;
}

/**
 * Saves a token as a cookie
 * @param String $token Token to save
 * @param $config * Config as JSON
 * @return void
 */
function saveTokenCookies(String $token, $config) {
    setcookie($config->token->name, $token, time() + (2678400) * $config->token->lifespan, "/", "", true);//expires in x year (x = 1)
}

/**
 * Deletes all Cookies related with the token
 * @param String $token
 * @param $config
 * @return void
 */
function deleteTokenCookies($config) {
    setcookie($config->token->name, null, 2, '/');
}

/**
 * Generates a new Token
 * @param mysqli $db Databse
 * @param $config * Config as JOSN
 * @param int $userID owner of token
 * @return false|string false if error, Token otherwise
 */
function newToken(mysqli $db, $config, int $userID) {
    $token = generateToken($db, $config);
    if(!saveTokenDB($token, $db, $userID, $config)) {
        return false;
    }
    saveTokenCookies($token, $config);
    return $token;
}

/**
 * Generates a new Token using the users email
 * @param mysqli $db
 * @param $config
 * @param $email
 * @return false|string false if error, Token otherwise
 */
function newTokenEmail(mysqli $db, $config, $email) {
    $token = generateToken($db, $config);
    if(!saveTokenDBEmail($token, $db, $email, $config)) {
        return false;
    }
    saveTokenCookies($token, $config);
    return $token;
}
