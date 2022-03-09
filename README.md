# VISULY

Medientechnik SommerSemester Projekt 2022

[Github Repo](https://github.com/MctomSpdo/Medt_SSProject2022)

## Setting up VIUSLY: 

If you want to test or use Visuly, there is a setup process, that is rather minimal

In the directors /files, there is a **config_template.json** file. Rename this to **config.json**!

## Config File

### Database
The database part is for credentials.
The database has to be a mysql or mariadb database. 

````
host: hostname or ip address
database: mysql database
username: username for the database
password: password for the database
````

#### Token
This is for the authentication token, so that you keep being logged in

````
name: name of the token
salt: salt used in the hashing of the token (String)
length: character length of the token
lifespan: how long the token is valid in months
````

#### Post
This is the configuration of the post section

````
defaultDir: defautlt directory to save posts to
nameLength: characterlength of the postid.
maxSize: Max Size of the uploaded image in bytes
````

#### Other

````
userImageFolder: the default directory for user images
userDefaultImage: the name of the default image located in the default user directory
userDefaultPermission: The Permission Level that the user gets per default (concludes to the permission table in the datbase)
passwordSalt: The salt used in the hashing of the password
````