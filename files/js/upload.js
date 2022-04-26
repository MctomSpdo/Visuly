let inputBoxHoverClass = "upload-filehover";

let api_creatPost = "./API/post/create-post.php";

//drag and drop API: https://www.w3schools.com/html/html5_draganddrop.asp

//elements:
let uploadForm = document.getElementById("upload");
let uploadBox = document.getElementById("upload-main");

let inputFileBox = document.getElementById("upload-file");
let previewFileBox = document.getElementById("file-display");
let inputFile = document.getElementById("file");
let inputFileInnerBox = document.getElementById("file-upload");

let inputTitle = document.getElementById("upload-title");
let inputDesc = document.getElementById("upload-description");

let inputCategory = document.getElementById("upload-category");
let inputCategoryList = document.getElementById("upload-category-list");

let inputLocation = document.getElementById("upload-location");
let inputLocationList = document.getElementById("upload-location-list");

let outputError = document.getElementById("upload-error");

let submitButton = document.getElementById("submit");

let image = null;

/************************************************ EVENTLISTENER ************************************************/

//file:
inputFileBox.addEventListener("dragenter", dragEnter);
inputFileBox.addEventListener("dragleave", dragLeave);
inputFileInnerBox.addEventListener("dragenter", dragEnter);
inputFileInnerBox.addEventListener("dragleave", dragLeave);

inputFile.addEventListener("change", () => {
    let fileList = inputFile.files;

    if(fileList.length == 0) {
        return;
    }
    loadFile(fileList[0]);
});


uploadForm.addEventListener('submit', (event) => {
    event.preventDefault();
    upload();
});

//allow pasting an image from the clipboards
window.addEventListener('paste', e => {
    let file = e.clipboardData.files[0];
    console.log(file);
    loadFile(file);
});

function dragEnter() {
    inputFileBox.classList.add(inputBoxHoverClass);
}

function dragLeave() {
    inputFileBox.classList.remove(inputBoxHoverClass);
}

//inputs: 

document.querySelectorAll('input').forEach(input => {
    input.addEventListener('keyup', check);
    input.addEventListener('keypress', check);
});

/**
 * Checks all the input fields
 */
function check() {
    let title = inputTitle.value;
    let desc = inputDesc.value;
    
    if(title != "" && title.length > 30) {
        error("Title can't be over 30 characters long!");
        return true;
    } else if(desc != "" && desc.length > 2000) {
        error("Description can't be over 2000 Characters long!");
        return true;
    } else if(title == "") {
        error("The post must have a title!");
        return true;
    }
    error("");
    return false;
}

/**
 * Call this function to upload everything, and redirect
 */
function upload() {
    //check user inputs:
    if(check()) {
        return;
    } else if (image === null) {
        error("Select an Image first");
        return;
    }
    //disable button:
    submitButton.disabled = true;

    let formData = new FormData();

    formData.append('title', inputTitle.value);
    formData.append('desc', inputDesc.value);
    formData.append('category', inputCategory.value);
    formData.append('location', inputLocation.value);
    formData.append('image', image);

    fetch(api_creatPost, {
        method: 'post',
        credentials: 'same-origin',
        body: formData
    }).then(function (response) {
        return response.json();
    }).then(function (data) { 
        console.log(data);
        if(data.postid === undefined || data.postid === null) {
            if(data.error == 'File is too big') {
                alert("Your File is too big for the platform to handle!");
            } else {
                alert("Something went wrong during your upload, try again later");
                window.location.replace("./error.html");
                console.log(data);
            }
        } else {
            uploadBox.innerHTML = "Redirecting....";
            window.location.replace(`./post.php?post=${data.postid}`);
        }
    });
}

/**
 * receives dropped file
 * @param {Event} ev Event
 */
function drop(ev) {
    ev.preventDefault();
    let file = "";

    if (ev.dataTransfer.items) {
        file = ev.dataTransfer.items[0].getAsFile();
    } else {
        file = ev.dataTransfer.files[0];
    }

    loadFile(file);
    inputFileBox.classList.remove(inputBoxHoverClass);
}

/**
 * Drag over event handler for file-input
 * @param {Event} ev Event
 */
function dragOverHandler(ev) {
    ev.preventDefault();
    inputFileBox.classList.add(inputBoxHoverClass);
}

/**
 * Loads the file after selecting
 * @param {File} file 
 * @returns nothing
 */
function loadFile(file) {
    if (!fileIsImage(file)) {
        error("File is not an image!")
        return;
    }
    image = file;

    //https://stackoverflow.com/questions/20403778/display-image-after-drag-and-drop-on-html-page
    let fd = new FormData();
    fd.append('file', file);

    let img = document.getElementById("img-prev");
    img.classList.add("obj");
    img.file = file;

    let reader = new FileReader();
    reader.onload = (function (aImg) { return function (e) { aImg.src = e.target.result; }; })(img);
    reader.readAsDataURL(file);

    inputFileInnerBox.style.display = "none";
    previewFileBox.style.display = "flex";
    inputFileBox.style.backgroundColor = "rgba(0, 0, 0, 0)"
    inputFileBox.style.boxShadow = "none";
}

/**
 * checks if a given File is an Image
 * @param {File} file File to check 
 * @returns true if image, false otherwise
 */
function fileIsImage(file) {
    const acceptedImageTypes = ['image/gif', 'image/jpeg', 'image/png'];

    return file && acceptedImageTypes.includes(file['type'])
}

/**
 * Displays an error on the page
 * @param {String} text 
 */
function error(text) {
    outputError.innerHTML = text;
}