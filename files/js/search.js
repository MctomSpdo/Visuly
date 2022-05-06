let searchPath = './API/search.php'
let loader = "<div class=\"loader\"></div>";

let searchValue = document.getElementById("search-words").innerHTML;
let searchbar = document.getElementById("header-searchbar");

let userSearch = document.getElementById("search-user-content");

let dataReceived = false;

init();
function init() {
    searchbar.value = searchValue;
    search(searchValue);
}

/**
 * searches for a given keyword
 * @param keyword keyword to search
 */
function search(keyword) {
    fetch(searchPath + `?search=${keyword}` , {
        credentials: 'same-origin',
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        dataReceived = true;

        displayUserResult(data);
    });

    setTimeout(() => {
        if(!dataReceived) {
            userSearch.innerHTML = loader;
        }
    }, 100)
}

function redirectUser(element) {
    window.location.href=`./user.php?user=${element.id}`;
}

function displayUserResult(data) {
    let html = "";
    for(let i = 0; i < data.user.length; i++) {
        html += userToHTML(data.user[i]);
    }
    if(html === "") {
        html = '<p>No Results found</p>';
    }
    userSearch.innerHTML = html;
}

function userToHTML(data) {
    return `<div class="search-userresult" onclick="redirectUser(this)" id="${data.uuid}">
                <div>
                    <img src="./files/img/users/${data.profilePic}" alt="User-image">
                </div>
                <p>${data.username}</p>
            </div>`
}