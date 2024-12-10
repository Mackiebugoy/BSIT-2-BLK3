<?php
session_start();
require 'config.php';

// Set the time zone to the Philippines
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}

// Fetch all approved auctions that have not yet ended
$products = $conn->query("SELECT * FROM auctions WHERE status = 'approved' AND ending_time > NOW()");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Listings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script>
        // Countdown function that receives the remaining time in seconds
        function startCountdown(remainingTimeInSeconds, elementId) {
            const countdownInterval = setInterval(function() {
                if (remainingTimeInSeconds <= 0) {
                    clearInterval(countdownInterval);
                    document.getElementById(elementId).innerHTML = "Auction Ended";
                } else {
                    const hours = Math.floor((remainingTimeInSeconds / 3600));
                    const minutes = Math.floor((remainingTimeInSeconds % 3600) / 60);
                    const seconds = remainingTimeInSeconds % 60;
                    document.getElementById(elementId).innerHTML = `${hours}h ${minutes}m ${seconds}s`;
                    remainingTimeInSeconds--;
                }
            }, 1000);
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F7FAFC;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .countdown-bar {
            height: 4px;
            background-color: #E2E8F0;
            margin-top: 10px;
            position: relative;
        }

        .countdown-fill {
            height: 4px;
            background-color: #F56565;
            width: 0%;
            position: absolute;
            top: 0;
            left: 0;
            transition: width 1s linear;
        }

        /* Fade-in animation for card */
        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        /* Slide-up animation for countdown */
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

        .countdown {
            animation: slideUp 0.5s ease-out;
        }
    </style>
</head>
<body>
<aside class="bg-blue-600 text-white w-64 p-6 fixed inset-y-0 left-0 transition-all duration-300">
        <h1 class="text-2xl font-bold mb-6">PBS</h1>
        <ul class="space-y-4">
            <li><a href="dashboard_buyer.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li><a href="product_list.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-list"></i><span>Product lists</span></a></li>
            <li><a href="notifications.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-bell"></i><span>Notifications</span></a></li>
            <li><a href="bids.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-gavel"></i><span>My bids</span></a></li>
            <li><a href="logout.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </aside>

<!-- Main Content -->
<div class="main-content ml-64 p-6">
    <h1 class="text-2xl font-bold mb-6">Product Listings</h1>
    <div class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while ($product = $products->fetch_assoc()): ?>
            <?php 
                $current_time = time(); // Current server time as a Unix timestamp
                $end_time = strtotime($product['ending_time']); // Auction end time as a Unix timestamp
                $remaining_time = $end_time - $current_time; // Remaining time in seconds
            ?>
            <a href="view_item.php?id=<?php echo $product['id']; ?>" class="card block">
                <img src="<?php echo $product['image_path']; ?>" alt="Product Image" class="h-40 w-full object-cover rounded-md mb-4">
                <h2 class="font-semibold text-lg"><?php echo $product['title']; ?></h2>
                <p class="text-gray-600"><?php echo substr($product['description'], 0, 100) . '...'; ?></p>
                <p class="text-blue-600 font-bold mt-2">Starting Price: ₱<?php echo number_format($product['starting_price'], 2); ?></p>
                <p class="text-gray-800">Current Price: ₱<?php echo number_format($product['current_price'], decimals: 2); ?></p>
                <p class="mt-2 text-sm text-red-500 countdown">
                    Auction Ends: <span id="countdown-<?php echo $product['id']; ?>"></span>
                </p>
                <script>
                    startCountdown(<?php echo $remaining_time; ?>, "countdown-<?php echo $product['id']; ?>");
                </script>
            </a>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
