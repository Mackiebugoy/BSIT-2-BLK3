<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch analytics data
$total_users_query = "SELECT COUNT(*) AS total_users FROM users";
$result_users = $conn->query($total_users_query);
$total_users = $result_users->fetch_assoc()['total_users'] ?? 0;

$active_auctions_query = "SELECT COUNT(*) AS active_auctions FROM auctions WHERE status = 'active'";
$result_auctions = $conn->query($active_auctions_query);
$active_auctions = $result_auctions->fetch_assoc()['active_auctions'] ?? 0;

$bidding_trends_query = "SELECT DATE(created_at) AS bid_date, COUNT(*) AS bid_count 
                         FROM bids GROUP BY DATE(created_at) ORDER BY created_at DESC LIMIT 7";
$result_bidding_trends = $conn->query($bidding_trends_query);
$bidding_trends = [];
$bid_dates = [];

while ($row = $result_bidding_trends->fetch_assoc()) {
    $bidding_trends[] = $row['bid_count'];
    $bid_dates[] = $row['bid_date'];
}

// Fetch new listings
$new_listings_query = "SELECT id, title, created_at FROM auctions WHERE status = 'active' ORDER BY created_at DESC LIMIT 5";
$result_listings = $conn->query($new_listings_query);
$new_listings = $result_listings->fetch_all(MYSQLI_ASSOC);

// Fetch auction notifications (active and upcoming auctions)
$auction_notifications_query = "
    SELECT title, created_at 
    FROM auctions 
    WHERE status = 'active' OR created_at > NOW() 
    ORDER BY created_at ASC 
    LIMIT 5";
$result_notifications = $conn->query($auction_notifications_query);
$auction_notifications = $result_notifications->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        body.loaded {
            opacity: 1;
            transform: translateY(0);
        }

        /* Sidebar slide-in */
        aside {
            transform: translateX(-100%);
            transition: transform 0.6s ease-out;
        }

        aside.loaded {
            transform: translateX(0);
        }

        /* Card hover effect */
        .analytics-card {
            transform: scale(1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .analytics-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-800 h-screen flex">

    <!-- Page Load Animation -->
    <div id="loadingScreen" class="fixed inset-0 bg-white dark:bg-gray-900 flex items-center justify-center z-50">
        <div class="w-16 h-16 border-4 border-blue-500 border-dashed rounded-full animate-spin"></div>
    </div>

    <!-- Sidebar -->
    <aside class="bg-blue-600 text-white w-64 p-6 fixed inset-y-0 left-0">
        <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>
        <ul class="space-y-4">
            <li><a href="dashboard_admin.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li><a href="manage_users.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-users"></i><span>Manage Users</span></a></li>
            <li><a href="manage_auctions.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-clipboard-list"></i><span>Manage Auctions</span></a></li>
            <li><a href="profile.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-user"></i><span>Profile</span></a></li>
            <li><a href="logout.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 p-8 flex-1">
        <!-- Welcome Header -->
        <header class="mb-8">
            <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-200">Welcome, Admin!</h2>
            <p class="text-gray-600 dark:text-gray-400">Here's an overview of the platform's activity and statistics.</p>
        </header>

        <!-- Analytics Cards -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="analytics-card bg-white dark:bg-gray-700 p-6 rounded shadow">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Total Users</h3>
                <p class="text-2xl font-bold text-blue-600"><?php echo $total_users; ?></p>
            </div>
            <div class="analytics-card bg-white dark:bg-gray-700 p-6 rounded shadow">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Active Auctions</h3>
                <p class="text-2xl font-bold text-blue-600"><?php echo $active_auctions; ?></p>
            </div>
            <div class="analytics-card bg-white dark:bg-gray-700 p-6 rounded shadow">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Weekly Bids</h3>
                <p class="text-2xl font-bold text-blue-600"><?php echo array_sum($bidding_trends); ?></p>
            </div>
        </section>

        <!-- Charts Section -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-700 p-6 rounded shadow">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Bidding Trends</h3>
                <canvas id="biddingTrendsChart"></canvas>
            </div>
            <div class="bg-white dark:bg-gray-700 p-6 rounded shadow">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">User Activity Alerts</h3>
                <ul class="space-y-2">
                    <?php foreach ($auction_notifications as $notification): ?>
                        <li class="text-gray-700 dark:text-gray-300">
                            <i class="fas fa-bullhorn text-blue-600"></i>
                            Auction: <strong><?php echo htmlspecialchars($notification['title']); ?></strong> 
                            (<?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($notification['created_at']))); ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    </main>

    <script>
        // Hide loading screen after page load
        window.addEventListener('load', function () {
            document.getElementById('loadingScreen').style.display = 'none';
            document.body.classList.add('loaded');
            document.querySelector('aside').classList.add('loaded');
        });

        // Bidding Trends Chart
        const biddingTrendsData = {
            labels: <?php echo json_encode(array_reverse($bid_dates)); ?>,
            datasets: [{
                label: 'Bids',
                data: <?php echo json_encode(array_reverse($bidding_trends)); ?>,
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                fill: true,
                tension: 0.4
            }]
        };

        const biddingTrendsConfig = {
            type: 'line',
            data: biddingTrendsData,
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                }
            }
        };

        new Chart(document.getElementById('biddingTrendsChart'), biddingTrendsConfig);
    </script>
</body>
</html>
