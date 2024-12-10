<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}

$won_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the won auction details
$stmt = $conn->prepare("SELECT * FROM won_auctions WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $won_id, $user_id);
$stmt->execute();
$won_auction = $stmt->get_result()->fetch_assoc();

if (!$won_auction) {
    echo "Invalid auction or unauthorized access.";
    exit();
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Placeholder for payment processing logic
    $payment_method = $_POST['payment_method'];
    $status = "Paid";

    // Update the status of the won auction
    $update_stmt = $conn->prepare("UPDATE won_auctions SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $status, $won_id);
    if ($update_stmt->execute()) {
        echo "<script>alert('Payment successful!'); window.location.href = 'won_auctions.php';</script>";
        exit();
    } else {
        echo "<script>alert('Payment failed. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-4">Payment for <?php echo $won_auction['product_name']; ?></h1>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <img src="<?php echo $won_auction['product_image']; ?>" alt="<?php echo $won_auction['product_name']; ?>" class="w-full h-40 object-cover mb-6 rounded-lg">
            <p class="text-xl font-semibold mb-4">Winning Bid: ₱<?php echo number_format($won_auction['winning_bid'], 2); ?></p>
            
            <form method="POST">
                <label for="payment_method" class="block text-lg font-medium mb-2">Choose Payment Method:</label>
                <select name="payment_method" id="payment_method" class="w-full p-2 border border-gray-300 rounded-lg mb-4">
                    <option value="credit_card">Credit Card</option>
                    <option value="paypal">PayPal</option>
                    <option value="gcash">GCash</option>
                </select>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Pay Now</button>
            </form>
        </div>
    </div>
</body>
</html>
