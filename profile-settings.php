<?php
require_once 'config.php';
requireLogin();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Error: User not logged in.";
    header("Location: login.php");
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
if ($stmt === false) {
    $_SESSION['message'] = "Error preparing query: " . $conn->error;
    header("Location: dashboard.php");
    exit;
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user) {
    $_SESSION['message'] = "Error: User not found.";
    header("Location: dashboard.php");
    exit;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - NPS eLearning</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .profile-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 40px 30px 32px 30px;
            margin-bottom: 30px;
            transition: box-shadow 0.3s;
        }
        .profile-card:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.14);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 32px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
        }
        .profile-picture-container {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 18px;
        }
        .profile-picture {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.10);
        }
        .upload-btn-wrapper {
            position: absolute;
            right: 0;
            bottom: 0;
        }
        .upload-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(13,110,253,0.15);
            transition: background 0.2s, transform 0.2s;
        }
        .upload-btn:hover {
            background: #0b5ed7;
            transform: scale(1.08);
        }
        .upload-btn input[type=file] {
            position: absolute;
            left: 0; top: 0;
            opacity: 0;
            width: 100%; height: 100%;
            cursor: pointer;
        }
        .info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px 18px;
            margin-bottom: 24px;
        }
        .info-item {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0;
        }
        .info-value {
            font-size: 1.07em;
            color: #212529;
            text-align: right;
        }
        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }
        .btn-action {
            padding: 12px 22px;
            border-radius: 8px;
            font-size: 1.05em;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .btn-action:hover {
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 5px 16px rgba(0,0,0,0.10);
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
            animation: slideIn 0.5s ease;
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @media (max-width: 600px) {
            .profile-card { padding: 16px 6px; }
            .profile-header { padding-bottom: 12px; }
            .info-section { padding: 10px 6px; }
        }
    </style>
</head>
<body>
    <?php include 'navbar-loggedin.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-picture-container">
                            <img src="<?php echo htmlspecialchars(isset($user['profile_picture']) && file_exists($user['profile_picture']) ? $user['profile_picture'] : 'IMG/default-profile.jpg'); ?>" 
                                 alt="Profile Picture" class="profile-picture">
                            <div class="upload-btn-wrapper">
                                <form action="upload-photo.php" method="POST" enctype="multipart/form-data" id="profile-pic-form">
                                    <div class="upload-btn">
                                        <i class="bi bi-camera-fill"></i>
                                        <input type="file" name="profile_picture" accept="image/*" onchange="this.form.submit()">
                                    </div>
                                </form>
                            </div>
                        </div>
                        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <p class="text-muted"><?php echo ucfirst($user['user_type']); ?></p>
                    </div>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo strpos($_SESSION['message'], 'Error') !== false ? 'danger' : 'success'; ?> alert-dismissible fade show">
                            <?php 
                                echo $_SESSION['message']; 
                                unset($_SESSION['message']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="info-section">
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Bio</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['bio'] ?? 'No bio added yet'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Login</div>
                            <div class="info-value"><?php echo date('F j, Y g:i a', strtotime($user['last_login'])); ?></div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="update-profile.php" class="btn btn-primary btn-action">
                            <i class="bi bi-pencil-square me-2"></i>Edit Profile
                        </a>
                        <a href="change-password.php" class="btn btn-outline-primary btn-action">
                            <i class="bi bi-shield-lock me-2"></i>Change Password
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary btn-action">
                            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>