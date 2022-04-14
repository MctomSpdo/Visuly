<?php
//check if config exists:
$configPath = '../../files/config.json';
$warnings = array();

function error(string $message) {
    $resp = new stdClass();
    $resp->status = "error";
    $resp->message = $message;
    exit(json_encode($resp));
}

function warning(string $message, array &$arr) {
    $arr[] = $message;
}

function isJSON(string $string):bool {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

if(!file_exists($configPath)) {
    error("Config file does not exist");
}

//check if json is parsable:
$configContents = file_get_contents($configPath);
if(!isJSON($configContents)) {
    error("Config file could not be parsed!");
}

$config = json_decode($configContents);
//check database config:
if(!isset($config->database)) {
    error("Config invalid: section database does not exist");
} else {
    if(!isset($config->database->host) || $config->database->host == "") {
        error("Config invalid: value database.host does not exist or is empty");
    }
    if(!isset($config->database->database) || $config->database->database = "") {
        error("Config invalid: value database.database does not exist or is empty" );
    }
    if(!isset($config->database->username) || $config->database->username == "") {
        error("Config invalid: value database.username does not exist or is empty");
    }
    if(!isset($config->database->password) || $config->database->password == "") {
        error("Config invalid: value database.password does not exist or is empty");
    }
}

//check token:
if(!isset($config->token)) {
    error("Config invalid: section token deos not exist");
} else {
    if(!isset($config->token->name) || $config->token->name == "") {
        error("Config invalid: section token.name does not exist or is empty");
    }
    if(!isset($config->token->salt)) {
        error("Config invalid: section token.salt does not exist");
    }
    if($config->token->salt == "") {
        warning("Field token.salt is empty, this is a security risk!", $warnings);
    }
    if(!isset($config->token->length) || !is_numeric($config->token->length)) {
        error("Config invalid: section token.length does not exist or is not numeric");
    }
    if(!isset($config->token->lifespan) || !is_numeric($config->token->lifespan)) {
        error("Config invalid: section token.lifepsan does not exist or is not numeric");
    }
}

//check post:
if(!isset($config->post)) {
    error("Config invalid: section post deos not exist");
} else {
    if(!isset($config->post->defaultDir) || $config->post->defaultDir == "") {
        error("Config invalid: section post.defaultDir does not exist or is empty");
    }
    if(!isset($config->post->nameLength) || !is_numeric($config->post->nameLength)) {
        error("Config invalid: section post.nameLength does not exist or is not numeric");
    }
    if(!isset($config->post->maxSize) || !is_numeric($config->post->maxSize)) {
        error("Config invalid: section post.maxSize does not exist or is not numeric");
    }
    if(!isset($config->post->imgType) || $config->post->imgType == "") {
        error("Config invalid: section post.imgType does not exist or is empty");
    }
    if(!isset($config->post->imgHeight) || !is_numeric($config->post->imgHeight)) {
        error("Config invalid: section post.imgHeight does not exist or is not numeric");
    } else if($config->post->imgHeight < 1 || $config->post->imgHeight > 10000) {
        error("Config invalid: section post.imgHeight has to be in range 1 - 10000");
    }
    if(!isset($config->post->imgQuality) || !is_numeric($config->post->imgQuality)) {
        error("Config invalid: section post.imgQuality does not exist or is not numeric");
    } else if ($config->post->imgQuality < -1 || $config->post->imgQuality > 100) {
        error("Config invalid: section post.imgQuality has to be in range -1 to 100");
    }
}

//check other values:
if(!isset($config->userImageFolder) || $config->userImageFolder == "") {
    error("Config invalid: section userImageFolder does not exist or is empty");
}
if(!isset($config->userDefaultImage) || $config->userDefaultImage == "") {
    error("Config invalid: section userDefaultImage does not exist or is empty");
}
if(!isset($config->userDefaultPermission) || $config->userDefaultPermission == "") {
    error("Config invalid: section userDefaultPermission does not exist or is empty");
}
if(!isset($config->passwordSalt)) {
    error("Config invalid: section passwordSalt does not exist");
}
if($config->passwordSalt == "") {
    warning("Field passwordSalt is empty, this is a security risk!", $warnings);
}
if(!isset($config->respLength) || !is_numeric($config->respLength)) {
    error("Config invalid: section respLength does not exist or is not numeric");
}

//prepare response:
$resp = new stdClass();
$resp->status = "passed";
$resp->warning = false;

//check warnings:
if(sizeof($warnings) > 0) {
    $resp->warning = true;
    $resp->status = "passed";
    $resp->warnings = $warnings;
}

//echo json
echo json_encode($resp);