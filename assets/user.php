<?php

class User
{
    private $UserID;
    public $UUID;
    public $username;
    public $desc;
    public $gender;
    public $profilePic;
    public $createdOn;
    public $phoneNumber;
    public $email;
    public $password;
    public $deleted;
    public $lastLogin;
    public $lastTriedLogin;
    public $permission;

    function __construct()
    {
    }

    /**
     * Loads a user with the help of the UUID
     * @param mysqli $db database to pull the data from
     * @return bool false if error, true otherwise
     */
    function DBLoadFromUUID(mysqli $db): bool
    {
        if ($this->UUID == null) {
            return false;
        }

        $dbUUID = $db->real_escape_string($this->UUID);
        $sql = "select * from user where UUID like '$dbUUID' limit 1";

        if (!$res = $db->query($sql)) {
            return false;
        }
        $result = $res->fetch_all()[0];

        $this->UserID = $result[0];
        $this->UUID = $result[1];
        $this->username = $result[2];
        $this->desc = $result[3];
        $this->gender = $result[4];
        $this->profilePic = $result[5];
        $this->createdOn = $result[6];
        $this->phoneNumber = $result[7];
        $this->email = $result[8];
        $this->password = $result[9];
        $this->deleted = $result[10];
        $this->lastLogin = $result[11];
        $this->lastTriedLogin = $result[12];
        $this->permission = $result[13];
        return true;
    }

    /**
     * @param mysqli $db Database for the user
     * @return false|string false if error, UUID otherwise
     */
    function generateUUID(mysqli $db)
    {
        while (true) {
            $UUID = "U" . $this->generateString(14) . "-" . $this->generateString(14);
            $dbUUID = $db->real_escape_string($UUID);
            $sql = "select UUID from user where UUID like '$dbUUID';";

            if (!$res = $db->query($sql)) {
                return false;
            }

            if ($res->num_rows > 0) {
                $res->close();
                continue;
            }
            $res->close();
            $this->UUID = $UUID;
            return $UUID;
        }
    }

    /**
     * Creates the user with the minimal requirements
     * @param string $username username
     * @param string $email email
     * @param string $password password
     * @param string $gender gender
     * @param $config * config as JSON
     * @param mysqli $db Database connection
     * @return bool true if worked, false otherwise
     */
    function createUser(string $username, string $email, string $password, string $gender, $config, mysqli $db): bool
    {
        $dbUsername = $db->real_escape_string($username);
        $dbEmail = $db->real_escape_string($email);
        $dbPassword = $db->real_escape_string($config->passwordSalt . $password);

        $profilePic = $db->real_escape_string($config->userDefaultImage);
        $defaultPermission = $config->userDefaultPermission;
        $dbUUID = $db->real_escape_string($this->generateUUID($db));

        if (strcmp($gender, 'male') == 0) {
            $dbGender = $db->real_escape_string('m');
        } else if (strcmp($gender, 'female') == 0) {
            $dbGender = $db->real_escape_string('f');
        } else {
            $dbGender = $db->real_escape_string('d');
        }

        $sql = "insert into USER
        (UUID, username, gender, profilePic, createdOn, email, password, deleted, permission)
        values ('$dbUUID', '$dbUsername', '$dbGender', '$profilePic', now(), '$dbEmail', md5('$dbPassword'), 0, $defaultPermission);";

        if($db->query($sql)) {
            return true;
        }
        return false;
    }

    /**
     * Looks up in the database, if user already has the given username or email.
     * @param string $email email
     * @param string $username username
     * @param mysqli $db Database to look up
     * @return bool|int true if exists, -1 if error
     */
    function DBExistsFromUsernameOrEmail(string $email, string $username, mysqli $db) {
        $dbUsername = $db->real_escape_string($username);
        $dbEmail = $db->real_escape_string($email);

        $sql = "select username from user where username like '$dbUsername' or email like '$dbEmail' limit 1;";

        if(!$res = $db->query($sql)) {
            return -1;
        }

        $ret = $res->num_rows > 0;
        $res->close();
        return $ret;
    }

    /**
     * Looks up in the database, if the username is already in use
     * @param string $username email
     * @param mysqli $db Database
     * @return bool|int true if exists, false otherwise, -1 if error
     */
    function DBExistsFromUsername(string $username, mysqli $db) {
        $dbUsername = $db->real_escape_string($username);
        $sql = "select username from user where username like '$dbUsername' limit 1;";

        if(!$res = $db->query($sql)) {
            return -1;
        }
        $ret = $res->num_rows > 0;
        $res->close();
        return $ret;
    }

    /**
     * Looks up in the database, if the email is already in use
     * @param string $email email
     * @param mysqli $db Database
     * @return bool|int true if exists, false otherwise, -1 if error
     */
    function DBExistsFromEmail(string $email, mysqli $db) {
        $dbEmail = $db->real_escape_string($email);
        $sql = "select username from user where email like '$dbEmail' limit 1";

        if(!$res = $db->query($sql)) {
            return -1;
        }

        $ret = $res->num_rows > 0;
        $res->close();
        return $ret;
    }

    private function generateString($length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}