<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the username and profile picture from the database
$query = "SELECT username, profile_picture FROM users WHERE id = $user_id";
$result = $conn->query($query);
$user = $result->fetch_assoc();

$username = $user['username'] ?? 'Buyer';
$profile_picture = $user['profile_picture'] ?? 'uploads/profile_pictures/default.jpg'; // Default image if not set

// Fetch number of bids placed by the user
$query_bids = "SELECT COUNT(*) as bid_count FROM bids WHERE user_id = $user_id";
$result_bids = $conn->query($query_bids);
$bids = $result_bids->fetch_assoc();
$bid_count = $bids['bid_count'] ?? 0;

// Fetch number of auctions
$query_auctions = "SELECT COUNT(*) as auction_count FROM auctions";
$result_auctions = $conn->query($query_auctions);
$auctions = $result_auctions->fetch_assoc();
$auction_count = $auctions['auction_count'] ?? 0;

// Fetch total amount spent by the user
$query_spent = "SELECT SUM(bid_amount) as total_spent FROM bids WHERE user_id = $user_id";
$result_spent = $conn->query($query_spent);
$spent = $result_spent->fetch_assoc();
$total_spent = $spent['total_spent'] ?? 0;

// Fetch weekly bids and spending data for the charts
$query_weekly_bids = "SELECT YEAR(created_at) AS year, WEEK(created_at) AS week, COUNT(*) AS bid_count 
                      FROM bids 
                      WHERE user_id = $user_id 
                      GROUP BY YEAR(created_at), WEEK(created_at) 
                      ORDER BY YEAR(created_at) DESC, WEEK(created_at) DESC";
$result_weekly_bids = $conn->query($query_weekly_bids);
$weekly_bids = [];
$week_labels = [];

while ($row = $result_weekly_bids->fetch_assoc()) {
    $weekly_bids[] = $row['bid_count'];
    $week_labels[] = 'Week ' . $row['week'] . ' (' . $row['year'] . ')';
}

$query_weekly_spending = "SELECT YEAR(created_at) AS year, WEEK(created_at) AS week, SUM(bid_amount) AS total_spent 
                          FROM bids 
                          WHERE user_id = $user_id 
                          GROUP BY YEAR(created_at), WEEK(created_at) 
                          ORDER BY YEAR(created_at) DESC, WEEK(created_at) DESC";
$result_weekly_spending = $conn->query($query_weekly_spending);
$weekly_spending = [];

while ($row = $result_weekly_spending->fetch_assoc()) {
    $weekly_spending[] = $row['total_spent'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Buyer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            opacity: 0;
            animation: fadeIn 1s forwards;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
        }

        .chart-container {
            height: 300px;
        }

        .button-transition {
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .button-transition:hover {
            transform: scale(1.05);
            background-color: #2c5282;
        }

        /* Dark mode styling */
        .dark .bg-gray-100 {
            background-color: #1a202c;
        }
        .dark .text-white {
            color: #e2e8f0;
        }
        .dark .bg-blue-600 {
            background-color: #2b6cb0;
        }

        .dark .card {
            background: #2d3748;
        }

        .dark .card:hover {
            transform: translateY(-10px);
            background: #4a5568;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        /* Mobile responsive adjustments */
        @media (max-width: 768px) {
            .ml-64 {
                margin-left: 0;
            }
            .w-64 {
                width: 100%;
            }
            .stats-container {
                grid-template-columns: 1fr;
            }
            .card {
                margin-bottom: 16px;
            }
            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-800 dark:text-white h-screen flex transition-colors duration-300">

    <!-- Sidebar -->
    <aside class="bg-blue-600 text-white w-64 p-6 fixed inset-y-0 left-0 transition-all duration-300 z-10 lg:w-64">
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
    <div class="ml-64 flex-1 p-8 lg:ml-64">
        <!-- Header -->
        <header class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <div class="flex items-center space-x-4">
                <!-- Link profile picture to profile.php -->
                <a href="profile.php">
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="w-12 h-12 rounded-full border-2 border-gray-200">
                </a>
                <span><?php echo htmlspecialchars($username); ?></span>
            </div>
        </header>

        <!-- Dark Mode Toggle -->
        <div class="mb-8 flex justify-end">
            <button id="dark-mode-toggle" class="px-4 py-2 bg-blue-600 text-white rounded-full focus:outline-none transition duration-300 button-transition">Toggle Dark Mode</button>
        </div>

        <!-- Stats Overview -->
        <section class="stats-container mb-8">
            <div class="card">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-gavel text-blue-600"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Bids Placed</h3>
                        <p class="text-2xl font-bold"><?php echo $bid_count; ?></p>
                    </div>
                </div>
                <span class="text-green-500">+5.00%</span>
            </div>
            <div class="card">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-archive text-blue-600"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Auctions</h3>
                        <p class="text-2xl font-bold"><?php echo $auction_count; ?></p>
                    </div>
                </div>
                <span class="text-red-500">-3.00%</span>
            </div>
            <div class="card">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-dollar-sign text-blue-600"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Total Spent</h3>
                        <p class="text-2xl font-bold"><?php echo number_format($total_spent, 2); ?> PHP</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Charts -->
        <section>
            <div class="card chart-container">
                <canvas id="weekly-bids-chart"></canvas>
            </div>
            <div class="card chart-container">
                <canvas id="weekly-spending-chart"></canvas>
            </div>
        </section>
    </div>

    <script>
        // Toggle Dark Mode
        const toggleButton = document.getElementById('dark-mode-toggle');
        toggleButton.addEventListener('click', () => {
            document.body.classList.toggle('dark');
        });

        // Weekly Bids Chart
        const weeklyBidsChart = new Chart(document.getElementById('weekly-bids-chart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($week_labels); ?>,
                datasets: [{
                    label: 'Weekly Bids',
                    data: <?php echo json_encode($weekly_bids); ?>,
                    backgroundColor: 'rgba(66, 153, 225, 0.5)',
                    borderColor: 'rgba(66, 153, 225, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Weekly Spending Chart
        const weeklySpendingChart = new Chart(document.getElementById('weekly-spending-chart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($week_labels); ?>,
                datasets: [{
                    label: 'Weekly Spending (PHP)',
                    data: <?php echo json_encode($weekly_spending); ?>,
                    borderColor: 'rgba(66, 153, 225, 1)',
                    backgroundColor: 'rgba(66, 153, 225, 0.2)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
