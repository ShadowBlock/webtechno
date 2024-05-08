function showError() {
    // Check if there's an error query parameter in the URL
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');

    // Get the error message element
    const errorMessage = document.getElementById('error-message');

    // Display different error messages based on the error code
    switch (error) {
        case '1':
            document.getElementById("error-popup").style.display = "block";
            errorMessage.textContent = "Error: Please fill out all fields.";
            break;
        case '2':
            document.getElementById("error-popup").style.display = "block";
            errorMessage.textContent = "Error: User does not exist.";
            break;
        case '3':
            document.getElementById("error-popup").style.display = "block";
            errorMessage.textContent = "Error: Invalid status.";
            break;
        case '4':
            document.getElementById("error-popup").style.display = "block";
            errorMessage.textContent = "Error: Invalid date.";
            break;
        case '5':
            document.getElementById("error-popup").style.display = "block";
            errorMessage.textContent = "Error: Please fill out at least one field.";
            break;
        case '6':
            document.getElementById("error-popup").style.display = "block";
            errorMessage.textContent = "Error: Title is over 30 characters.";
            break;
        case '7':
            document.getElementById("error-popup").style.display = "block";
            errorMessage.textContent = "Error: Description is over 150 characters.";
            break;
        default:
            break;
    }
}

// If close button is pressed, hide the error
function hideError() {
    var popup = document.getElementById("error-popup");
    popup.style.display = "none";
}

window.onload = showError();