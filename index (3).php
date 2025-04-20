<?php
session_start();

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'nps_elearning';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Debug image path
$image_path = __DIR__ . 'IMG/logo1.jpg';
if (file_exists($image_path)) {
    error_log("Image exists at: " . $image_path);
} else {
    error_log("Image not found at: " . $image_path);
}

// Initialize variables
$login_error = $register_error = $register_success = '';

// Handle Login
if (isset($_POST['login'])) {
    $email = filter_var($_POST['loginEmail'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['loginPassword'];

    if (empty($email) || empty($password)) {
        $login_error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                header("Location: dashboard.php"); // Redirect to a dashboard page
                exit();
            } else {
                $login_error = "Invalid email or password.";
            }
        } else {
            $login_error = "Invalid email or password.";
        }
        $stmt->close();
    }
}

// Handle Registration
if (isset($_POST['register'])) {
    $full_name = filter_var($_POST['registerName'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['registerEmail'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['registerPassword'];

    if (empty($full_name) || empty($email) || empty($password)) {
        $register_error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $register_error = "Password must be at least 6 characters.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $register_error = "Email already registered.";
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $full_name, $email, $hashed_password);

            if ($stmt->execute()) {
                $register_success = "Registration successful! Please sign in.";
            } else {
                $register_error = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>NPS eLearning - Login/Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-color: #0a2540;
      --accent-color: #00b4d8;
      --secondary-color: #6c757d;
    }

    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      font-family: 'Inter', sans-serif;
      padding-top: 80px;
      min-height: 100vh;
    }

    .navbar {
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
      background: var(--primary-color) !important;
      transition: all 0.3s ease;
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .navbar-brand img {
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s ease;
    }

    .navbar-brand:hover img {
      transform: scale(1.1);
    }

    .nav-link {
      position: relative;
      margin: 0 5px;
      padding: 8px 15px !important;
      transition: all 0.3s ease;
    }

    .nav-link:not(.btn)::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      left: 50%;
      background-color: var(--accent-color);
      transition: all 0.3s ease;
      transform: translateX(-50%);
    }

    .nav-link:not(.btn):hover::after {
      width: 80%;
    }

    .nav-link.btn {
      animation: pulse 2s infinite;
      transform-origin: center;
      box-shadow: 0 4px 15px rgba(0, 180, 216, 0.3);
      border: none;
    }

    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(0, 180, 216, 0.5); }
      70% { box-shadow: 0 0 0 10px rgba(0, 180, 216, 0); }
      100% { box-shadow: 0 0 0 0 rgba(0, 180, 216, 0); }
    }

    .auth-container {
      max-width: 1200px;
      margin: auto;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transform: translateY(0);
      transition: transform 0.5s ease;
      animation: containerReveal 1s ease forwards;
    }

    @keyframes containerReveal {
      0% { opacity: 0; transform: translateY(30px); }
      100% { opacity: 1; transform: translateY(0); }
    }

    .left-panel {
      background: linear-gradient(135deg, var(--primary-color) 0%, #1a365d 100%);
      color: white;
      padding: 40px;
      position: relative;
      overflow: hidden;
      animation: leftPanelReveal 1.2s ease forwards;
    }

    @keyframes leftPanelReveal {
      0% { opacity: 0; transform: translateX(-30px); }
      100% { opacity: 1; transform: translateX(0); }
    }

    .left-panel::before {
      content: '';
      position: absolute;
      top: -50px;
      left: -50px;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
    }

    .left-panel::after {
      content: '';
      position: absolute;
      bottom: -80px;
      right: -80px;
      width: 300px;
      height: 300px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
    }

    .logo-container {
      text-align: center;
      animation: fadeIn 1.5s ease;
    }

    .logo {
      background: var(--accent-color);
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
      box-shadow: 0 8px 25px rgba(0, 180, 216, 0.4);
      width: 80px;
      height: 80px;
      position: relative;
      overflow: hidden;
    }

    .logo::before {
      content: '';
      position: absolute;
      top: -10px;
      left: -10px;
      right: -10px;
      bottom: -10px;
      background: linear-gradient(45deg, transparent 40%, rgba(255, 255, 255, 0.8) 50%, transparent 60%);
      animation: shimmer 3s infinite linear;
    }

    @keyframes shimmer {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }

    .logo-img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      border-radius: 50%;
      transition: transform 0.8s ease;
      z-index: 1;
    }

    .logo:hover .logo-img {
      transform: rotate(360deg);
    }

    .logo-text {
      font-size: 1.8rem;
      font-weight: 700;
      color: white;
      letter-spacing: 1px;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      position: relative;
      display: inline-block;
    }

    .logo-text::after {
      content: '';
      position: absolute;
      width: 40%;
      height: 3px;
      bottom: -8px;
      left: 30%;
      background-color: var(--accent-color);
      border-radius: 5px;
    }

    .feature-item {
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px;
      border-radius: 12px;
      transition: all 0.3s ease;
      position: relative;
      z-index: 1;
      opacity: 0;
      transform: translateY(20px);
      animation: featureReveal 0.5s ease forwards;
    }

    .feature-item:nth-child(1) { animation-delay: 0.3s; }
    .feature-item:nth-child(2) { animation-delay: 0.6s; }
    .feature-item:nth-child(3) { animation-delay: 0.9s; }

    @keyframes featureReveal {
      0% { opacity: 0; transform: translateY(20px); }
      100% { opacity: 1; transform: translateY(0); }
    }

    .feature-item:hover {
      background: rgba(255, 255, 255, 0.1);
      transform: translateX(5px);
    }

    .feature-item i {
      font-size: 2rem;
      color: var(--accent-color);
      min-width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0, 180, 216, 0.1);
      border-radius: 50%;
      text-shadow: 0 2px 10px rgba(0, 180, 216, 0.3);
      transition: all 0.3s ease;
    }

    .feature-item:hover i {
      transform: scale(1.1);
      background: rgba(0, 180, 216, 0.2);
    }

    .feature-text {
      flex: 1;
    }

    .feature-text strong {
      display: block;
      color: var(--accent-color);
      margin-bottom: 5px;
      font-weight: 600;
      font-size: 1.1rem;
    }

    .form-panel {
      padding: 50px 40px;
      background: #ffffff;
      animation: formPanelReveal 1.2s ease forwards;
    }

    @keyframes formPanelReveal {
      0% { opacity: 0; transform: translateX(30px); }
      100% { opacity: 1; transform: translateX(0); }
    }

    .nav-tabs {
      border-bottom: 2px solid #dee2e6;
      margin-bottom: 30px;
    }

    .nav-tabs .nav-link {
      border: none;
      color: var(--secondary-color);
      font-weight: 500;
      padding: 12px 25px;
      position: relative;
      transition: all 0.3s ease;
    }

    .nav-tabs .nav-link::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 0;
      height: 3px;
      background: var(--accent-color);
      transition: all 0.3s ease;
      transform: translateX(-50%);
    }

    .nav-tabs .nav-link:hover::after {
      width: 30%;
    }

    .nav-tabs .nav-link.active {
      color: var(--primary-color);
      font-weight: 600;
    }

    .nav-tabs .nav-link.active::after {
      width: 80%;
    }

    .form-label {
      color: var(--primary-color);
      font-weight: 500;
      margin-bottom: 8px;
    }

    .input-group {
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 3px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      transform: translateY(0);
    }

    .input-group:focus-within {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0, 180, 216, 0.15);
    }

    .input-group-icon {
      background-color: #f8f9fa;
      border: 2px solid #e9ecef;
      border-right: 0;
      border-radius: 8px 0 0 8px;
      padding: 0 15px;
      display: flex;
      align-items: center;
    }

    .input-group-icon i {
      color: var(--accent-color);
      font-size: 1.2rem;
      transition: all 0.3s ease;
    }

    .input-group:focus-within .input-group-icon i {
      transform: scale(1.1);
    }

    .form-control {
      border: 2px solid #e9ecef;
      border-radius: 0 8px 8px 0;
      padding: 12px 15px;
      transition: all 0.3s ease;
      font-size: 0.95rem;
    }

    .form-control:focus {
      border-color: var(--accent-color);
      box-shadow: none;
    }

    .btn-custom {
      background: linear-gradient(to right, var(--accent-color), #0093b0);
      color: white;
      padding: 12px 25px;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      z-index: 1;
      box-shadow: 0 4px 15px rgba(0, 180, 216, 0.3);
    }

    .btn-custom::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: all 0.6s ease;
      z-index: -1;
    }

    .btn-custom:hover {
      background: linear-gradient(to right, #0093b0, #0082a0);
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 180, 216, 0.4);
    }

    .btn-custom:hover::before {
      left: 100%;
    }

    .tab-pane {
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.5s ease;
    }

    .tab-pane.show.active {
      opacity: 1;
      transform: translateY(0);
    }

    .error, .success {
      margin-bottom: 15px;
      padding: 10px;
      border-radius: 5px;
    }

    .error {
      background-color: #f8d7da;
      color: #721c24;
    }

    .success {
      background-color: #d4edda;
      color: #155724;
    }

    @media (max-width: 991px) {
      .navbar-collapse {
        background: var(--primary-color);
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-top: 10px;
      }
    }

    @media (max-width: 768px) {
      body {
        padding-top: 60px;
      }
      
      .auth-container {
        margin: 20px 15px;
        flex-direction: column;
      }

      .left-panel, .form-panel {
        width: 100%;
        max-width: 100%;
      }

      .left-panel {
        border-radius: 20px 20px 0 0;
        padding: 30px 20px;
      }

      .form-panel {
        padding: 30px 20px;
        border-radius: 0 0 20px 20px;
      }
      
      .feature-item {
        margin-bottom: 15px;
      }
    }

    @keyframes fadeIn {
      0% { opacity: 0; }
      100% { opacity: 1; }
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container">
      <a class="navbar-brand" href="Home.html">
        <img src="IMG/logo1.jpg" alt="eLearning Logo" height="40"> NPS eLearning
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="Home.html">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="Courses.php">Courses</a></li>
          <li class="nav-item"><a class="nav-link" href="AboutUs.php">About Us</a></li>
          <a class="nav-link btn btn-info text-light px-3 nav-item" href="index.php"><strong>Register/Login</strong></a>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Auth Container -->
  <div class="container auth-container d-flex mt-5">
    <!-- Left Panel -->
    <div class="col-md-6 left-panel d-flex flex-column">
      <div class="logo-container">
        <div class="logo">
          <img src="IMG/logo1.jpg" alt="NPS eLearning Logo" class="logo-img" width="40" height="40">
        </div>
        <div class="logo-text">NPS eLearning</div>
      </div>
      <div class="mt-auto">
        <div class="feature-item">
          <i class="bi bi-trophy"></i>
          <div class="feature-text">
            <strong>Earn XP & Achievements</strong>
            Climb leaderboards and showcase your skills
          </div>
        </div>
        <div class="feature-item">
          <i class="bi bi-play-btn"></i>
          <div class="feature-text">
            <strong>Interactive Video Courses</strong>
            Learn at your own pace with progress tracking
          </div>
        </div>
        <div class="feature-item">
          <i class="bi bi-puzzle"></i>
          <div class="feature-text">
            <strong>Daily Challenges</strong>
            Reinforce learning with engaging activities
          </div>
        </div>
      </div>
    </div>

    <!-- Right Panel -->
    <div class="col-md-6 form-panel">
      <!-- Tabs -->
      <ul class="nav nav-tabs" id="authTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">Sign In</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">Create Account</button>
        </li>
      </ul>

      <div class="tab-content mt-4" id="authTabContent">
        <!-- Login Form -->
        <div class="tab-pane fade show active" id="login" role="tabpanel">
          <form method="POST" action="index.php">
            <?php if (!empty($login_error)): ?>
              <div class="alert alert-danger"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <div class="mb-3">
              <label for="loginEmail" class="form-label">Email address</label>
              <input type="email" class="form-control" id="loginEmail" name="loginEmail" required>
            </div>
            <div class="mb-3">
              <label for="loginPassword" class="form-label">Password</label>
              <input type="password" class="form-control" id="loginPassword" name="loginPassword" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Sign In</button>
          </form>
        </div>

        <!-- Register Form -->
        <div class="tab-pane fade" id="register" role="tabpanel">
          <form method="POST" action="index.php">
            <?php if (!empty($register_error)): ?>
              <div class="alert alert-danger"><?php echo $register_error; ?></div>
            <?php endif; ?>
            <?php if (!empty($register_success)): ?>
              <div class="alert alert-success"><?php echo $register_success; ?></div>
            <?php endif; ?>
            <div class="mb-3">
              <label for="registerName" class="form-label">Full Name</label>
              <input type="text" class="form-control" id="registerName" name="registerName" required>
            </div>
            <div class="mb-3">
              <label for="registerEmail" class="form-label">Email address</label>
              <input type="email" class="form-control" id="registerEmail" name="registerEmail" required>
            </div>
            <div class="mb-3">
              <label for="registerPassword" class="form-label">Password</label>
              <input type="password" class="form-control" id="registerPassword" name="registerPassword" required>
            </div>
            <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Code injected by live-server -->
<script>
	// <![CDATA[  <-- For SVG support
	if ('WebSocket' in window) {
		(function () {
			function refreshCSS() {
				var sheets = [].slice.call(document.getElementsByTagName("link"));
				var head = document.getElementsByTagName("head")[0];
				for (var i = 0; i < sheets.length; ++i) {
					var elem = sheets[i];
					var parent = elem.parentElement || head;
					parent.removeChild(elem);
					var rel = elem.rel;
					if (elem.href && typeof rel != "string" || rel.length == 0 || rel.toLowerCase() == "stylesheet") {
						var url = elem.href.replace(/(&|\?)_cacheOverride=\d+/, '');
						elem.href = url + (url.indexOf('?') >= 0 ? '&' : '?') + '_cacheOverride=' + (new Date().valueOf());
					}
					parent.appendChild(elem);
				}
			}
			var protocol = window.location.protocol === 'http:' ? 'ws://' : 'wss://';
			var address = protocol + window.location.host + window.location.pathname + '/ws';
			var socket = new WebSocket(address);
			socket.onmessage = function (msg) {
				if (msg.data == 'reload') window.location.reload();
				else if (msg.data == 'refreshcss') refreshCSS();
			};
			if (sessionStorage && !sessionStorage.getItem('IsThisFirstTime_Log_From_LiveServer')) {
				console.log('Live reload enabled.');
				sessionStorage.setItem('IsThisFirstTime_Log_From_LiveServer', true);
			}
		})();
	}
	else {
		console.error('Upgrade your browser. This Browser is NOT supported WebSocket for Live-Reloading.');
	}
	// ]]>
</script>
</body>
</html>