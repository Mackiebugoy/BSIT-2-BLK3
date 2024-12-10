<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $starting_price = $_POST['starting_price'];
    $end_time = $_POST['end_time'];
    $seller_id = $_SESSION['user_id'];

    $image_path = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_path = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }

    $stmt = $conn->prepare("
        INSERT INTO auctions (title, description, starting_price, current_price, ending_time, image_path, seller_id, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param('ssddssi', $title, $description, $starting_price, $starting_price, $end_time, $image_path, $seller_id);
    $stmt->execute();

    header('Location: dashboard_seller.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Create Auction</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            opacity: 0;
            animation: fadeIn 0.6s ease-out forwards;
        }

        .card:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
        }

        .button-transition {
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .button-transition:hover {
            transform: scale(1.05);
            background-color: #2c5282;
        }

        .sidebar {
            background-color: #38a169;
            transition: transform 0.3s ease-in-out;
        }

        .sidebar.closed {
            transform: translateX(-100%);
        }

        .sidebar ul li a {
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .sidebar ul li a:hover {
            transform: scale(1.05);
            background-color: #2c5282;
        }

        /* Smooth fade-in animation */
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        /* Smooth slide-up animation for form inputs */
        @keyframes slideUp {
            0% {
                transform: translateY(30px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .form-input, .form-textarea, .form-button {
            animation: slideUp 0.6s ease-out forwards;
        }

        .form-input {
            animation-delay: 0.1s;
        }

        .form-textarea {
            animation-delay: 0.2s;
        }

        .form-button {
            animation-delay: 0.3s;
        }

    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-800 dark:text-white h-screen flex transition-colors duration-300">

    <!-- Sidebar -->
    <aside class="sidebar bg-green-500 text-white w-64 p-6 fixed inset-y-0 left-0 transition-all duration-300">
        <h1 class="text-2xl font-bold mb-6">Seller Dashboard</h1>
        <ul class="space-y-4">
            <li><a href="dashboard_seller.php" class="flex items-center space-x-4 hover:bg-green-600 px-4 py-2 rounded">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li><a href="create_auction.php" class="flex items-center space-x-4 hover:bg-green-600 px-4 py-2 rounded">
                <i class="fas fa-plus-circle"></i><span>Create Auction</span></a></li>
            <li><a href="auction_history.php" class="flex items-center space-x-4 hover:bg-green-600 px-4 py-2 rounded">
                <i class="fas fa-history"></i><span>Auction History</span></a></li>
            <li><a href="profile.php" class="flex items-center space-x-4 hover:bg-green-600 px-4 py-2 rounded">
                <i class="fas fa-user"></i><span>Profile</span></a></li>
            <li><a href="logout.php" class="flex items-center space-x-4 hover:bg-green-600 px-4 py-2 rounded">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="ml-64 flex-1 p-8">
        <!-- Header -->
        <header class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Create Auction</h2>
        </header>

        <!-- Auction Form -->
        <section class="bg-white shadow-lg rounded-lg p-6">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block font-semibold">Title</label>
                    <input type="text" name="title" class="w-full p-2 border rounded form-input" required>
                </div>
                <div>
                    <label class="block font-semibold">Description</label>
                    <textarea name="description" class="w-full p-2 border rounded form-textarea" required></textarea>
                </div>
                <div>
                    <label class="block font-semibold">Starting Price</label>
                    <input type="number" name="starting_price" class="w-full p-2 border rounded form-input" required>
                </div>
                <div>
                    <label class="block font-semibold">Auction Duration (End Time)</label>
                    <input type="datetime-local" name="end_time" class="w-full p-2 border rounded form-input" required>
                </div>
                <div>
                    <label class="block font-semibold">Upload Image</label>
                    <input type="file" name="image" class="w-full p-2 border rounded form-input">
                </div>
                <button type="submit" class="w-full py-3 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200 form-button">Submit Auction</button>
            </form>
        </section>
    </div>

</body>
</html>
