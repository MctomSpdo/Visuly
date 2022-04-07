let element = document.getElementById("user-posts");
let userId = document.body.id;
let api_newPosts = `./API/user/get-post.php?user=${userId}`;

loadPosts(element, api_newPosts);