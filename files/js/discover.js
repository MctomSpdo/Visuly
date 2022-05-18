let API_TOP_POSTS = './API/feed/top-posts.php';
let API_NEWEST_POSTS = './API/feed/newest.php';

let content = document.getElementById("content");
let discoverNav = document.getElementById("discover-tab-nav");

let loadingHTML = "";
let currentOffset = 0;

//what is currently selected:
let current;

init();
function init() {
    loadSuggested()
}

function clearNav() {
    Array.from(discoverNav.children).forEach((item => {
        item.id = "";
    }));
}

function loadSuggested() {

    if(current == "suggested") {
        return;
    }
    content.innerHTML = loadingHTML;
    clearNav();
    discoverNav.children[0].id = "discover-selected";
    current = "suggested"
    currentOffset = 0;

    loadDiscoverPosts(content, API_NEWEST_POSTS);
}

function loadDiscoverNewest() {
    if(current == "newest") {
        return;
    }
    content.innerHTML = loadingHTML;
    clearNav();
    discoverNav.children[1].id = "discover-selected";
    current = "newest";
    currentOffset = 0;
}

function loadDiscoverTop() {
    if(current == "top") {
        return;
    }
    content.innerHTML = loadingHTML;
    clearNav();
    discoverNav.children[2].id = "discover-selected";
    current = "top";
    currentOffset = 0;
    loadDiscoverPosts(content, `${API_TOP_POSTS}?offset=${currentOffset}`);
}


function loadDiscoverPosts(element, apiPath) {
    fetch(apiPath , {
        credentials: 'same-origin',
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        console.log(data);

        for(let i = 0; i < data.length; i++) {
            element.innerHTML += parsePostToHTML(data[i]);
        }
    });
}

function loadDiscoverNewest() {
    if(current == "newest") {
        return;
    }
    content.innerHTML = loadingHTML;
    clearNav();
    discoverNav.children[1].id = "discover-selected";
    current = "newest";
    offset = 0;
    loadDiscoverPosts(content, `${API_NEWEST_POSTS}?offset=${offset}`)
}
