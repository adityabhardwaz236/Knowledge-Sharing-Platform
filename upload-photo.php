<?php
require_once 'config.php';
requireLogin();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Error: User not logged in.";
    header("Location: login.php");
    exit;
}

$conn = getDBConnection(); // Initialize database connection
$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
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
            // Update the database
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?"); // Line 24
            if ($stmt === false) {
                $message = "Error preparing query: " . $conn->error;
                error_log("Prepare failed: " . $conn->error, 3, 'upload_errors.log');
            } else {
                $stmt->bind_param("si", $dest_path, $user_id);
                if ($stmt->execute()) {
                    $message = "Profile picture uploaded successfully!";
                } else {
                    $message = "Error updating profile picture: " . $stmt->error;
                    error_log("Execute failed: " . $stmt->error, 3, 'upload_errors.log');
                }
                $stmt->close();
            }
        } else {
            $message = "Error uploading the profile picture.";
            error_log("Failed to move uploaded file to $dest_path", 3, 'upload_errors.log');
        }
    } else {
        $message = "Invalid image file. Only JPG, PNG, GIF, WEBP under 2MB allowed.";
    }
} else {
    $message = "No file uploaded or upload error.";
}

$conn->close();
$_SESSION['message'] = $message;
header("Location: profile-settings.php");
exit;
?>