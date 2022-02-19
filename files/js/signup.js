//inputs:
let inputUsername = document.getElementById("input-username");
let inputEmail = document.getElementById("input-email");
let inputPwd1 = document.getElementById("input-pwd1");
let inputPwd2 = document.getElementById("input-pwd2");
let inputGenders = document.getElementsByName("gender");

//outputs:
let outputError = document.getElementById("signup-error");

//add Eventlistener:
document.querySelectorAll('input').forEach(input => {
    input.addEventListener("change", check);
    input.addEventListener("keyup", check);
});

/************************************************ TYPE Input ************************************************/

/**
 * Check for inputs
 */
function check() {
    let errorMessage = usernameCheck();

    if (errorMessage == "") {
        errorMessage = emailCheck();
    }

    if (errorMessage == "") {
        errorMessage = pwdCheck();
    }

    if (errorMessage == "") {
        outputError.innerHTML = "";
    } else {
        outputError.style.display = "block";
        outputError.innerHTML = errorMessage;
    }
}

/**
 * Checks if the password is correct:
 * 
 * password < 8 ("Password has to be at least 8 letters long")
 * password1 != password2 ("Password are not equal")
 * @returns String "" if everything is correct, if not, the error message given in the cases above
 */
function pwdCheck() {
    let pwd1 = inputPwd1.value;
    let pwd2 = inputPwd2.value;

    if (pwd1 == "" && pwd2 == "") {
        return "";
    }

    if (pwd1.length < 8) {
        return "Password has to be at least 8 letters long";
    }

    if (!(pwd1 == "" || pwd2 == "")) {
        if (pwd1 != pwd2) {
            return "Passwords are not equal!";
        }
    }

    if (!hasLowerCase(pwd1) || !hasUpperCase(pwd1)) {
        return "Password must contain upper and lowercase letters!";
    }

    if (!containsNumber(pwd1)) {
        return "Password muste contain a number!"
    }

    return "";
}

/**
 * Checks the Username
 * 
 * length < 4: "Username too short (4 characters)"
 * length > 30: "Username can't be over 30 characters long"
 * @returns {String} "" if false, errors listed above otherwise
 */
function usernameCheck() {
    let username = inputUsername.value;

    if (username == "") {
        return "";
    }

    if (username.length < 4) {
        return "Username too short (4 characters)"
    }

    if (username.length > 30) {
        return "Username can't be over 30 characters long";
    }

    return "";
}

/**
 * Checks the email
 * 
 * invalid: "Invalid Email"
 * @returns {String} "" if flase, errors listed above otherwise
 */
function emailCheck() {
    let email = inputEmail.value;

    if (email == "") {
        return "";
    }

    //https://stackoverflow.com/questions/46155/whats-the-best-way-to-validate-an-email-address-in-javascript
    if (email.toLowerCase().match(
        /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    ) === null) {
        return "Invalid Email";
    }

    return "";
}

/**
 * Checks the gender
 * 
 * not selected: "Please select a gender"
 * @returns {String} "" if false, errors listed above otherwise
 */
function genderCheck() {
    if(genderValue() === null) {
        return "Please select a gender";
    }
    return "";
}

/************************************************ UTIL ************************************************/

/**
 * checks if a String has a lowercase letter inside
 * @param {String} str String to check
 * @returns {Boolean} true, if there is a lowercase letter, false otherwise
 */
 function hasLowerCase(str) {
    return str.toUpperCase() !== str;
}

/**
 * checks if a String has a uppercase letter inside
 * @param {String} str String to check
 * @returns {Boolean} true, if there is a uppercase letter, false othewise
 */
function hasUpperCase(str) {
    return str.toLowerCase() !== str;
}

/**
 * checks if a String contains a number
 * @param {String} str String to check
 * @returns {Boolean} true, if there is a number
 */
function containsNumber(str) {
    return /\d/.test(str);
}

/**
 * Displays the error
 * @param {String} message 
 */
function error(message) {
    outputError.style.display = "block";
    outputError.innerHTML = message;
}

/**
 * Value of the Gender radio buttons
 */
function genderValue() {
    for(let i = 0; i < inputGenders.length; i++) {
        if(inputGenders[i].checked) {
            return inputGenders[i].value;
        }
    }
    return null;
}

/************************************************ SUBMIT ************************************************/

function createUser() {
    let errorMessage = "";
    
    //check Username: 
    errorMessage = usernameCheck();
    if(errorMessage != "") {
        error(errorMessage);
        return false;
    }
    if(inputUsername.value == "") {
        error("Choose a username first!");
        return false;
    }

    //email:
    errorMessage = emailCheck();
    if(errorMessage != "") {
        error(errorMessage);
        return false;
    }
    if(inputEmail.value == "") {
        error("Email required");
        return false;
    }

    //gender: 
    errorMessage = genderCheck();
    if(errorMessage != "") {
        error(errorMessage);
        return false;
    }

    //password:
    errorMessage = pwdCheck();
    if(errorMessage != "") {
        error(errorMessage);
        return false;
    }
    if(inputPwd1.value == "" || inputPwd2.value == "") {
        error("Password is required");
        return false;
    }

    //TODO: send request to Server: 

    return false;
}