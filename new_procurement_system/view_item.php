<?php 
session_start();
require 'config.php';

// Set the time zone to the Philippines
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}

$auction_id = $_GET['id'];
$auction = $conn->query("SELECT * FROM auctions WHERE id = $auction_id")->fetch_assoc();

// Calculate the remaining time in seconds
$current_time = time(); // Current time (Unix timestamp)
$end_time = strtotime($auction['ending_time']); // Convert auction ending time to Unix timestamp
$remaining_time = $end_time - $current_time; // Remaining time in seconds

// Handle auction end logic (if auction has ended)
if ($remaining_time <= 0) {
    // Auction has ended, determine the highest bidder
    $highest_bid_query = $conn->query("SELECT b.bid_amount, u.username, u.email AS buyer_email, a.seller_id, s.email AS seller_email
        FROM bids b
        JOIN users u ON b.user_id = u.id
        JOIN auctions a ON b.auction_id = a.id
        JOIN users s ON a.seller_id = s.id
        WHERE b.auction_id = $auction_id
        ORDER BY b.bid_amount DESC LIMIT 1");

    if ($highest_bid_query->num_rows > 0) {
        $highest_bid = $highest_bid_query->fetch_assoc();
        $winner_username = $highest_bid['username'];
        $winner_email = $highest_bid['buyer_email'];
        $winning_bid_amount = $highest_bid['bid_amount'];
        $seller_email = $highest_bid['seller_email'];

        // Notify the winner
        $message = "Congratulations $winner_username! You have won the auction for '{$auction['title']}' with a bid of ₱" . number_format($winning_bid_amount, 2) . ". The seller's email is $seller_email.";

        // Insert notification for the winner
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $_SESSION['user_id'], $message);
        $stmt->execute();

        // Insert notification for the seller
        $seller_message = "Your auction '{$auction['title']}' has been won by $winner_username with a bid of ₱" . number_format($winning_bid_amount, 2);
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $auction['seller_id'], $seller_message);
        $stmt->execute();
    } else {
        echo "No bids were placed in this auction.";
    }
} else {
    echo "Auction is still running. Remaining time: $remaining_time seconds.";
}

// Handle bidding logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bid_amount = $_POST['bid_amount'];
    $buyer_id = $_SESSION['user_id'];

    if ($bid_amount > $auction['current_price']) {
        // Insert the new bid
        $stmt = $conn->prepare("INSERT INTO bids (auction_id, user_id, bid_amount, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iid", $auction_id, $buyer_id, $bid_amount);

        if ($stmt->execute()) {
            // Update current price
            $conn->query("UPDATE auctions SET current_price = $bid_amount WHERE id = $auction_id");

            // Notify the previous highest bidder (if any)
            $previous_highest_bid = $conn->query("SELECT b.bid_amount, u.id, u.username, u.email
                FROM bids b
                JOIN users u ON b.user_id = u.id
                WHERE b.auction_id = $auction_id
                ORDER BY b.bid_amount DESC LIMIT 1 OFFSET 1");
            if ($previous_highest_bid->num_rows > 0) {
                $previous_bidder = $previous_highest_bid->fetch_assoc();
                $message = "You have been outbid on the auction '{$auction['title']}' with your bid of ₱" . number_format($previous_bidder['bid_amount'], 2);
                
                // Send notification to the previous highest bidder
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
                $stmt->bind_param("is", $previous_bidder['id'], $message);
                $stmt->execute();
            }

            // Redirect buyer to the dashboard
            header("Location: dashboard_buyer.php");
        }
    } else {
        echo "Bid must be higher than the current price.";
    }
}

if ($remaining_time <= 0) {
    // Existing code to determine the highest bidder
    if ($highest_bid_query->num_rows > 0) {
        $highest_bid = $highest_bid_query->fetch_assoc();
        $winner_id = $_SESSION['user_id'];
        $winning_bid_amount = $highest_bid['bid_amount'];

        // Save to won_auctions table
        $stmt = $conn->prepare("INSERT INTO won_auctions (user_id, auction_id, product_name, product_image, winning_bid) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissd", $winner_id, $auction_id, $auction['title'], $auction['image_path'], $winning_bid_amount);
        $stmt->execute();
    }
}

