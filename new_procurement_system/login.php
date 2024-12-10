<?php
session_start();
require_once 'config.php';  // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture the login form data
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query to check if the user exists and fetch their data
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Store user details in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];  // Store role (admin, buyer, seller)

            // Redirect based on user role
            if ($user['role'] == 'admin') {
                header("Location: dashboard_admin.php");
            } elseif ($user['role'] == 'buyer') {
                header("Location: dashboard_buyer.php");
            } elseif ($user['role'] == 'seller') {
                header("Location: dashboard_seller.php");
            }
            exit();
        } else {
            echo "<div class='text-red-500'>Incorrect password!</div>";
        }
    } else {
        echo "<div class='text-red-500'>No user found with that email.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.2.4/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-xl shadow-lg w-96">
        <h2 class="text-2xl font-bold text-center mb-6">Login</h2>

        <form action="login.php" method="POST">
            <div class="mb-4">
                <input type="email" name="email" placeholder="Email" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <input type="password" name="password" placeholder="Password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none
                       focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition duration-300">Login</button>
        </form>

        <div class="mt-4 text-center">
            <a href="register.php" class="text-blue-500">Don't have an account? Register here</a>
        </div>
    </div>
</body>
</html>
