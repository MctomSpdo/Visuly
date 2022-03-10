let api_like = "./API/post/like.php"

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