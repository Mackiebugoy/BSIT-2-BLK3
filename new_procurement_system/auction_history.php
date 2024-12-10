<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

$seller_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM auctions WHERE seller_id = ?");
$stmt->bind_param('i', $seller_id);
$stmt->execute();
$auctions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auction History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            transition: background-color 0.3s ease;
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
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 260px;
            background-color: #2c5282;
            padding: 20px;
            color: white;
            transition: transform 0.3s ease;
        }
        .sidebar a {
            display: block;
            padding: 12px;
            margin-bottom: 12px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .sidebar a:hover {
            background-color: #2b6cb0;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
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
        .dark .sidebar {
            background-color: #1a202c;
        }
        .dark .sidebar a:hover {
            background-color: #4a5568;
        }
        .dark .main-content {
            background-color: #2d3748;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100">

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
            <li><a href="profile.php" class="flex items-center space-x-4 hover:bg-green-600 px-4 py-2 rounded">
                <i class="fas fa-user"></i><span>Profile</span></a></li>
            <li><a href="logout.php" class="flex items-center space-x-4 hover:bg-green-600 px-4 py-2 rounded">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Header -->
        <header class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Auction History</h2>
        </header>

        <!-- Search Bar -->
        <input type="text" id="search" class="mb-4 p-3 w-full md:w-1/2 border rounded-md" placeholder="Search auctions..." onkeyup="filterAuctions()">

        <!-- Auction Table -->
        <div class="card">
            <table class="w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border p-2 text-left">Title</th>
                        <th class="border p-2 text-right">Starting Price</th>
                        <th class="border p-2 text-right">Current Price</th>
                        <th class="border p-2 text-center">Status</th>
                        <th class="border p-2 text-center">Auction Ends</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="auction-table">
                    <?php while ($auction = $auctions->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="border p-2"><?php echo htmlspecialchars($auction['title']); ?></td>
                            <td class="border p-2 text-right">₱<?php echo number_format($auction['starting_price'], 2); ?></td>
                            <td class="border p-2 text-right">₱<?php echo number_format($auction['current_price'], 2); ?></td>
                            <td class="border p-2 text-center">
                                <?php
                                    $status_class = $auction['status'] === 'active' ? 'text-green-500' : ($auction['status'] === 'closed' ? 'text-red-500' : 'text-gray-500');
                                    echo "<span class='$status_class'>" . ucfirst($auction['status']) . "</span>";
                                ?>
                            </td>
                            <td class="border p-2 text-center"><?php echo date('F j, Y, g:i a', strtotime($auction['ending_time'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Filter auctions by title
        function filterAuctions() {
            const searchQuery = document.getElementById('search').value.toLowerCase();
            const rows = document.querySelectorAll('#auction-table tr');
            rows.forEach(row => {
                const title = row.cells[0].textContent.toLowerCase();
                row.style.display = title.includes(searchQuery) ? '' : 'none';
            });
        }
    </script>

</body>
</html>
