<?php 
session_start();
require 'config.php';

$user_id = $_SESSION['user_id'];

// Fetch won auctions for the logged-in user
$won_auctions = $conn->query("SELECT wa.*, u.username AS seller_username 
                              FROM won_auctions wa
                              JOIN users u ON wa.email = u.email
                              WHERE wa.user_id = $user_id");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Won Auctions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Add styles as needed */
    </style>
</head>
<body class="bg-gray-100">
    <div class="p-6">
        <h1 class="text-3xl font-bold mb-6">My Won Auctions</h1>
        
        <?php while ($auction = $won_auctions->fetch_assoc()): ?>
            <div class="bg-white p-6 rounded-lg shadow-md mb-4">
                <h2 class="text-2xl font-semibold"><?php echo $auction['product_name']; ?></h2>
                <img src="<?php echo $auction['product_image']; ?>" alt="Auction Item" class="w-full h-64 object-cover mb-4">
                <p><strong>Bid Amount:</strong> ₱<?php echo number_format($auction['winning_bid'], 2); ?></p>
                <p><strong>Seller:</strong> <?php echo $auction['seller_username']; ?></p>
                
                <form method="POST" action="payment.php" class="mt-4">
                    <input type="hidden" name="auction_id" value="<?php echo $auction['auction_id']; ?>">
                    <button type="submit" class="w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Proceed to Payment</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
