/* User */
DROP TABLE IF EXISTS `user`;
create table user
(
    UserID         int auto_increment
        primary key,
    UUID           varchar(30)  not null,
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
        unique (username),
    constraint UUID
        unique(UUID)
);

/* Permissions */
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
INSERT INTO permission (name, isBlocked, isAdmin, canPost, canLike, canComment)
VALUES ('Admin', 0, 1, 1, 1, 1);
INSERT INTO permission (name, isBlocked, isAdmin, canPost, canLike, canComment)
VALUES ('User', 0, 0, 1, 1, 1);
INSERT INTO permission (name, isBlocked, isAdmin, canPost, canLike, canComment)
VALUES ('Blocked', 1, 0, 0, 0, 0);

/* Category */
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

/* Post */
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

/* Post_Category */
DROP TABLE IF EXISTS `post_category`;
create table post_category
(
    PoCaID   int auto_increment
        primary key,
    Post     int not null,
    Category int not null
);

/* Comment */
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

/* PostLiked */
DROP TABLE IF EXISTS `postliked`;
create table postliked
(
    PostLikedID int auto_increment
        primary key,
    Post        int not null,
    User        int not null
);

/* Follow */
DROP TABLE IF EXISTS `follow`;
create table follow
(
    FollowID int null,
    owner    int not null,
    Follows  int not null
);

/* Token */
DROP TABLE IF EXISTS `token`;
create table token
(
    TokenID    int auto_increment
        primary key,
    Token      varchar(32) not null,
    Owner      int         not null,
    Created    datetime    not null,
    ValidUntil datetime    not null
);