function showDelete(){
    var prompt = document.getElementById("delete-profile-prompt");
    prompt.style.display = "block";
}

function hideDelete() {
    var cancel = document.getElementById("delete-profile-prompt");
    cancel.style.display = "none";
}

// Adds border to picture
function addBorder(profilePictureId) {
    var selector = '#profile-pic-' + profilePictureId;
    var element = document.querySelector(selector);

    var prevSelected = document.querySelector('.selected-profile-img');
    if (prevSelected) {
        prevSelected.classList.remove('selected-profile-img');
    }

    if (element) {
        element.classList.add('selected-profile-img');
    }
}

// Function to set the profile ID and submit the update profile form
function setProfileId(profilePictureId) {
    const numberList = [1, 2, 3, 4, 5, 6, 7, 8];
    if (!numberList.includes(profilePictureId)) {
        alert("Please select a valid picture");
    } else {
        document.getElementById('selected-profile-id').value = profilePictureId;
        addBorder(profilePictureId);
    }
}

// Confirms the selection of profile pictures
function confirmInput() {
    var form = document.getElementById('update-profile-form');
    if (form) {
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                window.location.reload();
            } else {
                console.error('Form submission failed with status:', xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Network error occurred while submitting the form.');
        };
        xhr.send(formData);
    }
}