// Fetch all bids placed on the auction
$bids_query = $conn->query("SELECT b.bid_amount, u.username, b.created_at 
    FROM bids b
    JOIN users u ON b.user_id = u.id
    WHERE b.auction_id = $auction_id
    ORDER BY b.created_at DESC");
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Item</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script>
        function startCountdown(remainingTimeInSeconds, elementId) {
            const countdownInterval = setInterval(function () {
                if (remainingTimeInSeconds <= 0) {
                    clearInterval(countdownInterval);
                    document.getElementById(elementId).innerHTML = "Auction Ended";
                    document.getElementById(elementId).classList.add('text-gray-500');
                } else {
                    const hours = Math.floor(remainingTimeInSeconds / 3600);
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
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }
        .sidebar {
            background-color: #2563eb; /* Blue-600 */
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 250px;
            padding-top: 40px;
            box-shadow: 2px 0 6px rgba(0, 0, 0, 0.1);
        }
        .sidebar a {
            color: #ffffff;
            padding: 15px;
            display: block;
            font-size: 1.1rem;
            transition: background-color 0.3s;
        }
        .sidebar a:hover {
            background-color: #4f9ef7;
            color: #f1faee;
        }
        .header {
            color: #2563eb;
        }
        .button {
            background: linear-gradient(90deg, #4f9ef7, #2563eb);
        }
        .button:hover {
            background: linear-gradient(90deg, #2563eb, #4f9ef7);
        }
        /* Custom styles for countdown and prices */
        .countdown {
            font-size: 1.25rem;
            font-weight: bold;
            padding: 5px 10px;
            color: #1e3a8a; /* Dark blue text */
            border-radius: 5px;
        }

        .price {
            font-size: 1.25rem;
            font-weight: bold;
        }

        .starting-price {
            color: #4caf50; /* Green for the starting price */
        }

        .current-price {
            color: #dc2626; /* Red for the current price */
        }

        /* Fade-In Animation */
        .fade-in {
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                visibility: hidden;
            }
            100% {
                opacity: 1;
                visibility: visible;
            }
        }

    </style>
</head>
<body class="bg-gray-100">
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
        <div class="flex-1 ml-64 p-8 fade-in flex space-x-6">
            <div class="flex-1 w-2/3 bg-white shadow-lg rounded-lg p-6">
                <!-- Auction Details -->
                <h1 class="text-4xl font-bold mb-4 header"><?php echo $auction['title']; ?></h1>
                <img src="<?php echo $auction['image_path']; ?>" alt="Auction Image" class="w-full h-80 object-cover mb-6 rounded-lg shadow-md">
                <p class="mb-4"><?php echo $auction['description']; ?></p>
                
                <!-- Display Current Price and Starting Price -->
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xl font-semibold current-price">Current Price: ₱<?php echo number_format($auction['current_price'], 2); ?></span>
                    <span class="text-lg font-semibold starting-price">Starting Price: ₱<?php echo number_format($auction['starting_price'], 2); ?></span>
                </div>

                <!-- Countdown Timer -->
                <div class="countdown mb-4">
                    <span>Remaining Time: <span id="auction-time"></span></span>
                </div>
                <script>
                    startCountdown(<?php echo $remaining_time; ?>, "auction-time");
                </script>

                <!-- Bid Form -->
                <form method="POST" class="mt-6">
                    <label for="bid_amount" class="block text-xl font-semibold mb-2">Your Bid</label>
                    <input type="number" name="bid_amount" id="bid_amount" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your bid" required>
                    <button type="submit" class="mt-4 w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Place Bid</button>
                </form>
            </div>

            <!-- Bid History -->
            <div class="w-1/3 bg-white shadow-lg rounded-lg p-6">
                <h2 class="text-2xl font-semibold mt-8 mb-4">Bid History</h2>
                <ul class="space-y-4">
                    <?php while ($bid = $bids_query->fetch_assoc()): ?>
                        <li class="flex justify-between items-center">
                            <span><?php echo $bid['username']; ?> - ₱<?php echo number_format($bid['bid_amount'], decimals: 2); ?></span>
                            <span class="text-sm text-gray-500"><?php echo date('M d, Y H:i', timestamp: strtotime($bid['created_at'])); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
</body>
</html>
