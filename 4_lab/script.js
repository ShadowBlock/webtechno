var loggedIn = false;

// Checks the name for the cookie and loads data if there is anything in localStorage
function checkName() {
    var nameStored = getCookie("nameStored") || sessionStorage.getItem("nameStored");

    // If there is no storedname, then display that person is not logged in
    if (nameStored === null) {
        document.getElementById("nameDisplay").textContent = "You are not logged in.";
        document.getElementById("errorDisplay").textContent = "No name given.";
    }

    // Check if there is a name in the cookie or sessionStorage.
    if (nameStored) {
        console.log("Name found.");

        // Reset error text
        document.getElementById("errorDisplay").textContent = "";
        loggedIn = true;

        makeTable();
        eventListen();
        displayName();
    // If there is no name in the session, then ask for the name
    } else {
        var name = prompt("Please enter your name:");
        var sanitizedName = sanitizeInput(name);
        /* If the prompt is given - store it in cookie and sessionStorage, 
        then start the logging in again to load the data if there is some */
        if (sanitizedName) {
            if (validateName(sanitizedName)) {
                document.getElementById("errorDisplay").textContent = validateName(sanitizedName);
            } else {
                document.cookie = "nameStored=" + sanitizedName + ";";
                sessionStorage.setItem("nameStored", sanitizedName);
                console.log("Name stored: " + sanitizedName);
                checkName();
            }
        }
    }
}

// Make sure name is not empty and does not contain only whitespaces
function validateName(name) {
    if (name === null) {
        return "No name provided.";
    } else if (name.trim() === "") {
        return "Name cannot contain only whitespace.";
    } else {
        return false;
    }
}

// Delete table after logout
function deleteTable() {
    var table = document.getElementById("growing-table");
    // Clear rows that are shown
    table.innerHTML = '';

    // Rows for header, made sure they are th elements not td
    var thead = document.createElement("thead");
    var headerRow = document.createElement("tr");
    var headerTh1 = document.createElement("th");
    var headerTh2 = document.createElement("th");
    var headerTh3 = document.createElement("th");
    headerTh1.textContent = "No.";
    headerTh2.textContent = "Product";
    headerTh3.textContent = "Quantity";
    headerRow.appendChild(headerTh1);
    headerRow.appendChild(headerTh2);
    headerRow.appendChild(headerTh3);
    thead.appendChild(headerRow);
    table.appendChild(thead);
}

// Create and display the table based on stored data in localStorage
function makeTable() {
    var nameStored = getCookie("nameStored") || sessionStorage.getItem("nameStored");
    var storedData = JSON.parse(localStorage.getItem(nameStored)) || [];

    var table = document.getElementById("growing-table");
    
    deleteTable();

    var tbody = document.createElement("tbody");
    table.appendChild(tbody);

    // Generate all rows according to localStorage data
    for (var i = 0; i < storedData.length; i++) {
        var rowData = storedData[i];
        var row = document.createElement("tr");
        var cell1 = document.createElement("td"); 
        var cell2 = document.createElement("td");
        var cell3 = document.createElement("td");
        cell1.textContent = (i + 1) + ".";
        cell2.textContent = rowData.product;
        cell3.textContent = rowData.quantity;
        row.appendChild(cell1);
        row.appendChild(cell2);
        row.appendChild(cell3);
        tbody.appendChild(row);
    }
}

// Get cookie value by name of cookie
function getCookie(name) {
    var cookies = document.cookie.split("; ");
    for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i].split("=");
        if (cookie[0] === name) {
            return cookie[1];
        }
    }
    return null;
}

// Display the name on top of page, if error then say log in and show right button
function displayName() {
    var nameStored = getCookie("nameStored") || sessionStorage.getItem("nameStored");
    if (nameStored == null) {
        var text = "You are not logged in.";
        document.getElementById("nameDisplay").textContent = text;
        var button = document.getElementById('log');
        button.innerText = "Log in";
    } else {
        var text = nameStored + "'s Shopping List";
        document.getElementById("nameDisplay").textContent = text;
        var button = document.getElementById('log');
        button.innerText = "Log out";
    }
}

