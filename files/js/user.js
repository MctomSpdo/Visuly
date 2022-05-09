let element = document.getElementById("user-posts");
let userId = document.body.id;
let api_newPosts = `./API/user/get-post.php?user=${userId}`;
let api_follow = './API/user/follow.php'

let followButton = document.getElementById("user-follow");
let editButton = document.getElementById("user-edit");

let params = parseURLParams(window.location.href);

loadPosts(element, api_newPosts);

initUser();
function initUser() {

    console.log("test");
    if(followButton !== null) {
        //EventListener for following a user:
        followButton.addEventListener('click', () => {
            followButton.disabled = true;
            let uuid = params.user[0];
            let follow = followButton.innerHTML == "Follow";

            fetch(`${api_follow}?user=${uuid}&follow=${follow}` , {
                credentials: 'same-origin',
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                console.log(data);

                if(data.success == true) {
                    if(followButton.innerText == "Follow") {
                        followButton.innerHTML = "Unfollow";
                    } else {
                        followButton.innerText = "Follow";
                    }
                    followButton.disabled = false;
                } else {
                    alert(data.error);
                }
            });
        });
    }

    if(editButton !== null) {
        editButton.addEventListener('click', () => {
           window.location.href = "./settings.php";
        });
    }
}

function parseURLParams(url) {
    var queryStart = url.indexOf("?") + 1,
        queryEnd   = url.indexOf("#") + 1 || url.length + 1,
        query = url.slice(queryStart, queryEnd - 1),
        pairs = query.replace(/\+/g, " ").split("&"),
        parms = {}, i, n, v, nv;

    if (query === url || query === "") return;

    for (i = 0; i < pairs.length; i++) {
        nv = pairs[i].split("=", 2);
        n = decodeURIComponent(nv[0]);
        v = decodeURIComponent(nv[1]);

        if (!parms.hasOwnProperty(n)) parms[n] = [];
        parms[n].push(nv.length === 2 ? v : null);
    }
    return parms;
}