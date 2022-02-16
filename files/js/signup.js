//inputs:
let inputUsername = document.getElementById("input-username");
let inputEmail = document.getElementById("input-email");
let inputPwd1 = document.getElementById("input-pwd1");
let inputPwd2 = document.getElementById("input-pwd2"); 

//inputs are valid: 
let isWrong = true;

//add Eventlistener:
document.querySelectorAll('input').forEach(input => {
    input.addEventListener("change", check);
    input.addEventListener("keyup", check);
});

/**
 * Check for inputs
 */
function check() {
    let errorMessage = "";

    let pwd1 = inputPwd1.value;
    let pwd2 = inputPwd2.value;

    if(pwd1 != pwd2) {
        errorMessage="Passwords are not equal!";
    } 

    console.log(errorMessage);
}