/*
Validating product input, it cannot contain anything else than letters, numbers, spaces and
cannot be over 100 chars
*/
function validateProduct(productName) {
    if (!productName.trim()) {
        return "Product input is empty.";
    }
    if (!/^[a-zA-Z0-9][a-zA-Z0-9 %\-]*$/.test(productName)) {
        return "Product name can only contain letters, numbers, percentage, hyphen, spaces. Also must start with letter or number.";
    }
    if (productName.length > 100) {
        return "Product name is too long.";
    }
    return false;
}

/* Parse the input as an int and it will check if it is positive, 
give out error if it is not a positive int */
function validateQuantity(quantity) {
    // Check if quantity is empty
    if (!quantity.trim()) {
        return "Quantity input is empty.";
    }
    // Check if quantity is a positive integer
    if (/^\d+$/.test(quantity) && parseInt(quantity) > 0 && parseInt(quantity) < 1000000) {
        return false;
    }
    // Return an error message if not working
    return "Quantity must be only a positive integer. Not larger than 1000000.";
}

// Remove all html tags so you cannot insert code
function sanitizeInput(input) {
    const sanitizedInput = input.replace(/<[^>]*>?/gm, '');
    return sanitizedInput;
}

// Pressing logout button deletes name from session and "reloads" page
function logout() {
    // This is to reset the error message.
    document.getElementById("errorDisplay").textContent = "";

    loggedIn = false;

    sessionStorage.removeItem("nameStored");
    document.cookie = "nameStored=; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
    displayName();
    deleteTable();
    checkName();
    eventListen();
}

// When form "Add to list" button pressed
function submitPressed() {
    var nameStored = getCookie("nameStored") || sessionStorage.getItem("nameStored");
    console.log("submit pressed");

    // Check if user is logged in
    if (loggedIn == false || nameStored == null) {
        document.getElementById("errorDisplay").textContent = "Logged out users cannot add products.";
    } else {
        var product = sanitizeInput(document.getElementById('product').value);
        var quantity = sanitizeInput(document.getElementById('quantity').value);

        // Check the validity of inputs
        var valProduct = validateProduct(product);
        var valQuantity = validateQuantity(quantity);
        document.getElementById("errorDisplay").textContent = "";

        // Display error if input not valid
        if (valProduct || valQuantity) {
            if (valProduct) {
                document.getElementById("errorDisplay").textContent = valProduct;
                console.error("Error: ", valProduct);
            }
            if (valQuantity) {
                document.getElementById("errorDisplay").textContent = valQuantity;
                console.error("Error: ", valQuantity);
            }
        } else {
            // Reset error display
            document.getElementById("errorDisplay").textContent = "";
            // Store data in localStorage as JSON data
            var storedData = JSON.parse(localStorage.getItem(nameStored)) || [];
            storedData.push({ product: product, quantity: quantity });
            localStorage.setItem(nameStored, JSON.stringify(storedData));

            // Clear input fields after successful adding
            document.getElementById("product").value = "";
            document.getElementById("quantity").value = "";
            makeTable();
            eventListen();
        }
    }
}

// Add event listener to each row in the table
function eventListen() {
    var table = document.getElementById("growing-table");
    var rows = table.getElementsByTagName("tr");

    // Add event listener to each row starting from row 1 (excluding the header row)
    for (var i = 1; i < rows.length; i++) {
        rows[i].addEventListener("click", rowClick);
    }
}

// Detect Enter press in input fields and submit form then
function keyPress(event) {
    if (event.key === "Enter") {
        event.preventDefault();
        submitPressed();
    }
}

// If row is clicked, then remove row and data from localStorage
function rowClick() {
    var row = this;
    var rowIndex = row.rowIndex;
    var nameStored = getCookie("nameStored") || sessionStorage.getItem("nameStored");
    var product = row.cells[1].textContent;
    var quantity = row.cells[2].textContent;

    // Ask for confirmation
    var confirmDeletion = confirm("Are you sure you want to delete " + product + " with quantity " + quantity + "?");

    if (confirmDeletion) {
        // Remove product from localStorage
        var storedData = JSON.parse(localStorage.getItem(nameStored)) || [];
        storedData.splice(rowIndex - 1, 1);
        localStorage.setItem(nameStored, JSON.stringify(storedData));

        // Reload data so that table will be right as well
        makeTable();
        eventListen();
    }
}

window.onload = function() {
    checkName();
    eventListen();
}