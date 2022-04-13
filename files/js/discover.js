let API_TOP_POSTS = './API/feed/top-posts.php';


loadNewest(document.getElementById('content'));

function loadPopular(offset) {
    let data = new FormData();

    data.append('offset', offset);

    fetch(API_TOP_POSTS, {
        method: 'post',
        credentials: 'same-origin',
        body: data
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        console.log(data);
    });
}