<?php

class Util
{
    /********************************************************* ERROR **************************************************/

    /**
     * Gets the default error with an error message
     * @param string $errortext
     * @return stdClass errorClass
     */
    static function getError(string $errortext): stdClass
    {
        $err = new stdClass();
        $err->error = $errortext;
        return $err;
    }

    /**
     * Gets an Error encoded as a JSON
     * @param string $errortext Text for error
     * @return string JSON encoded error
     */
    static function getErrorJSON(string $errortext): string
    {
        return json_encode(Util::getError($errortext));
    }

    /**
     * Error for not being logged in
     * @return string Gives an error in case you are not logged in
     */
    static function getLoginError(): string
    {
        $error = "Not logged in";
        return Util::getErrorJSON($error);
    }

    /**
     * Gives you the error for invalid Requests
     * @return string error for invalid Request
     */
    static function invalidRequestError(): string
    {
        $error = "Not logged in";
        return Util::getErrorJSON($error);
    }

    /**
     * Gets an error for failed database connection
     * @return string error for database connection
     */
    static function getDBErrorJSON(): string
    {
        $error = "Internal Server Error (E004)";
        return Util::getErrorJSON($error);
    }

    /**
     * if the request fails, this will be the default error
     * @return string default error for request
     */
    static function getDBRequestError(): string
    {
        $error = "Internal Server Error (E002)";
        return Util::getErrorJSON($error);
    }

    /********************************************************* DB STUFF **************************************************/

    /**
     * Turns a response object from a mysqli into an array having a hashmap with the values and the name of the value.
     * https://stackoverflow.com/questions/38437306/return-json-from-mysql-with-column-name
     * @param mysqli_result $result
     * @return array
     */
    static function resToJson(mysqli_result $result): array
    {
        $jsonData = array();
        if (mysqli_num_rows($result) > 0) {
            while ($array = mysqli_fetch_assoc($result)) {
                $jsonData[] = $array;
            }
        }
        return $jsonData;
    }

    /********************************************************* STRING UTILS **************************************************/

    /**
     * Generates a random text with a given length. Values include A-Z, a-z, 0-9
     * @param int $length length for the text, default is 60
     * @return string Text with Numbers
     */
    static function generateRandomString(int $length = 60): string
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
     * Checks if a given string has lower characters in it
     * @param $str string text
     * @return bool true if lower characters, false otherwise
     */
    static function hasLowerCase(string $str): bool
    {
        return strtoupper($str) != $str;
    }

    /**
     * Checks if a given string has upper characters in it
     * @param $str string text
     * @return bool true if upper characters, false otherwise
     */
    static function hasUpperCase(string $str): bool
    {
        return strtolower($str) != $str;
    }

    /**
     * Checks if a given string is an email or not
     * @param string $email text to check
     * @return bool true if email, false otherwise
     */
    static function isEmail(string $email): bool
    {
        $emailReg = '/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
        return preg_match($emailReg, $email) == 1;
    }

    /**
     * checks if a number exists in a given text
     * @param string $text text
     * @return bool true if number is in it, false otherwise
     */
    static function hasNumeric(string $text): bool
    {
        return preg_match('~[0-9]~', $text) > 0;
    }
}

?>