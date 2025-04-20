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

// Fetch current user info
$stmt = $conn->prepare("SELECT full_name, email, phone, bio, profile_picture FROM users WHERE id = ?");
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

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);
    $profile_picture = $user['profile_picture'] ?? null;

    // Validate inputs
    if (empty($name) || empty($email)) {
        $message = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
            $fileName = basename($_FILES['profile_picture']['name']);
            $fileSize = $_FILES['profile_picture']['size'];
            $fileType = mime_content_type($fileTmpPath);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (in_array($fileType, $allowedTypes) && $fileSize <= 2 * 1024 * 1024) { // 2MB max
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                $dest_path = 'IMG/' . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $profile_picture = $dest_path;
                } else {
                    $message = "Error uploading the profile picture.";
                    error_log("Failed to move uploaded file to $dest_path", 3, 'upload_errors.log');
                }
            } else {
                $message = "Invalid image file. Only JPG, PNG, GIF, WEBP under 2MB allowed.";
            }
        }

        // Update user info if no upload errors
        if (empty($message)) {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, bio = ?, profile_picture = ? WHERE id = ?");
            if ($stmt === false) {
                $message = "Error preparing query: " . $conn->error;
                error_log("Prepare failed: " . $conn->error, 3, 'upload_errors.log');
            } else {
                $stmt->bind_param("sssssi", $name, $email, $phone, $bio, $profile_picture, $user_id);
                if ($stmt->execute()) {
                    $message = "Profile updated successfully!";
                    // Refresh user data
                    $user['full_name'] = $name;
                    $user['email'] = $email;
                    $user['phone'] = $phone;
                    $user['bio'] = $bio;
                    $user['profile_picture'] = $profile_picture;
                } else {
                    $message = "Error updating profile: " . $stmt->error;
                    error_log("Execute failed: " . $stmt->error, 3, 'upload_errors.log');
                }
                $stmt->close();
            }
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
    <title>Update Profile - NPS eLearning</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: #f8f9fa;
            padding-top: 80px;
        }
        .profile-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 40px 30px;
            margin-bottom: 30px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 32px;
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
        }
        .upload-btn:hover {
            background: #0b5ed7;
            transform: scale(1.08);
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
            <div class="col-md-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-picture-container">
                            <img src="<?php echo htmlspecialchars(isset($user['profile_picture']) && file_exists($user['profile_picture']) ? $user['profile_picture'] : 'IMG/default-profile.jpg'); ?>" 
                                 alt="Profile Picture" class="profile-picture">
                            <div class="upload-btn-wrapper">
                                <div class="upload-btn">
                                    <i class="bi bi-camera-fill"></i>
                                </div>
                            </div>
                        </div>
                        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    </div>
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo strpos($message, 'success') !== false ? 'success' : 'danger'; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <form id="update-form" action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" name="profile_picture" class="form-control" accept="image/*">
                            <div class="form-text">Accepted: JPG, PNG, GIF, WEBP. Max size: 2MB.</div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-action w-100">Update Profile</button>
                        <a href="profile-settings.php" class="btn btn-outline-secondary btn-action w-100 mt-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.querySelector('.upload-btn').addEventListener('click', () => {
            document.querySelector('input[name="profile_picture"]').click();
        });
    </script>
</body>
</html>