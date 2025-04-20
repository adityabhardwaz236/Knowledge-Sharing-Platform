<?php
require_once 'config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to calculate user level based on achievements and progress
function calculateLevel($achievements, $progress_data) {
    // Base level is 1
    $level = 1;
    
    // Add levels based on number of achievements
    $level += min(count($achievements), 5); // Max 5 levels from achievements
    
    // Add levels based on total progress
    $total_progress = 0;
    foreach ($progress_data as $progress) {
        $total_progress += $progress['progress_percentage'];
    }
    
    // Every 100% progress in courses adds a level (max 10 levels from progress)
    $level += min(floor($total_progress / 100), 10);
    
    return $level;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    // Get database connection
    $conn = getDBConnection();
    
    // Create tables if they don't exist
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            profile_picture VARCHAR(255) DEFAULT NULL,
            user_type ENUM('student', 'teacher', 'admin') DEFAULT 'student',
            phone VARCHAR(20) DEFAULT NULL,
            bio TEXT DEFAULT NULL,
            last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS user_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            course_id INT,
            progress_percentage INT DEFAULT 0,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )",
        "CREATE TABLE IF NOT EXISTS achievements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            title VARCHAR(100) NOT NULL,
            description TEXT,
            date_earned TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )"
    ];

    foreach ($tables as $sql) {
        if (!$conn->query($sql)) {
            throw new Exception("Error creating table: " . $conn->error);
        }
    }

    // Get user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception("User not found");
    }

    // Set default profile image if none exists
    if (empty($user['profile_picture']) || !file_exists($user['profile_picture'])) {
        $user['profile_picture'] = 'IMG/default-profile.jpg';
    }

    // Fake progress data
    $progress_data = [
        ['course_name' => 'Math for Beginners', 'progress_percentage' => 75],
        ['course_name' => 'Science Fundamentals', 'progress_percentage' => 50],
        ['course_name' => 'English Grammar', 'progress_percentage' => 25]
    ];

    // Fake achievements data
    $achievements = [
        [
            'title' => 'Completed First Lesson',
            'description' => 'You finished your first lesson in a course!',
            'date_earned' => '2025-04-15 10:00:00'
        ],
        [
            'title' => 'Mastered Quiz',
            'description' => 'Scored 100% on a course quiz.',
            'date_earned' => '2025-04-10 14:30:00'
        ],
        [
            'title' => 'Course Explorer',
            'description' => 'Enrolled in three different courses.',
            'date_earned' => '2025-04-05 09:15:00'
        ]
    ];

    // Calculate user level with fake data
    $user_level = calculateLevel($achievements, $progress_data);

} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
} finally {
    // Close statement and connection
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NPS eLearning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Dashboard.css" rel="stylesheet">
    <style>
        .level-badge {
            background-color: #ffd700;
            color: #000;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
        }
        .achievement-item {
            border-left: 4px solid #2940D3;
            padding-left: 15px;
            margin-bottom: 20px;
        }
        .progress {
            height: 20px;
            border-radius: 10px;
        }
        .progress-bar {
            background-color: #2940D3;
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 15px;
        }
        .card-header {
            background-color: transparent;
            border-bottom: 2px solid #f0f0f0;
            padding: 20px;
        }
        .profile-card {
            text-align: center;
            padding: 20px;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 20px;
            border: 4px solid #2940D3;
            background-color: #f8f9fa;
        }
        .btn-action {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1.05em;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div id="navbar-placeholder"></div>

    <div class="container mt-5 pt-5">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-3">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
            </div>
        </div>

        <div class="row">
            <!-- Profile Section -->
            <div class="col-md-4 mb-4">
                <div class="card profile-card">
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                         class="profile-image mx-auto" 
                         alt="Profile Picture"
                         onerror="this.onerror=null; this.src='IMG/default-profile.jpg'">
                    <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="level-badge">
                        Level <?php echo $user_level; ?>
                    </div>
                    <a href="profile-settings.php" class="btn btn-primary btn-action">
                        <i class="bi bi-person-circle me-2"></i>Profile
                    </a>
                </div>
            </div>

            <!-- Progress and Achievements Section -->
            <div class="col-md-8">
                <!-- Course Progress -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Course Progress</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($progress_data)): ?>
                            <p class="text-muted">No courses in progress. Start learning today!</p>
                        <?php else: ?>
                            <?php foreach ($progress_data as $progress): ?>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($progress['course_name']); ?></h6>
                                        <span class="text-muted"><?php echo $progress['progress_percentage']; ?>%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo $progress['progress_percentage']; ?>%" 
                                             aria-valuenow="<?php echo $progress['progress_percentage']; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Achievements -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Achievements</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($achievements)): ?>
                            <p class="text-muted">Complete courses to earn achievements!</p>
                        <?php else: ?>
                            <?php foreach ($achievements as $achievement): ?>
                                <div class="achievement-item">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($achievement['title']); ?></h6>
                                    <p class="text-muted mb-1"><?php echo htmlspecialchars($achievement['description']); ?></p>
                                    <small class="text-muted">Earned on: <?php echo date('F j, Y', strtotime($achievement['date_earned'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <i class="bi bi-book display-4 text-primary mb-3"></i>
                        <h5 class="card-title">English Challenge</h5>
                        <p class="card-text">Test your English language skills</p>
                        <a href="english.html" class="btn btn-outline-primary">Start Challenge</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <i class="bi bi-calculator display-4 text-primary mb-3"></i>
                        <h5 class="card-title">Mathematics Challenge</h5>
                        <p class="card-text">Solve mathematical problems</p>
                        <a href="math.html" class="btn btn-outline-primary">Start Challenge</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <i class="bi bi-flask display-4 text-primary mb-3"></i>
                        <h5 class="card-title">Science Challenge</h5>
                        <p class="card-text">Explore scientific concepts</p>
                        <a href="science.html" class="btn btn-outline-primary">Start Challenge</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Navbar Loading Script -->
    <script>
        $(function(){
            $.get("check_auth.php")
                .done(function(isLoggedIn) {
                    const navbarFile = isLoggedIn === "true" ? "navbar-loggedin.php" : "navbar-guest.php";
                    $("#navbar-placeholder").load(navbarFile);
                })
                .fail(function() {
                    $("#navbar-placeholder").load("navbar-guest.php");
                });
        });
    </script>
</body>
</html>