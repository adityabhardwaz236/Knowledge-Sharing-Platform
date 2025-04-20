<?php
require_once 'config.php';
// No need to call session_start() as it's already called in config.php

if (!isLoggedIn()) {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied. Please log in.";
    exit();
}

$profileImage = 'IMG/default-profile.png';
$userName = 'Profile';

// Try to get user data if available
try {
    $conn = getDBConnection();
    if ($conn) {
        $stmt = $conn->prepare("SELECT name, profile_image FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $userName = htmlspecialchars($row['name']);
                if (!empty($row['profile_image'])) {
                    $profileImage = htmlspecialchars($row['profile_image']);
                }
            }
            $stmt->close();
        }
        $conn->close();
    }
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container">
        <a class="navbar-brand" href="Home.html">
            <img src="IMG/logo1.jpg" alt="eLearning Logo" height="40"> NPS eLearning
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="Home.html">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Courses.html">Courses</a>
                </li>
                <!-- Challenges Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="challengesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Challenges
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="challengesDropdown">
                        <li>
                            <a class="dropdown-item" href="english.html">
                                <i class="bi bi-book me-2"></i> English Challenge
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="math.html">
                                <i class="bi bi-calculator me-2"></i> Mathematics Challenge
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="science.html">
                            <i class="bi bi-book me-2"></i> Science Challenge
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Leaderboard.html">Leaderboard</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="AboutUs.html">About Us</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <div class="dropdown me-3">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="image.png" alt="Profile" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                        <span class="text-white"><?php echo $userName; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item" href="profile-settings.php"><i class="bi bi-gear-fill me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
/* Dropdown submenu styles */
.dropdown-submenu {
    position: relative;
}

.dropdown-submenu .dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -1px;
}

.dropdown-submenu .dropdown-toggle::after {
    display: inline-block;
    margin-left: .255em;
    vertical-align: .255em;
    content: "";
    border-top: .3em solid transparent;
    border-right: 0;
    border-bottom: .3em solid transparent;
    border-left: .3em solid;
}

/* Show dropdowns on hover */
.dropdown:hover > .dropdown-menu {
    display: block;
}

.dropdown-submenu:hover > .dropdown-menu {
    display: block;
}

/* Dropdown styling */
.dropdown-menu {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.dropdown-item {
    padding: .5rem 1rem;
    color: #333;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    color: #2940D3;
}

.dropdown-item i {
    width: 20px;
}
</style>

<script>
// Initialize dropdowns
document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown hover on desktop
    if (window.innerWidth > 992) {
        document.querySelectorAll('.dropdown-submenu').forEach(function(element) {
            element.addEventListener('mouseover', function() {
                this.querySelector('.dropdown-menu').classList.add('show');
            });
            element.addEventListener('mouseout', function() {
                this.querySelector('.dropdown-menu').classList.remove('show');
            });
        });
    }
});
</script>