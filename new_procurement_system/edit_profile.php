<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check if the user data exists
if (!$user) {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Basic validation
    if (empty($username) || empty($email)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Handle file upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['profile_photo']['tmp_name'];
            $file_name = basename($_FILES['profile_photo']['name']);
            $upload_dir = 'uploads/profile_photos/';
            $file_path = $upload_dir . $file_name;

            // Check if file is an image
            $check = getimagesize($file_tmp);
            if ($check === false) {
                $error_message = "Uploaded file is not an image.";
            } else {
                // Move the uploaded file to the desired directory
                if (move_uploaded_file($file_tmp, $file_path)) {
                    // Update the database with the new profile photo
                    $stmt_update = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_photo = ? WHERE id = ?");
                    $stmt_update->bind_param("sssi", $username, $email, $file_path, $user_id);

                    if ($stmt_update->execute()) {
                        $success_message = "Profile updated successfully!";
                    } else {
                        $error_message = "Failed to update profile. Please try again later.";
                    }
                } else {
                    $error_message = "Error uploading file.";
                }
            }
        } else {
            // If no file was uploaded, update without changing the photo
            $stmt_update = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt_update->bind_param("ssi", $username, $email, $user_id);

            if ($stmt_update->execute()) {
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Failed to update profile. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-lg mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
    <h2 class="text-2xl font-semibold mb-6">Edit Profile</h2>

    <?php if (isset($error_message)): ?>
        <div class="text-red-500 mb-4"><?php echo $error_message; ?></div>
    <?php elseif (isset($success_message)): ?>
        <div class="text-green-500 mb-4"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="mt-1 p-2 w-full border border-gray-300 rounded" required>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="mt-1 p-2 w-full border border-gray-300 rounded" required>
        </div>

        <div class="mb-4">
            <label for="profile_photo" class="block text-sm font-medium text-gray-700">Profile Photo</label>
            <input type="file" id="profile_photo" name="profile_photo" class="mt-1 p-2 w-full border border-gray-300 rounded" accept="image/*">
            <?php if ($user['profile_picture']): ?>
                <div class="mt-4">
                    <img src="<?php echo $user['profile_picture']; ?>" alt="Profile Photo" class="w-24 h-24 rounded-full">
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-700">Update Profile</button>
    </form>
</div>

</body>
</html>
