let API_TOP_POSTS = './API/feed/top-posts.php';
let API_NEWEST_POSTS = './API/feed/newest.php';
let API_SUGGESTED_POSTS = './API/feed/newest.php'

let content = document.getElementById("content");
let discoverNav = document.getElementById("discover-tab-nav");

let loadingHTML = "";
let currentOffset = 0;
let currentAPI = API_SUGGESTED_POSTS;
let scrollLock = false;

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
    currentAPI = API_SUGGESTED_POSTS;

    loadDiscoverPosts(content);
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
    currentAPI = API_NEWEST_POSTS;
    loadDiscoverPosts(content);
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
    currentAPI = API_TOP_POSTS;
    loadDiscoverPosts(content);
}

function loadDiscoverPosts(element) {
    let apiPath = `${currentAPI}?offset=${currentOffset}`;

    if(currentOffset === 0) {
        element.innerHTML = loader;
    }

    currentOffset += 50;
    fetch(apiPath , {
        credentials: 'same-origin',
    }).then(function (response) {
        return response.json();
    }).then(function (data) {

        if(data.length === 0) {
            removeLoaders();
            return;
        }

        for(let i = 0; i < data.length; i++) {
            element.innerHTML += parsePostToHTML(data[i]);
        }
        removeLoaders();
        element.innerHTML += loader;
        setTimeout(() => {
            scrollLock = false;
        }, 1000)
    });
}

//add eventListener to load more content:
document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('scroll', (e) => {
        checkScrollLoading();
    });
});

//add eventlistner for the "end" key:
document.addEventListener('keyup', (event) => {
    let key = event.key;
    if (key === "End" && !scrollLock) {
        checkScrollLoading();
    }
})

/**
 * Checks if the content should load or not, and if, loads more content, and locks the loading of content.
 */
function checkScrollLoading() {
    let currentScroll = document.body.scrollTop + window.innerHeight;
    let documentHeight = document.body.scrollHeight;

    let modifier = 500;
    if(currentScroll + modifier > documentHeight && !scrollLock) {
        scrollLock = true;
        loadDiscoverPosts(content);
    }
}
