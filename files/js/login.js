let api_login = "./API/user/login.php"

let form = document.getElementById("login-form");
let inputUsername = document.getElementById("login-username");
let inputPassword = document.getElementById("login-password");
let outputError = document.getElementById("sign-in-error");

//submit eventlistener: 
form.addEventListener('submit', (event) => {
    event.preventDefault();
    login();
})

/**
 * Shows a error on the login formula
 * @param {String} message 
 */
function error(message) {
    outputError.innerHTML = message;
}

/**
 * sends login to the Server
 */
function login() {
    let username = inputUsername.value;
    let password = inputPassword.value;

    if(username == "") {
        error("input a username or email first");
        return;
    }
    if(password.length < 8) {
        error("password invalid");
        return;
    }

    fetch(api_login, {
        method: 'post',
        body: JSON.stringify({"username" : username, "password" : password})
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        console.log(data);

        if(!data.userExists) {
            error("Username or password incorrect!");
        } else {
            if(data.successfull) {
                window.location.replace("./");
            } else {
                error("An unknown error occured, please try again later");
            }
        }
    });
}