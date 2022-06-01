let api_posts = "./API/feed/home.php";
let api_like = "./API/post/like.php";
let api_newest = "./API/feed/newest.php";

let loader = '<div class="content-loading"><div class="loader"></div></div>';

function loadHome(element) {
    loadPosts(element, api_posts, 0);
}

function loadNewest(element) {
    loadPosts(element, api_newest, 0);
}

function loadPosts(element, apiPath) {
    fetch(apiPath , {
        credentials: 'same-origin',
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        console.log(data);

        if(data.posts == "no posts yet" || data.posts.length == 0) {
            removeLoaders();
            return;
        }

        data.posts.forEach((postElement) => {
            element.innerHTML += parsePostToHTML(postElement);
        });
        removeLoaders();
        element.innerHTML += loader;
    });
}

function parsePostToHTML(post) {
    let postlikeImage = ((post.hasLiked == "true" || post.hasLiked === true) ? "heart_filled" : "heart") + ".svg";

    let postdescr = post.description;

    if(postdescr.length > 100) {
        postdescr = postdescr.substring(0, 98) + " ...";
    }

    return `<div class="post" id="${post.postId}" onclick="postPressed(this)">
            <div class="post-header">
                <a href="./user.php?user=${post.postedFromID}" class="post-user-wrapper">
                    <div class="post-user-image">
                        <img src="./files/img/users/${post.postedFromImage}" alt="User">
                    </div>
                    <div class="post-user-name">
                        <p>${post.postedFrom}</p>
                    </div>
                    <div></div>
                </a>

                <div class="post-title-wrapper">
                    <h3 onclick="redirectPost(this);">${post.title}</h3>
                </div>
            </div>
            <div class="post-img">
                <div class="post-img-wrapper">
                    <img src="./files/img/post/${post.path}" alt="${post.title}">
                </div>
            </div>
            <div class="post-body">
                <div class="post-interaction-wrapper">
                    <div class="post-like">
                        <div class="post-interaction-imgwrapper">
                            <img src="./files/img/${postlikeImage}" alt="Likes" onclick="likeButtonPress(this); event.stopPropagation();" ${(post.hasLiked == "true" || post.hasLiked === true) ? 'class="liked"' : ""}>
                        </div>
                        <div class="post-interaction-textwrapper">
                            <p>${getLikesSpelled(post.likes)}</p>
                        </div>
                    </div>
                    <div class="post-comment">
                        <div class="post-interaction-imgwrapper invert-image-dark">
                            <img src="./files/img/comment.svg" alt="Comment">
                        </div>
                        <div class="post-interaction-textwrapper">
                            <p>${getCommentsSpelled(post.comments)}</p>
                        </div>
                    </div>
                    <div class="post-share" onclick="shareEventHandler(this); event.stopPropagation();">
                        <div class="post-interaction-imgwrapper invert-image-dark">
                            <img src="./files/img/share.svg" alt="Share">
                        </div>
                        <div class="post-interaction-textwrapper">
                            <p>Share</p>
                        </div>
                    </div>
                </div>
                <div class="post-descr">
                    <p>${postdescr}</p>
                </div>
            </div>
            <div class="post-footer"></div>
        </div>`
}
/**************************************************** POST INTERACTION *******************************************************/
/**
 * Onclick listener for post
 * @param element postelement
 */
function postPressed(element) {
    let postId = element.id;
    window.location.href = getPostLink(postId);
}

/**************************************************** LIKE POST *******************************************************/

function likeButtonPress(element) {
    console.log(element)
    let parentNumber = 5;
    let postElement = element;
    for(let i = 0; i < parentNumber; i++) {
        postElement = postElement.parentNode;
    }
    let postId = postElement.id;
    let isLiked = !element.classList.contains("liked");

    likePost(postId, isLiked, element);
}

/**
 * sends an API request to the server to like a certain post
 * @param post postUUid
 * @param like true if like, false if unlike
 * @param element image that was clicked
 */
function likePost(post, like, element) {
    let formData = new FormData();

    formData.append('post', post);
    formData.append('like', (like) ? "like" : "unlike");

    fetch(api_like, {
        method: 'post',
        credentials: 'same-origin',
        body: formData
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        if(data.success === false) {
            alert("Could not like");
            return;
        }

        if(element.classList.contains("liked")) {
            element.src = "./files/img/heart.svg";
        } else {
            element.src = "./files/img/heart_filled.svg";
        }
        element.classList.toggle("liked");

        let likeElement = element.parentNode.parentNode.children[1].children[0];
        likeElement.innerHTML = getLikesSpelled(data.likes);
    });
}

/**************************************************************** SHARE POST *******************************************/

function shareEventHandler(element) {
    let postElement = element;
    for(let i = 0; i < 3; i++) {
        postElement = postElement.parentNode;
    }

    let postId = postElement.id;
    navigator.clipboard.writeText(getPostLink(postId));
    element.children[1].innerHTML = "copied";

    setTimeout(() => {
        element.children[1].innerHTML = "Share";
    }, 2000)
}

function redirectPost(element) {
    let postElement = element;
    for(let i = 0; i < 3; i++) {
        postElement = postElement.parentNode;
    }

    let postid = postElement.id;
    window.location.href = getPostLink(postid);
}

function getPostLink(postId) {
    let location = document.URL.substr(0,document.URL.lastIndexOf('/'));
    return location + "/post.php?post=" + postId;
}

/**
 * Gets the likes spelled out
 * @param likes
 * @returns {string}
 */
function getLikesSpelled(likes) {
    if(likes == 0) {
        return "no likes";
    } else if (likes == 1) {
        return "1 like";
    } else {
        return `${likes} likes`;
    }
}

function getCommentsSpelled(comments) {
    if(comments == 0) {
        return "none";
    } else if (comments == 1) {
        return "1 comment";
    } else {
        return `${comments} comments`
    }
}

/**
 * removes all loaders from the site
 */
function removeLoaders() {
    Array.from(document.getElementsByClassName("content-loading")).forEach((element) => {
        element.parentNode.removeChild(element);
    })
}