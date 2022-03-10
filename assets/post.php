<?php

class Post
{
    public $PostId;
    public $ImgPath;
    public $Title;
    public $Desc;
    public $PostedOn;
    public $fromUser;
    public $isDeleted;
    public $extention;

    /**
     * @return string full path to image
     */
    function getImagePath(): string
    {
        return $this->ImgPath . "." . $this->extention;
    }

    /**
     * Load the amount of likes from the Database
     * @param mysqli $db database
     * @return false|string false if error, amount in String otherwise
     */
    function getLikes(mysqli $db) {
        if($this->PostId == null) {
            return false;
        }

        $dbPostId = $db->real_escape_string($this->PostId);
        $sql = "select count(*) from postliked where post = $dbPostId;";

        if(!$res = $db->query($sql)) {
            return false;
        }
        $result = $res->fetch_all()[0];
        if(sizeof($result) == 0) {
            return false;
        }
        $res->close();
        return $result[0];
    }

    /**
     * Gets the amount of comments on the post from the database
     * @param mysqli $db database
     * @return false|string false if error, String with the number otherwise
     */
    function getCommentAmount(mysqli $db) {
        if($this->PostId == null) {
            return false;
        }

        $dbPostId = $db->real_escape_string($this->PostId);
        $sql = "select count(*) from comment where Post = $dbPostId and isDeleted = 0;";

        if(!$res = $db->query($sql)) {
            return false;
        }

        $result = $res->fetch_all()[0];
        if(sizeof($result) == 0) {
            return false;
        }
        $res->close();
        return $result[0];
    }

    /**
     * @param String $uuid UUID of the post
     * @param String $title Title of the post
     * @param String $desc Desc of the post
     * @param String $fromUser Owner of the post
     * @param String $extention File extention of the post
     * @param mysqli $db database connection
     * @return bool true if successfull, false otherwise
     */
    function createPost(String $uuid, String $title, String $desc, String $fromUser, String $extention, mysqli $db) {
        $dbUuid = $db->real_escape_string($uuid);
        $dbTitle = $db->real_escape_string($title);
        $dbDesc = $db->real_escape_string($desc);
        $dbFromUser = $db->real_escape_string($fromUser);
        $dbExtention = $db->real_escape_string($extention);

        $sql = "insert into post (uuid, Title, Description, PostedOn, FromUser, IsDeleted, extention) values ('$dbUuid', '$dbTitle', '$dbDesc', now(), '$dbFromUser', 0, '$dbExtention')";

        if($db->query($sql)) {
            return true;
        }
        return false;
    }

    function DBLoadFromPath(mysqli $db) {
        if ($this->ImgPath == null) {
            return false;
        }

        $dbUUID = $db->real_escape_string($this->ImgPath);
        $sql = "select * from post where uuid like '$dbUUID' and IsDeleted = 0 limit 1";

        if (!$res = $db->query($sql)) {
            return false;
        }
        $result = $res->fetch_all();

        if(sizeof($result) == 0) {
            return false;
        }
        $result = $result[0];

        $this->loadFromResult($result);
        $res->close();
        return true;
    }

    /**
     * Loads a post from the PostId of the post
     * @param mysqli $db database
     * @return bool false on failure, true otherwise
     */
    function DBLoadFromID(mysqli $db) {
        if($this->ImgPath == null) {
            return false;
        }

        $dbPostId = $db->real_escape_string($this->ImgPath);
        $sql = "select * from post where uuid like '$dbPostId' and IsDeleted = 0;";

        if (!$res = $db->query($sql)) {
            return false;
        }
        $result = $res->fetch_all();
        if(sizeof($result) == 0) {
            return false;
        }
        $result = $result[0];

        $this->loadFromResult($result);
        $res->close();
        return true;
    }

    /**
     * Saves a like to the database
     * @param int $postid postid
     * @param int $userid userid
     * @param mysqli $db database
     * @return bool Returns true on success, false on failure
     */
    function DBSaveLike(int $postid, int $userid, mysqli $db) {
        //see if the user has like the post already:
        $dbPostId = $db->real_escape_string($postid);
        $dbUserId = $db->real_escape_string($userid);
        $sql = "select count(*) from postliked where User = $dbUserId and Post = $dbPostId";

        if(!$res = $db->query($sql)) {
            return false;
        }
        $result = $res->fetch_all()[0];
        $res->close();
        if($result[0] > 0) {
            return true;
        }

        $stmt = $db->prepare("insert into postliked (Post, User) values (?, ?);");
        $stmt->bind_param("ii", $postid, $userid);
        return $stmt->execute() & $stmt->close();
    }

    /**
     * Deletes a like in the database
     * @param int $postid postId
     * @param int $userid userId
     * @param mysqli $db database
     * @return bool Returns true on success, false on failure
     */
    function DBDeleteLike(int $postid, int $userid, mysqli $db) {
        $stmt = $db->prepare("delete from postliked where Post = ? and User = ?");
        $stmt->bind_param("ii", $postid, $userid);
        return $stmt->execute() & $stmt->close();
    }

    private function loadFromResult($result)
    {
        $this->PostId = $result[0];
        $this->ImgPath = $result[1];
        $this->Title = $result[2];
        $this->Desc = $result[3];
        $this->PostedOn = $result[4];
        $this->fromUser = $result[5];
        $this->isDeleted = $result[6];
        $this->extention = $result[7];
    }


}