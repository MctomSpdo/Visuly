/* Permissions */
drop table if exists `PERMISSION`;
create table PERMISSION
(
    PermissionID int auto_increment
        primary key,
    name         varchar(30) not null,
    isBlocked    tinyint(1)  not null,
    isAdmin      tinyint(1)  not null,
    canPost      tinyint(1)  not null,
    canLike      tinyint(1)  not null,
    canComment   tinyint(1)  not null,
    constraint name
        unique (name)
);

/*default permissions: */
insert into PERMISSION (name, isBlocked, isAdmin, canPost, canLike, canComment)
values ('Admin', 0, 1, 1, 1, 1);
insert into PERMISSION (name, isBlocked, isAdmin, canPost, canLike, canComment)
values ('User', 0, 0, 1, 1, 1);
insert into PERMISSION (name, isBlocked, isAdmin, canPost, canLike, canComment)
values ('Blocked', 1, 0, 0, 0, 0);

/* User */
drop table if exists `USER`;
create table USER
(
    UserID         int auto_increment
        primary key,
    uuid           varchar(30)  not null,
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
    PermissionID   int          not null,
    foreign key (PermissionID) references PERMISSION (PermissionID),
    constraint email
        unique (email),
    constraint username
        unique (username),
    constraint UUID
        unique (UUID)
);

/* Category */
drop table if exists `CATEGORY`;
create table CATEGORY
(
    CategoryID  int auto_increment
        primary key,
    name        varchar(30)  not null,
    description varchar(300) not null,
    constraint name
        unique (name)
);

/* Post */
drop table if exists `POST`;
create table POST
(
    PostID      int auto_increment
        primary key,
    uuid        varchar(60)   not null,
    title       varchar(30)   not null,
    description varchar(2000) not null,
    postedOn    datetime      not null,
    UserID      int           not null,
    isDeleted   tinyint       not null,
    extention   varchar(3)    not null,
    foreign key (UserID) references USER (UserID)
);

/* Post_Category */
drop table if exists `POST_CATEGORY`;
create table POST_CATEGORY
(
    PoCaID     int auto_increment
        primary key,
    PostID     int not null,
    CategoryID int not null,
    foreign key (CategoryID) references CATEGORY (CategoryID),
    foreign key (PostID) references POST (PostID)
);

/* Comment */
drop table if exists `COMMENT`;
create table COMMENT
(
    CommentID int auto_increment
        primary key,
    content   varchar(300) not null,
    UserID    int          not null,
    PostID    int          not null,
    postedOn  datetime     not null,
    isDeleted tinyint      not null,
    foreign key (UserID) references USER (UserID),
    foreign key (PostID) references POST (PostID)
);

/* PostLiked */
drop table if exists `POSTLIKED`;
create table POSTLIKED
(
    PostLikedID int auto_increment
        primary key,
    PostID      int not null,
    UserID      int not null,
    foreign key (PostID) references POST (PostID),
    foreign key (UserID) references USER (UserID)
);

/* Follow */
drop table if exists `FOLLOW`;
create table FOLLOW
(
    FollowID int auto_increment
        primary key,
    UserID   int not null,
    Follows  int not null,
    foreign key (UserID) references USER (UserID),
    foreign key (Follows) references USER (UserID)
);

/* Token */
drop table if exists `TOKEN`;
create table TOKEN
(
    TokenID    int auto_increment
        primary key,
    token      varchar(32) not null,
    UserID     int         not null,
    created    datetime    not null,
    validUntil datetime    not null,
    foreign key (UserID) references USER (UserID)
);

/* PasswordReset */
drop table if exists `PASSWORDRESET`;
create table PASSWORDRESET
(
    ID         int auto_increment primary key,
    UserID     int         not null,
    uuid       varchar(60) not null,
    code       varchar(4)  not null,
    created    datetime    not null,
    validUntil datetime    not null,

    constraint PASSWORDRESET_fguser
        foreign key (UserID) references user (UserID),
    constraint uuid
        unique (uuid)
);