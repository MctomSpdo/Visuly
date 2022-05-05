let api_newPassword = "./API/user/change-password.php";
let api_edit = "./API/user/edit.php"

//data
let username = document.getElementById("data-username").innerHTML;
let email = document.getElementById("data-email").innerHTML;
let phoneNumber = document.getElementById("data-phoneNumber").innerHTML;
let desc = document.getElementById("data-desc").innerHTML;

//elements:
let userForm;
let contentArea = document.getElementById("current-setting-wrapper");
let usernameInput;
let emailInput;
let phoneNumberInput;
let descInput;

//buttons:
let saveButton;
let cancelButton;


init();

function init() {
    loadEditUser(null);
}

/**
 * Checks if the current input fields have the same value as the already defined data
 * @returns {boolean} true if same, false otherwise
 */
function checkUserUpdate() {
    return usernameInput.value != username || emailInput.value != email || phoneNumberInput.value != phoneNumber || descInput.value != desc;
}

/**
 * Turns on or off saveButton on EditUser
 */
function checkUserSubmit() {
    saveButton.disabled = !checkUserUpdate();
}

/**
 * Loads the edit user submenu
 * @param element onClick element
 */
function loadEditUser(element) {
    if (element !== null) {
        //change what element is selected in nav:
        Array.from(document.getElementById("setting-nav").children).forEach((item) => {
            item.id = "";
        })
        element.id = "nav-active";
    }

    //load new content:
    contentArea.innerHTML = `<h1>Edit User</h1>
                <form id="settings-profile-wrapper">
                    <div id="settings-user-profilepic">
                        <div id="settings-userpfp-wrapper">
                            <input type="file" accept="image/png, image/jpeg, image/gif" id="user-img-upload">
                            <label for="user-img-upload" id="user-img-upload-label">
                                <div>
                                    <p>Upload File</p>
                                    <img src="files/img/users/test.png" alt="User image">
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="username">Username</label>
                        <br>
                        <input type="text" name="username" id="input-username">
                        <br>
                        
                        <label for="desc">Description</label>
                        <br>
                        <input type="text" name="desc" id="input-desc">
                        <br>

                        <label for="email">Email</label>
                        <br>
                        <input type="text" name="email" id="input-email">
                        <br>

                        <label for="phonenumber">Phone number</label>
                        <br>
                        <input type="text" name="phonenumber" id="input-phone">

                        <div class="settings-form-buttons">
                            <button type="submit" disabled id="save-button">Save</button>
                            <button id="cancel-button">Cancel</button>
                        </div>
                    </div>
                </form>`;

    //load elements:
    userForm = document.getElementById("settings-profile-wrapper");
    usernameInput = document.getElementById("input-username");
    emailInput = document.getElementById("input-email");
    phoneNumberInput = document.getElementById("input-phone");
    descInput = document.getElementById("input-desc");

    saveButton = document.getElementById("save-button");
    cancelButton = document.getElementById("cancel-button");

    //add submit eventListener:
    userForm.addEventListener("submit", (event) => {
        event.preventDefault();
        console.log("submitting to Server");
        sendToServer();
    });

    //add eventListeners to all input fields
    Array.from(userForm.elements).forEach((item) => {
        item.addEventListener("keyup", checkUserSubmit);
    });

    //set data:
    usernameInput.value = username;
    emailInput.value = email;
    phoneNumberInput.value = phoneNumber;
    descInput.value = desc;
}

function sendToServer() {
    userForm.disabled = true;
    let formData = new FormData();
    formData.append("username", usernameInput.value);
    formData.append("email", emailInput.value);
    formData.append("phonenumber", phoneNumberInput.value);
    formData.append("description", descInput.value);

    fetch(api_edit, {
        method: 'post',
        credentials: 'same-origin',
        body: formData
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        if (data.success !== undefined && data.success == true) {
            phoneNumber = phoneNumberInput.value;
            email = emailInput.value;
            username = usernameInput.value;
            userForm.disabled = false;
            contentArea.innerHTML = "<h1>Information updated!</h1>";
        } else if (data.error !== undefined) {
            error(data.error);
        } else {
            error("Unknown error while sending Request!");
        }
    });
}

function error(text) {
    alert("Error" + text);
}

/************************************************ PASSWORD ************************************************/

let inputCurrentPw;
let inputNewPw;
let reenterNewPw;

let passwordError;

