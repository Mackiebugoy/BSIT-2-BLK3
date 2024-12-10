<?php
require 'config.php';

// Fetch expired auctions that are still marked as "approved"
$expired_auctions = $conn->query("
    SELECT id, highest_bidder_id, current_price 
    FROM auctions 
    WHERE end_time <= NOW() AND status = 'approved'
");

while ($auction = $expired_auctions->fetch_assoc()) {
    $id = $auction['id'];
    $winner_id = $auction['highest_bidder_id'];

    // Update the auction status to "ended"
    $stmt = $conn->prepare("UPDATE auctions SET status = 'ended' WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    // Notify the winner (if any)
    if ($winner_id) {
        // You can add a notification system here
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, message) 
            VALUES (?, ?)
        ");
        $message = "Congratulations! You won the auction with ID $id at a price of ₱{$auction['current_price']}.";
        $stmt->bind_param('is', $winner_id, $message);
        $stmt->execute();
    }
}
?>
