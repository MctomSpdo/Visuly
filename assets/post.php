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
        $sql = "select * from post where uuid like '$dbUUID' limit 1";

        if (!$res = $db->query($sql)) {
            return false;
        }
        $result = $res->fetch_all()[0];

        $this->loadFromResult($result);
        $res->close();
        return true;
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