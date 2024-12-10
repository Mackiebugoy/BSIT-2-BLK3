<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle search query if provided
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Get sorting option from GET request (default to 'low-high')
$sort_order = isset($_GET['sort']) && in_array($_GET['sort'], ['low-high', 'high-low']) ? $_GET['sort'] : 'low-high';

// Set sorting clause based on selected sort order
if ($sort_order == 'high-low') {
    $sort_clause = "ORDER BY b.bid_amount DESC";
} else {
    $sort_clause = "ORDER BY b.bid_amount ASC";
}

// Get active bids for the buyer with search and sorting functionality
$active_bids_query = "
    SELECT b.bid_amount, a.title, a.current_price, a.ending_time 
    FROM bids b 
    JOIN auctions a ON b.auction_id = a.id 
    WHERE b.user_id = $user_id 
    AND a.status = 'approved' 
    AND a.ending_time > NOW() 
    AND a.title LIKE '%$search_query%' 
    $sort_clause
";

// For debugging purposes, uncomment to view the query and verify
// echo $active_bids_query;

$active_bids = $conn->query($active_bids_query);

// Get won auctions for the buyer dynamically
$won_auctions = $conn->query("
    SELECT a.title, a.current_price, u.email AS seller_email 
    FROM auctions a
    JOIN bids b ON a.id = b.auction_id
    JOIN users u ON a.seller_id = u.id
    WHERE a.status = 'closed'
      AND b.user_id = $user_id
      AND b.bid_amount = (
          SELECT MAX(bid_amount)
          FROM bids
          WHERE auction_id = a.id
      )
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Bids</title>
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

<!-- Sidebar -->
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
        <li><a href="profile.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
            <i class="fas fa-gavel"></i><span>Profile</span></a></li>
        <li><a href="logout.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
    </ul>
</aside>

<!-- Main Content -->
<div class="ml-64 max-w-5xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">My Bids</h1>

    <!-- Search Bar -->
    <form method="GET" action="" class="mb-4">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search by Auction Title"
               class="border border-gray-300 rounded-lg px-4 py-2 w-full mb-4">
    </form>

    <!-- Sorting Buttons -->
    <div class="mb-4">
        <a href="?search=<?php echo urlencode($search_query); ?>&sort=low-high" class="px-4 py-2 bg-blue-500 text-white rounded mr-2">Sort: Low to High</a>
        <a href="?search=<?php echo urlencode($search_query); ?>&sort=high-low" class="px-4 py-2 bg-blue-500 text-white rounded">Sort: High to Low</a>
    </div>

    <!-- Active Bids Section -->
    <h2 class="text-xl font-bold mb-2">Active Bids</h2>
    <?php if ($active_bids->num_rows > 0): ?>
        <table class="w-full table-auto border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border p-2">Auction Title</th>
                    <th class="border p-2">Current Price</th>
                    <th class="border p-2">Your Bid</th>
                    <th class="border p-2">Auction Ends</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($bid = $active_bids->fetch_assoc()): ?>
                    <tr>
                        <td class="border p-2"><?php echo htmlspecialchars($bid['title']); ?></td>
                        <td class="border p-2"><?php echo htmlspecialchars($bid['current_price']); ?></td>
                        <td class="border p-2"><?php echo htmlspecialchars($bid['bid_amount']); ?></td>
                        <td class="border p-2"><?php echo date('F j, Y, g:i a', strtotime($bid['ending_time'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-gray-500">You have no active bids at the moment.</p>
    <?php endif; ?>

    <!-- Won Auctions Section -->
    <h2 class="text-xl font-bold mt-6 mb-2">Won Auctions</h2>
    <?php if ($won_auctions->num_rows > 0): ?>
        <table class="w-full table-auto border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border p-2">Auction Title</th>
                    <th class="border p-2">Final Price</th>
                    <th class="border p-2">Seller Contact</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($won = $won_auctions->fetch_assoc()): ?>
                    <tr>
                        <td class="border p-2"><?php echo htmlspecialchars($won['title']); ?></td>
                        <td class="border p-2"><?php echo htmlspecialchars($won['current_price']); ?></td>
                        <td class="border p-2"><?php echo htmlspecialchars($won['seller_email']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-gray-500">You haven't won any auctions yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
