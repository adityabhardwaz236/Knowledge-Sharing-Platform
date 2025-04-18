// index1.js (Optimized & Cleaned)

document.addEventListener('DOMContentLoaded', () => {
    console.log("index1.js loaded and DOM ready");

    // ===== Utility Functions =====
    function saveUser(user) {
        localStorage.setItem("user", JSON.stringify(user));
    }

    function getUser() {
        return JSON.parse(localStorage.getItem("user"));
    }

    function getUserData() {
        let userData = JSON.parse(localStorage.getItem("userData"));
        if (!userData) {
            userData = {
                level: 1,
                xp: 0,
                challengesCompleted: 0,
                levelProgress: 0,
                mathProgress: 0,
                scienceProgress: 0,
                englishProgress: 0
            };
            localStorage.setItem("userData", JSON.stringify(userData));
        }
        return userData;
    }

    function updateUserDataUI(userData) {
        const {
            level, xp, challengesCompleted,
            levelProgress, mathProgress,
            scienceProgress, englishProgress
        } = userData;

        const elements = {
            userLevel: level,
            userXP: xp,
            totalXP: xp,
            challengesCompleted,
            levelProgress,
            mathProgress,
            scienceProgress,
            englishProgress
        };

        Object.keys(elements).forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;

            if (id.includes('Progress')) {
                el.style.width = `${elements[id]}%`;
                el.textContent = `${elements[id]}%${id === 'levelProgress' ? ` to Level ${level + 1}` : ''}`;
            } else {
                el.textContent = elements[id];
            }
        });
    }

    // ===== User Auth Functions =====
    function registerUser(event) {
        event.preventDefault();
        const name = document.getElementById("registerName").value;
        const email = document.getElementById("registerEmail").value;
        const password = document.getElementById("registerPassword").value;

        if (!name || !email || !password) {
            alert("Please fill in all fields.");
            return;
        }

        saveUser({ name, email, password });
        alert("Account created! Please log in.");
        document.getElementById("login-tab").click();
    }

    function loginUser(event) {
        event.preventDefault();
    
        const email = document.getElementById("loginEmail").value;
        const password = document.getElementById("loginPassword").value;
    
        const storedUser = JSON.parse(localStorage.getItem("user"));
    
        if (storedUser && email === storedUser.email && password === storedUser.password) {
            // Login successful
            localStorage.setItem("isLoggedIn", "true");
            window.location.href = "Home.html";
            return;
        }
    
        // If login fails
        alert("Invalid email or password.");
    }
    
    

    // ===== Login Form Detection =====
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", loginUser);
    }

    // ===== Navbar and Footer Loader =====
    const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
    const navbarFile = isLoggedIn ? "navbar-loggedin.html" : "navbar-guest.html";

    fetch(navbarFile)
        .then(res => res.text())
        .then(nav => {
            document.getElementById("navbar-placeholder").innerHTML = nav;
            return fetch("footer.html");
        })
        .then(res => res.text())
        .then(footer => {
            document.getElementById("footer-placeholder").innerHTML = footer;

            const backToTopButton = document.getElementById('back-to-top');
            if (backToTopButton) {
                window.addEventListener('scroll', () => {
                    backToTopButton.style.display = window.scrollY > 300 ? 'block' : 'none';
                });
                backToTopButton.addEventListener('click', () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            const currentPage = window.location.pathname.split('/').pop().toLowerCase() || 'home.html';
            document.querySelectorAll('.nav-link, .dropdown-item').forEach(link => {
                const href = link.getAttribute('href')?.toLowerCase();
                if (href === currentPage) {
                    link.classList.add('active');
                    if (link.classList.contains('dropdown-item')) {
                        const parent = link.closest('.dropdown');
                        if (parent) parent.querySelector('.dropdown-toggle')?.classList.add('active');
                    }
                }
            });

            const searchInput = document.getElementById("searchInput");
            const searchSpinner = document.getElementById("searchSpinner");

            if (searchInput && searchSpinner) {
                searchInput.addEventListener("keyup", () => {
                    searchSpinner.classList.remove("d-none");
                    setTimeout(() => {
                        const filter = searchInput.value.toLowerCase();
                        document.querySelectorAll(".course-card").forEach(card => {
                            const title = card.querySelector(".card-title").innerText.toLowerCase();
                            card.style.display = title.includes(filter) ? "block" : "none";
                        });
                        searchSpinner.classList.add("d-none");
                    }, 500);
                });
            }

            const categoryFilter = document.getElementById("categoryFilter");
            const levelFilter = document.getElementById("levelFilter");
            const resetFilters = document.getElementById("resetFilters");
            const courseList = document.querySelectorAll(".course-card");

            if (categoryFilter && levelFilter && resetFilters) {
                function filterCourses() {
                    const cat = categoryFilter.value;
                    const lvl = levelFilter.value;
                    courseList.forEach(card => {
                        const category = card.getAttribute("data-category");
                        const level = card.getAttribute("data-level");
                        card.style.display = (cat === "all" || category === cat) && (lvl === "all" || level === lvl) ? "block" : "none";
                    });
                }
                categoryFilter.addEventListener("change", filterCourses);
                levelFilter.addEventListener("change", filterCourses);
                resetFilters.addEventListener("click", () => {
                    categoryFilter.value = "all";
                    levelFilter.value = "all";
                    filterCourses();
                });
            }

            // Update dashboard if elements exist
            const userData = getUserData();
            updateUserDataUI(userData);

            setTimeout(() => {
                userData.xp += 50;
                userData.levelProgress = Math.min(userData.levelProgress + 5, 100);
                userData.mathProgress = Math.min(userData.mathProgress + 5, 100);
                localStorage.setItem("userData", JSON.stringify(userData));
                updateUserDataUI(userData);
            }, 2000);
        })
        .catch(err => console.error("Navbar/Footer load error:", err));

    // ===== Attach to Register Form =====
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", registerUser);
    }
});
