<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];

    // Validate file (image only)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        die("Only JPG, PNG, and GIF files are allowed.");
    }

    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        die("File size should not exceed 5MB.");
    }

    // Create a unique name for the file
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_file_name = uniqid('profile_', true) . '.' . $file_extension;
    $upload_dir = 'uploads/profile_pictures/';

    // Create the upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Move the uploaded file to the server's directory
    $file_path = $upload_dir . $new_file_name;
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Update the database with the new file path
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $file_path, $user_id);

        if ($stmt->execute()) {
            header('Location: profile.php');
            exit();
        } else {
            die("Error updating the profile picture.");
        }
    } else {
        die("Error uploading the file. Please try again.");
    }
}
?>
