<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the form was submitted and a file was uploaded
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $file = $_FILES['profile_picture'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Validate the file extension
    if (in_array($file_extension, $allowed_extensions)) {
        $upload_dir = 'uploads/profile_pictures/';
        $file_name = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;

        // Ensure the upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Move the file to the desired folder
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Update the user's profile picture path in the database
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $file_path, $user_id);
            $stmt->execute();

            // Redirect back to the dashboard with a success message
            header("Location: dashboard_buyer.php?upload=success");
            exit();
        } else {
            echo "Error uploading the file.";
        }
    } else {
        echo "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
    }
} else {
    echo "No file uploaded.";
}
?>
