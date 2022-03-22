let api_posts = "./API/feed/home.php";
let api_like = "./API/post/like.php";

function loadHome(element) {
    fetch(api_posts, {
        credentials: 'same-origin',
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        console.log(data);

        if(data.posts == "no posts yet") {
            element.innerHTML = "there are no posts yet";
        }

        data.posts.forEach((postElement) => {
            element.innerHTML += parsePostToHTML(postElement);
        });
    });
}

//TODO: correct parsing (amout of likes, user has liked, postedFrom, postedFromPfp)
function parsePostToHTML(post) {
    let likesSpelled = "";
    let commentsSpelled = "";
    let postlikeImage = ((post.hasLiked) ? "heart_filled" : "heart") + ".svg";


    return `<div class="post" id="${post.postId}">
            <div class="post-header">
                <a href="./user.php?user=<?php echo $postUser->UUID?>" class="post-user-wrapper">
                    <div class="post-user-image">
                        <img src="./files/img/users/${post.postedFromImage}" alt="User">
                    </div>
                    <div class="post-user-name">
                        <p>${post.postedFrom}</p>
                    </div>
                    <div></div>
                </a>

                <div class="post-title-wrapper">
                    <h3>${post.title}</h3>
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
                            <img src="./files/img/${postlikeImage}" alt="Likes" onclick="likeButtonPress(this);">
                        </div>
                        <div class="post-interaction-textwrapper">
                            <p>${post.likes}</p>
                        </div>
                    </div>
                    <div class="post-comment">
                        <div class="post-interaction-imgwrapper">
                            <img src="./files/img/comment.svg" alt="Comment">
                        </div>
                        <div class="post-interaction-textwrapper">
                            <p>${post.comments}</p>
                        </div>
                    </div>
                    <div class="post-share">
                        <div class="post-interaction-imgwrapper">
                            <img src="./files/img/share.svg" alt="Share">
                        </div>
                        <div class="post-interaction-textwrapper">
                            <p>Share</p>
                        </div>
                    </div>
                </div>
                <div class="post-descr">
                    <p>${post.description}</p>
                </div>
            </div>
            <div class="post-footer"></div>
        </div>`
}


/**************************************************** LIKE POST *******************************************************/



function likeButtonPress(element) {
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
        let likes = data.likes;

        if(likes == 0) {
            likeElement.innerHTML = "no likes";
        } else if (likes == 1) {
            likeElement.innerHTML = "1 like";
        } else {
            likeElement.innerHTML = `${likes} likes`;
        }
    });
}