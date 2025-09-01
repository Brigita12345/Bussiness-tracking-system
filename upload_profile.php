<?php
session_start();
include("connection.php");

if (!isset($_SESSION['saler_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['saler_id'];

// Check if a file is uploaded
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
    $fileName = $_FILES['profile_pic']['name'];
    $fileSize = $_FILES['profile_pic']['size'];
    $fileType = $_FILES['profile_pic']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Allowed file extensions
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileExtension, $allowedExts)) {
        // Rename the file to avoid conflicts
        $newFileName = 'profile_' . $id . '_' . time() . '.' . $fileExtension;
        $uploadFileDir = 'uploads/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        $destPath = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Update database
            $sql = "UPDATE users SET profile_pic=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $destPath, $id);
            if ($stmt->execute()) {
                $_SESSION['upload_success'] = "Profile picture updated successfully.";
            } else {
                $_SESSION['upload_error'] = "Database update failed.";
            }
        } else {
            $_SESSION['upload_error'] = "Failed to move uploaded file.";
        }
    } else {
        $_SESSION['upload_error'] = "Invalid file type. Allowed: jpg, jpeg, png, gif.";
    }
} else {
    $_SESSION['upload_error'] = "No file uploaded or upload error.";
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit();
