// Utility: Save and retrieve users from localStorage
function getUsers() {
    return JSON.parse(localStorage.getItem("users")) || [];
}

function saveUsers(users) {
    localStorage.setItem("users", JSON.stringify(users));
}

// Registration Logic
function registerUser(event) {
    event.preventDefault();

    const name = document.getElementById("regName").value.trim();
    const email = document.getElementById("regEmail").value.trim();
    const password = document.getElementById("regPassword").value;

    const users = getUsers();

    // Check if email already exists
    if (users.some(user => user.email === email)) {
        alert("Email is already registered.");
        return;
    }

    // Save new user
    users.push({ name, email, password });
    saveUsers(users);

    alert("Registration successful! Please log in.");
    window.location.href = "login.html"; // Change path if needed
}

// Login Logic
function loginUser(event) {
    event.preventDefault();

    const email = document.getElementById("loginEmail").value.trim();
    const password = document.getElementById("loginPassword").value;

    const users = getUsers();
    const matchedUser = users.find(user => user.email === email && user.password === password);

    if (matchedUser) {
        localStorage.setItem("isLoggedIn", "true");
        localStorage.setItem("loggedInUser", JSON.stringify(matchedUser));
        alert("Login successful!");
        window.location.href = "Courses.html"; // Redirect after login
    } else {
        alert("Invalid email or password.");
    }
}

// Logout Function (Optional)
function logoutUser() {
    localStorage.removeItem("isLoggedIn");
    localStorage.removeItem("loggedInUser");
    alert("Logged out successfully.");
    window.location.href = "login.html";
}

// Check login status anywhere
function isLoggedIn() {
    return localStorage.getItem("isLoggedIn") === "true";
}
