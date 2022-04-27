let commentTextArea = document.getElementById("comment-textarea");
let commentButton = document.getElementById("comment-button");
let cancelButton = document.getElementById("cancel-button");
let commentArea = document.getElementsByClassName("comment-content-wrapper")[0];
let commentWrapper = document.getElementsByClassName("comment-wrapper")[0];
let api_comment = "./API/post/comment.php";
let api_get_comment = "./API/post/get-comment.php";

//textarea: 

commentTextArea.addEventListener("keyup", (e) => {
    commentTextArea.style.height = calcHeight(commentTextArea.value) + "px";
    if (commentTextArea.value.length > 0) {
        commentButton.style.backgroundColor = "var(--colorB)";
        commentButton.classList.add("button-hoverable");
        cancelButton.classList.add("cancel-hoverable");
    } else {
        commentButton.style.backgroundColor = "";
        commentButton.classList.remove("button-hoverable");
        cancelButton.classList.remove("cancel-hoverable");
    }

    if(e.keyCode == 13 && e.ctrlKey) {
        checkSendComment();
    }
})

function calcHeight(value) {
    let numberOfLineBreaks = (value.match(/\n/g) || []).length;
    // min-height + lines x line-height + padding + border
    return 20 + numberOfLineBreaks * 20 + 22 + 2;
}

//buttons: 

commentButton.addEventListener("click", (event) => {
    event.preventDefault();
    if (commentTextArea.value.length > 0) {
        checkSendComment();
    }
});

function checkSendComment() {
    let comment = commentTextArea.value;

    //disable elements:
    setDisabledState(true);
    //get post ID:
    let element = getParents(commentTextArea, 7);
    sendComment(element.id, comment);
}

cancelButton.addEventListener("click", (event) => {
    event.preventDefault();
    commentTextArea.value = "";
    commentButton.style.backgroundColor = "";
    commentButton.classList.remove("button-hoverable");
})

function setDisabledState(value) {
    commentButton.disabled = value;
    cancelButton.disabled = value;
    commentTextArea.disabled = value;

    if (value) {
        commentButton.classList.remove("button-hoverable");
        cancelButton.classList.remove("cancel-hoverable");
    }
}

function getParents(element, iterations) {
    let parentElement = element;
    for (let i = 0; i < iterations; i++) {
        parentElement = parentElement.parentNode;
    }
    return parentElement;
}

function sendComment(post, comment) {
    let formData = new FormData();

    formData.append('post', post)
    formData.append('comment', comment);

    fetch(api_comment, {
        method: 'post',
        credentials: 'same-origin',
        body: formData
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        commentTextArea.value = "";
        setDisabledState(false);
        commentButton.classList.add("button-hoverable");
        commentButton.style.backgroundColor = "";
        if(data.error !== undefined) {
            alert("sorry, something went wrong during commenting: " + data.error);
        }
        loadComments(post, 0, commentWrapper);
    });
}

function loadComments(post, offset, element) {
    let formData = new FormData();

    formData.append('post', post);
    formData.append('offset', offset);

    fetch(api_get_comment, {
        method: 'post',
        credentials: 'same-origin',
        body: formData
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        console.log(data)
        element.innerHTML = "";
        data.forEach((item, index) => {
            element.innerHTML += generateComment(item);
        });
    });
}

function generateComment(comment) {
    let textArr = comment.content.split(/\r?\n/);
    let commentText = "";

    textArr.forEach((item, index) => {
       commentText += "<p>" + item + "</p>";
    });

    return `<div class="comment">
                <div class="comment-user-wrapper">
                    <img src="files/img/users/${comment.userImage}" alt="${comment.user}">
                </div>
                <div class="comment-content-wrapper">
                    <a href="./user.php?user=${comment.userId}">${comment.user}</a>
                    ${commentText}
                </div>
                <div></div>
            </div>`
}