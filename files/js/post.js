let commentTextArea = document.getElementById("comment-textarea");
let commentButton = document.getElementById("comment-button");
let cancelButton = document.getElementById("cancel-button");
let commentArea = document.getElementsByClassName("comment-content-wrapper")[0];
let api_comment = "./API/post/comment.php";

//textarea: 

commentTextArea.addEventListener("keyup", () => {
    commentTextArea.style.height = calcHeight(commentTextArea.value) + "px";
    if(commentTextArea.value.length > 0) {
        commentButton.style.backgroundColor = "var(--colorB)";
        commentButton.classList.add("button-hoverable");
    } else {
        commentButton.style.backgroundColor = "";
        commentButton.classList.remove("button-hoverable");
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
    if(commentTextArea.value.length > 0) {
        let comment = commentTextArea.value;
        console.log(comment);

        //disable elements: 
        setDisabledState(true);
        //get post ID:
        let element = getParents(commentTextArea, 7);
        sendComment(element.id, comment);
    }
}); 

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
    
    if(value) {
        commentButton.classList.remove("button-hoverable");
    } else {
        commentButton.classList.add("button-hoverable");
    }
}

function getParents(element, iterations) {
    let parentElement = element;
    for(let i = 0; i < iterations; i++) {
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
    }).then (function (response) {
        return response.json();
    }).then (function (data) {
        commentTextArea.value = "";
        setDisabledState(false);
        commentButton.classList.remove("button-hoverable");
        console.log(data);
    });
}