/**
 * loads the submenu for changing the password.
 * @param element onClick element
 */
function loadPassword(element) {
    //change what element is selected in nav:
    Array.from(document.getElementById("setting-nav").children).forEach((item) => {
        item.id = "";
    })
    element.id = "nav-active";

    contentArea.innerHTML = `<div id="change-password">
                    <h1>Change Password</h1>

                    <form class="setting-form-80" id="settings-password-wrapper">
                        <label for="current-password">Current Password</label>
                        <br>
                        <input type="password" placeholder="Current Password" name="current-password" id="input-currentPw">
                        <br>
                        <label for="new-password">New Password</label>
                        <br>
                        <input type="password" placeholder="New Password" name="new-password" id="input-newPw">
                        <br>
                        <label for="retype-password">Retype Password</label>
                        <br>
                        <input type="password" placeholder="Retype new Password" name="retype-password" id="input-reenterPw">

                        <p id="password-error" class="txt-red"></p>

                        <div class="settings-form-buttons">
                            <button type="submit" disabled id= "save-button">Save</button>
                            <button id="cancel-button">Cancel</button>
                        </div>
                    </form>
                </div>`;

    //update variables:
    inputCurrentPw = document.getElementById("input-currentPw");
    inputNewPw = document.getElementById("input-newPw");
    reenterNewPw = document.getElementById("input-reenterPw");

    saveButton = document.getElementById("save-button");
    cancelButton = document.getElementById("cancel-button");
    userForm = document.getElementById("settings-password-wrapper");
    passwordError = document.getElementById("password-error");

    //add eventListeners to all input fields
    Array.from(userForm.elements).forEach((item) => {
        item.addEventListener("keyup", checkPasswordSubmit);
    });

    userForm.addEventListener("submit", (event) => {
        event.preventDefault();
        pwSendToServer();
    });

    cancelButton.addEventListener("click", () => {
        inputNewPw.value = "";
        inputCurrentPw.value = "";
        reenterNewPw.value = "";
        checkPasswordSubmit();
    });
}

/**
 * checks if the password Values can enable a save button
 * @returns {boolean} true of none of them are emtpy
 */
function checkPasswordUpdate() {
    return inputCurrentPw.value != "" && inputNewPw.value != "" && reenterNewPw.value != "";
}

/**
 * Enables the save button if Input fields are valid
 */
function checkPasswordSubmit() {
    saveButton.disabled = !(checkPasswordValues() & checkPasswordUpdate());
}

/**
 * Validates the password that is entered by the user
 */
function checkPasswordValues() {
    let password = inputNewPw.value;

    if (password == "") {
        return true;
    }

    if (reenterNewPw.value != password) {
        pwError("Retyped password is not the same as new password!");
        return false;
    }
    if (!hasLowerCase(password)) {
        pwError("Password has to have at least 1 lowercase letter");
        return false;
    }
    if (!hasUpperCase(password)) {
        pwError("Password has to have at least 1 uppercase letter");
        return false;
    }
    if (!containsNumber(password)) {
        pwError("Password has to have at least 1 number letter");
        return false;
    }
    if (inputCurrentPw.value == password) {
        pwError("New Password can't be the same as the old one");
        return false;
    }
    if (password.length < 8) {
        pwError("Password has to be at least 8 characters long");
        return false;
    }
    pwError("");
    return true;
}

/**
 * Sets an error to the password error field
 * @param error message
 */
function pwError(error) {
    passwordError.style.display = (error == "") ? "none" : "block"
    passwordError.innerHTML = error;
}

/**
 * Sends the request to change the passwords to the server
 */
function pwSendToServer() {
    userForm.disabled = true;
    if (!checkPasswordValues()) {
        return;
    }

    let formData = new FormData();
    formData.append("currentPassword", inputCurrentPw.value);
    formData.append("newPassword", inputNewPw.value);

    fetch(api_newPassword, {
        method: 'post',
        credentials: 'same-origin',
        body: formData
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        if (data.success !== undefined && data.success == true) {
            contentArea.innerHTML = "<h1>Password updated!</h1>";
        } else if (data.error !== undefined) {
            pwError(data.error);
        } else {
            pwError("Unknown error while sending Request!");
        }
    });
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
 * checks if a String has an uppercase letter inside
 * @param {String} str String to check
 * @returns {Boolean} true, if there is an uppercase letter, false otherwise
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