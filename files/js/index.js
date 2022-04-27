let api_home = "./API/feed/home.php";
let contentBox = document.getElementById('content');

loadHome(contentBox);



//add eventlistener to load more content:
document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('scroll', (e) => {
        let currentScroll = document.body.scrollTop + window.innerHeight;
        let documentHeight = document.body.scrollHeight;

        let modifier = 200;
        if(currentScroll + modifier > documentHeight) {
            loadNewPosts();
        }
    });
});

let postsAreLoading = false;
let offset = 50;

function loadNewPosts() {
    if(postsAreLoading) {
        return;
    }

    postsAreLoading = true;
    console.log("loading new posts...");

    fetch(api_home + `?offset=${offset}`, {
        credentials: 'same-origin',
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        console.log(data);

        if(data.posts == "no posts yet") {//reached end of page
            return;
        }

        data.posts.forEach((postElement) => {
            contentBox.innerHTML += parsePostToHTML(postElement);
        });
        offset += 50;
        postsAreLoading = false;
    });
}