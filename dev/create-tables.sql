/* User
CREATE TABLE `ssprojekt22`.`user` ( `UserID` INT NOT NULL AUTO_INCREMENT ,  `username` VARCHAR(30) NOT NULL ,  `description` VARCHAR(300) NOT NULL ,  `gender` VARCHAR(1) NOT NULL ,  `profilePic` VARCHAR(30) NOT NULL ,  `createdOn` DATETIME NOT NULL ,  `phoneNumber` VARCHAR(15) NOT NULL ,  `email` VARCHAR(254) NOT NULL ,  `password` VARCHAR(32) NOT NULL ,  `deleted` TINYINT NOT NULL ,  `lastLogin` DATETIME,  `lastTriedLogin` DATETIME,  `permission` INT NOT NULL ,    PRIMARY KEY  (`ID`),    UNIQUE  (`username`),    UNIQUE  (`email`)) ENGINE = InnoDB;
*/
DROP TABLE IF EXISTS `user`;
create table user
(
    UserID         int auto_increment
        primary key,
    username       varchar(30)  not null,
    description    varchar(300) not null,
    gender         varchar(1)   not null,
    profilePic     varchar(30)  not null,
    createdOn      datetime     not null,
    phoneNumber    varchar(15)  not null,
    email          varchar(254) not null,
    password       varchar(32)  not null,
    deleted        tinyint      not null,
    lastLogin      datetime     null,
    lastTriedLogin datetime     null,
    permission     int          not null,
    constraint email
        unique (email),
    constraint username
        unique (username)
);

/* Permissions
CREATE TABLE `ssprojekt22`.`permission` ( `PermID` INT NOT NULL AUTO_INCREMENT ,  `name` VARCHAR(30) NOT NULL ,  `isBlocked` BOOLEAN NOT NULL ,  `isAdmin` BOOLEAN NOT NULL ,  `canPost` BOOLEAN NOT NULL ,  `canLike` BOOLEAN NOT NULL ,  `canComment` BOOLEAN NOT NULL ,    PRIMARY KEY  (`ID`),    UNIQUE  (`name`)) ENGINE = InnoDB; 
*/
DROP TABLE IF EXISTS `permission`;
create table permission
(
    PermID     int auto_increment
        primary key,
    name       varchar(30) not null,
    isBlocked  tinyint(1)  not null,
    isAdmin    tinyint(1)  not null,
    canPost    tinyint(1)  not null,
    canLike    tinyint(1)  not null,
    canComment tinyint(1)  not null,
    constraint name
        unique (name)
);

/*default permissions: */
INSERT INTO permission (name, isBlocked, isAdmin, canPost, canLike, canComment) VALUES ('Admin', 0, 1, 1, 1, 1);
INSERT INTO permission (name, isBlocked, isAdmin, canPost, canLike, canComment) VALUES ('User', 0, 0, 1, 1, 1);
INSERT INTO permission (name, isBlocked, isAdmin, canPost, canLike, canComment) VALUES ('Blocked', 1, 0, 0, 0, 0);

/* Category 
CREATE TABLE `ssprojekt22`.`permission` ( `ID` INT NOT NULL AUTO_INCREMENT ,  `name` VARCHAR(30) NOT NULL ,  `isBlocked` BOOLEAN NOT NULL ,  `isAdmin` BOOLEAN NOT NULL ,  `canPost` BOOLEAN NOT NULL ,  `canLike` BOOLEAN NOT NULL ,  `canComment` BOOLEAN NOT NULL ,    PRIMARY KEY  (`ID`),    UNIQUE  (`name`)) ENGINE = InnoDB;
*/
DROP TABLE IF EXISTS `category`;
create table category
(
    CategoryID  int auto_increment
        primary key,
    name        varchar(30)  not null,
    description varchar(300) not null,
    constraint name
        unique (name)
);

/* Post 
CREATE TABLE `ssprojekt22`.`post` ( `PostID` INT NOT NULL AUTO_INCREMENT ,  `ImgPath` VARCHAR(60) NOT NULL ,  `Title` VARCHAR(30) NOT NULL ,  `Description` VARCHAR(2000) NOT NULL ,  `PostedOn` DATETIME NOT NULL ,  `FromUser` INT NOT NULL ,  `IsDeleted` TINYINT NOT NULL ,    PRIMARY KEY  (`PostID`)) ENGINE = InnoDB;
*/
DROP TABLE IF EXISTS `post`;
create table post
(
    PostID      int auto_increment
        primary key,
    ImgPath     varchar(60)   not null,
    Title       varchar(30)   not null,
    Description varchar(2000) not null,
    PostedOn    datetime      not null,
    FromUser    int           not null,
    IsDeleted   tinyint       not null
);

/* Post_Category
CREATE TABLE `ssprojekt22`.`post_category` ( `PoCaID` INT NOT NULL AUTO_INCREMENT ,  `Post` INT NOT NULL ,  `Category` INT NOT NULL ,    PRIMARY KEY  (`PoCaID`)) ENGINE = InnoDB;
*/
DROP TABLE IF EXISTS `post_category`;
create table post_category
(
    PoCaID   int auto_increment
        primary key,
    Post     int not null,
    Category int not null
);

/* Comment 
CREATE TABLE `ssprojekt22`.`comment` ( `CommentID` INT NOT NULL AUTO_INCREMENT ,  `Content` VARCHAR(300) NOT NULL ,  `Owner` INT NOT NULL ,  `Post` INT NOT NULL ,  `isDeleted` TINYINT NOT NULL ,    PRIMARY KEY  (`CommentID`)) ENGINE = InnoDB;
*/
DROP TABLE IF EXISTS `comment`;
create table comment
(
    CommentID int auto_increment
        primary key,
    Content   varchar(300) not null,
    Owner     int          not null,
    Post      int          not null,
    isDeleted tinyint      not null
);

/* PostLiked
CREATE TABLE `ssprojekt22`.`postliked` ( `PostLikedID` INT NOT NULL AUTO_INCREMENT ,  `Post` INT NOT NULL ,  `User` INT NOT NULL ,    PRIMARY KEY  (`PostLikedID`)) ENGINE = InnoDB;
*/
DROP TABLE IF EXISTS `postliked`;
create table postliked
(
    PostLikedID int auto_increment
        primary key,
    Post        int not null,
    User        int not null
);

/* Follow
CREATE TABLE `ssprojekt22`.`follow` ( `FollowID` INT NULL ,  `owner` INT NOT NULL ,  `Follows` INT NOT NULL ) ENGINE = InnoDB;
*/
DROP TABLE IF EXISTS `follow`;
create table follow
(
    FollowID int null,
    owner    int not null,
    Follows  int not null
);

/* Token
CREATE TABLE `ssprojekt22`.`token` ( `TokenID` INT NOT NULL AUTO_INCREMENT ,  `Token` VARCHAR(254) NOT NULL ,  `Owner` INT NOT NULL ,  `Created` DATETIME NOT NULL ,  `ValidUntil` DATETIME NOT NULL ,    PRIMARY KEY  (`TokenID`)) ENGINE = InnoDB;
*/
DROP TABLE IF EXISTS `token`;
create table token
(
    TokenID    int auto_increment
        primary key,
    Token      varchar(32) not null,
    Owner      int          not null,
    Created    datetime     not null,
    ValidUntil datetime     not null
);