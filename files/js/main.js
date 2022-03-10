let api_like = "./API/post/like.php"

function likeButtonPress(element) {
    let parentNumber = 5;
    let postElement = element;
    for(let i = 0; i < parentNumber; i++) {
        postElement = postElement.parentNode;
    }
    let postId = postElement.id;
    let isLiked = element.classList.contains("liked");

    likePost(postId, isLiked, element);
}

/**
 * sends an API request to the server to like a certain post
 * @param post postUUid
 * @param like true if like, false if unlike
 */
function likePost(post, like, element) {
    let formData = new FormData();

    console.log("like: " + like);

    formData.append('post', post);
    formData.append('like', (like) ? "like" : "unlike");

    fetch(api_like, {
        method: 'post',
        credentials: 'same-origin',
        body: formData
    }).then(function (response) {
        return response.text();
    }).then(function (data) {
        console.log(data);
        element.classList.toggle("liked");
    });
}