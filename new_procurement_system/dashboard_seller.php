<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the username and profile picture from the database
$query = "SELECT username, profile_picture FROM users WHERE id = $user_id";
$result = $conn->query($query);
$user = $result->fetch_assoc();

$username = $user['username'] ?? 'Seller';
$profile_picture = $user['profile_picture'] ?? 'uploads/profile_pictures/default.jpg'; // Default image if not set

// Fetch the number of auctions by the seller
$query_auctions = "SELECT COUNT(*) as auction_count FROM auctions WHERE seller_id = $user_id";
$result_auctions = $conn->query($query_auctions);
$auctions = $result_auctions->fetch_assoc();
$auction_count = $auctions['auction_count'] ?? 0;

// Fetch the number of buyers (distinct users who have placed bids on auctions)
$query_buyers = "SELECT COUNT(DISTINCT id) as buyer_count FROM bids WHERE auction_id IN (SELECT id FROM auctions WHERE seller_id = $user_id)";
$result_buyers = $conn->query($query_buyers);
$buyers = $result_buyers->fetch_assoc();
$buyer_count = $buyers['buyer_count'] ?? 0;

// Fetch the total revenue from auctions (sold auctions only)
$query_revenue = "SELECT SUM(bid_amount) as total_revenue FROM bids WHERE auction_id";
$result_revenue = $conn->query($query_revenue);
$revenue = $result_revenue->fetch_assoc();
$total_revenue = $revenue['total_revenue'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Seller Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            opacity: 0;
            animation: fadeIn 1.5s ease-out forwards;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            animation: fadeUp 1s ease-out forwards;
        }

        .card:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card:nth-child(1) {
            animation-delay: 0.2s;
        }

        .card:nth-child(2) {
            animation-delay: 0.4s;
        }

        .card:nth-child(3) {
            animation-delay: 0.6s;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
        }

        .chart-container {
            height: 300px;
            opacity: 0;
            animation: fadeInUp 1s ease-out forwards;
        }

        .button-transition {
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .button-transition:hover {
            transform: scale(1.05);
            background-color: #2c5282;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        @keyframes fadeUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Dark Mode Transition */
        body.dark {
            background-color: #2d3748;
            color: white;
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-800 dark:text-white h-screen flex transition-colors duration-300">

    <!-- Sidebar -->
    <aside class="bg-green-500 text-white w-64 p-6 fixed inset-y-0 left-0 transition-all duration-300">
        <h1 class="text-2xl font-bold mb-6">Seller Dashboard</h1>
        <ul class="space-y-4">
            <li><a href="dashboard_seller.php" class="flex items-center space-x-4 hover:bg-green-600 px-4 py-2 rounded">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li><a href="create_auction.php" class="flex items-center space-x-4 hover:bg-green-600 px-4 py-2 rounded">
                <i class="fas fa-plus-circle"></i><span>Create Auction</span></a></li>
            <li><a href="auction_history.php" class="flex items-center space-x-4 hover:bg-green-600 px-4 py-2 rounded">
                <i class="fas fa-history"></i><span>Auction History</span></a></li>
            <li><a href="logout.php" class="flex items-center space-x-4 hover:bg-green-600 px-4 py-2 rounded">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="ml-64 flex-1 p-8">
        <!-- Header -->
        <header class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <div class="flex items-center space-x-4 cursor-pointer">
                <a href="profile.php">
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="w-12 h-12 rounded-full border-2 border-gray-200">
                </a>
                <span><?php echo htmlspecialchars($username); ?></span>
            </div>
        </header>

        <!-- Dark Mode Toggle -->
        <div class="mb-8 flex justify-end">
            <button id="dark-mode-toggle" class="px-4 py-2 bg-green-600 text-white rounded-full focus:outline-none transition duration-300 button-transition">Toggle Dark Mode</button>
        </div>

        <!-- Stats Overview -->
        <section class="stats-container mb-8">
            <div class="card">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-box"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Total Auctions</h3>
                        <p class="text-2xl"><?php echo $auction_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-users"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Total Buyers</h3>
                        <p class="text-2xl"><?php echo $buyer_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-dollar-sign"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Total Revenue</h3>
                        <p class="text-2xl">₱<?php echo number_format($total_revenue, 2); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Chart Section -->
        <section>
            <h3 class="text-xl font-semibold mb-4">Auction Revenue (Last 4 Weeks)</h3>
            <div class="chart-container mb-8">
                <canvas id="auction-revenue-chart"></canvas>
            </div>
        </section>
    </div>

    <script>
        // Toggle Dark Mode
        document.getElementById('dark-mode-toggle').addEventListener('click', () => {
            document.body.classList.toggle('dark');
        });

        // Data for Auction Revenue Chart
        const ctx = document.getElementById('auction-revenue-chart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    label: 'Revenue (₱)',
                    data: [12000, 15000, 18000, 22000], // Example data
                    backgroundColor: 'rgba(34, 197, 94, 0.6)',
                    borderColor: 'rgba(34, 197, 94, 1)',
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
