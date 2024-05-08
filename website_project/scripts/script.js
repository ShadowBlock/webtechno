// If create task/folder button is pressed, show the form
function showPopup() {
    var popup = document.getElementById("popup");
    if (popup.style.display == "block") {
        popup.style.display = "none";
    } else {
    popup.style.display = "block";
    }
}

// If close button is pressed, hide the form
function hidePopup() {
    document.getElementById("popup").style.display = "none";
}

// If gear icon is pressed, show the settings
function showSettings(folderId) {
    var folderSettings = document.getElementById("settings_"+folderId);
    if (folderSettings.style.display == "block") {
        folderSettings.style.display = "none";
    } else {
        folderSettings.style.display = "block";
    }
}

// If close button is pressed, hide the settings
function hideSettings(folderId) {
    document.getElementById("settings_"+folderId).style.display = "none";
}