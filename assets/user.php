<?php

class User
{
    public $UserID;
    public $UUID;
    public $username;
    public $desc;
    public $gender;
    public $profilePic;
    public $createdOn;
    public $phoneNumber;
    public $email;
    private $password;
    public $deleted;
    public $lastLogin;
    public $lastTriedLogin;
    public $permission;
    public $isBlocked;
    public $isAdmin;
    public $canPost;
    public $canLike;
    public $canComment;

    function __construct()
    {
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
            $sql = "select uuid from user where uuid like '$dbUUID';";

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
        (UUID, username, gender, profilePic, createdOn, email, password, deleted, PermissionID)
        values ('$dbUUID', '$dbUsername', '$dbGender', '$profilePic', now(), '$dbEmail', md5('$dbPassword'), 0, $defaultPermission);";

        if ($db->query($sql)) {
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
    function DBExistsFromUsernameOrEmail(string $email, string $username, mysqli $db)
    {
        $dbUsername = $db->real_escape_string($username);
        $dbEmail = $db->real_escape_string($email);

        $sql = "select username from user where username like '$dbUsername' or email like '$dbEmail' limit 1;";

        if (!$res = $db->query($sql)) {
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
    function DBExistsFromUsername(string $username, mysqli $db)
    {
        $dbUsername = $db->real_escape_string($username);
        $sql = "select username from user where username like '$dbUsername' limit 1;";

        if (!$res = $db->query($sql)) {
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
    function DBExistsFromEmail(string $email, mysqli $db)
    {
        $dbEmail = $db->real_escape_string($email);
        $sql = "select username from user where email like '$dbEmail' limit 1";

        if (!$res = $db->query($sql)) {
            return -1;
        }

        $ret = $res->num_rows > 0;
        $res->close();
        return $ret;
    }

    //load user from DB:

    /**
     * Loads the user from the DB based on the given UserID
     * @param int $userID userid of the user
     * @param mysqli $db database
     * @return bool true if exists, false otherwise (if fails false too)
     */
    function DBLoadFromUserID(int $userID, mysqli $db)
    {
        $dbUserId = $db->real_escape_string($userID);
        $sql = "select * from user inner join permission using (PermissionID) where UserID like $dbUserId";

        if (!$res = $db->query($sql)) {
            return false;
        }

        $result = $res->fetch_all()[0];

        $this->loadFromResult($result);

        $res->close();
        return true;
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
        $sql = "select * from user inner join permission using (PermissionID) where UUID like '$dbUUID' limit 1";

        if (!$res = $db->query($sql)) {
            return false;
        }
        $result = $res->fetch_all();

        if(sizeof($result) < 1) {
            return false;
        }

        $this->loadFromResult($result[0]);
        $res->close();
        return true;
    }

    function DBJoinDateFormat(string $format, mysqli $db)
    {
        $dbFormat = $db->real_escape_string($format);
        $sql = "select date_format(createdOn, '$dbFormat') from user where UserID like $this->UserID;";

        if (!$res = $db->query($sql)) {
            return false;
        }

        $result = $res->fetch_all()[0];
        $res->close();
        return $result[0];
    }

    /**
     * Looks up how many followers the user has
     * @param mysqli $db database
     * @return int -1 is error, number otherwise
     */
    function DBGetFollowers(mysqli $db)
    {
        $dbUserId = $db->real_escape_string($this->UserID);
        $sql = "select count(*) from follow where Follows like $dbUserId";

        if (!$res = $db->query($sql)) {
            return -1;
        }

        $request = $res->fetch_all()[0][0];
        $res->close();
        return (int)$request;
    }

    /**
     * Look up how many users the user follows
     * @param mysqli $db database
     * @return int -1 if error, number otherwise
     */
    function DBGetFollows(mysqli $db)
    {
        $dbUserId = $db->real_escape_string($this->UserID);
        $sql = "select count(*) from follow where UserID like $dbUserId;";

        if (!$res = $db->query($sql)) {
            return -1;
        }

        $result = $res->fetch_all()[0][0];
        $res->close();
        return (int)$result;
    }

    /**
     * Get the amount of posts the user posted to the db
     * @param mysqli $db database
     * @return int -1 if error, number otherwise
     */
    function DBGetPosts(mysqli $db)
    {
        $dbUserId = $db->real_escape_string($this->UserID);
        $sql = "select count(*) from post where UserID like $dbUserId and isDeleted = 0";

        if(!$res = $db->query($sql)) {
            return -1;
        }
        $result = $res->fetch_all()[0][0];
        $res->close();
        return (int)$result;
    }

    function DBGetPostIds(mysqli $db) {
        $dbUserId = $db->real_escape_string($this->UserID);
        $sql = "select uuid from post where UserID = '$dbUserId' and isDeleted = 0 order by postedOn desc";

        if(!$res = $db->query($sql)) {
            return -1;
        }
        $result = $res->fetch_all();
        $res->close();
        return $result;
    }

    function DBFollowsUser(mysqli $db, string $uuid):bool {
        $pstmt = $db->prepare("select count(*) from follow where UserID = ? and Follows = (select UserID from user where uuid = ?);");
        $pstmt->bind_param("is",  $this->UserID, $uuid);
        if(!$pstmt->execute()) {
            return false;
        };

        $res = $pstmt->get_result();
        $result = $res->fetch_all();
        $pstmt->close();
        return $result[0][0] == 1;
    }

    /**
     * @param mysqli $db database
     * @param string $uuid uuid of the user
     * @return array|false false if error, else parsed result
     */
    static function DBGetFollowerList(mysqli $db, string $uuid) {
        $pstmt = $db->prepare("select u.username, u.profilePic, u.uuid from follow
            inner join user u using(UserID)
            where follows = (select UserID from user where uuid = ? and deleted = 0);");
        $pstmt->bind_param("s", $uuid);
        if(!$pstmt->execute()) {
            return false;
        };

        $res = $pstmt->get_result();
        if(!$res) {
            return false;
        }
        $result = self::resToArr($res);
        $res->close();
        $pstmt->close();
        return $result;
    }

    private function loadFromResult($result)
    {
        $this->UserID = $result[1];
        $this->UUID = $result[2];
        $this->username = $result[3];
        $this->desc = $result[4];
        $this->gender = $result[5];
        $this->profilePic = $result[6];
        $this->createdOn = $result[7];
        $this->phoneNumber = $result[8];
        $this->email = $result[9];
        $this->password = $result[10];
        $this->deleted = $result[11];
        $this->lastLogin = $result[12];
        $this->lastTriedLogin = $result[13];
        $this->permission = $result[14];
        $this->isBlocked = $result[15];
        $this->isAdmin = $result[16];
        $this->canPost = $result[17];
        $this->canLike = $result[18];
        $this->canComment = $result[19];
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

    /**
     * Converts a result to an Array, where each field has the name of the column as array index
     * https://stackoverflow.com/questions/38437306/return-json-from-mysql-with-column-name
     * @param mysqli_result $result
     * @return array
     */
    private static function resToArr(mysqli_result $result): array
    {
        $jsonData = array();
        if (mysqli_num_rows($result) > 0) {
            while ($array = mysqli_fetch_assoc($result)) {
                $jsonData[] = $array;
            }
        }
        return $jsonData;
    }
}