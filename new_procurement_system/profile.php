<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, role, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $user = $stmt->get_result()->fetch_assoc();
} else {
    die("Error retrieving user data.");
}

if (!$user) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Profile Card -->
    <div class="max-w-lg mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">Profile</h2>
            <a href="javascript:void(0);" onclick="document.getElementById('profile-modal').classList.remove('hidden')" class="text-blue-500 hover:text-blue-700">Edit</a>
        </div>

        <div class="space-y-4">
            <!-- Profile Picture Section -->
            <div class="flex justify-center">
                <!-- Display the profile picture from the database -->
                <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'uploads/profile_pictures/default.jpg'); ?>" alt="Profile Picture" class="rounded-full w-32 h-32 border-2 border-gray-300">
            </div>

            <div>
                <p><strong class="font-medium">Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            </div>

            <div>
                <p><strong class="font-medium">Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <div>
                <p><strong class="font-medium">Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
            </div>
        </div>

        <!-- Logout Button -->
        <div class="mt-6">
            <a href="logout.php" class="block text-center text-red-500 hover:text-red-700">Logout</a>
        </div>
    </div>

    <!-- Modal for Profile Edit -->
    <div id="profile-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h3 class="text-xl font-semibold mb-4">Update Profile Picture</h3>
            <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="profile_picture" class="block text-sm font-medium">Choose a new profile picture</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required class="border p-2 mt-2 w-full rounded">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Upload</button>
                    <button type="button" class="ml-4 px-4 py-2 bg-red-500 text-white rounded-lg" onclick="document.getElementById('profile-modal').classList.add('hidden')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
