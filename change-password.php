<?php
require_once 'config.php';
requireLogin();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Error: User not logged in.";
    header("Location: login.php");
    exit;
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$message = '';

// Fetch current hashed password
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
if ($stmt === false) {
    $_SESSION['message'] = "Error preparing query: " . $conn->error;
    header("Location: profile-settings.php");
    exit;
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user) {
    $_SESSION['message'] = "Error: User not found.";
    header("Location: dashboard.php");
    exit;
}
$stmt->close();
$current_hashed_password = $user['password'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required.";
    } elseif (!password_verify($current_password, $current_hashed_password)) {
        $message = "Current password is incorrect.";
    } elseif (strlen($new_password) < 8) {
        $message = "New password must be at least 8 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $message = "New password and confirmation do not match.";
    } else {
        // Hash the new password
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt === false) {
            $message = "Error preparing query: " . $conn->error;
            error_log("Prepare failed: " . $conn->error, 3, 'password_errors.log');
        } else {
            $stmt->bind_param("si", $new_hashed_password, $user_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Password changed successfully!";
                $stmt->close();
                $conn->close();
                header("Location: profile-settings.php");
                exit;
            } else {
                $message = "Error updating password: " . $stmt->error;
                error_log("Execute failed: " . $stmt->error, 3, 'password_errors.log');
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - NPS eLearning</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: #f8f9fa;
            padding-top: 80px;
        }
        .password-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 40px 30px;
            margin-bottom: 30px;
        }
        .password-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .btn-action {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1.05em;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'navbar-loggedin.php'; ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="password-card">
                    <div class="password-header">
                        <h2>Change Password</h2>
                        <p class="text-muted">Update your account password</p>
                    </div>
                    <?php if ($message): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <form id="change-password-form" action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                            <div class="form-text">Minimum 8 characters.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-action w-100">Change Password</button>
                        <a href="profile-settings.php" class="btn btn-outline-secondary btn-action w-100 mt-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>