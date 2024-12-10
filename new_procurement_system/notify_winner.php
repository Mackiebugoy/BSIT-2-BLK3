<?php
session_start();
require 'config.php';

// Get the current time
$current_time = date("Y-m-d H:i:s");

// Query to find auctions that are closed and have ended
$auctions = $conn->query("
    SELECT a.id, a.seller_id, a.title, b.user_id AS winner_id, b.bid_amount 
    FROM auctions a
    JOIN bids b ON a.id = b.auction_id
    WHERE a.ending_time < '$current_time' AND a.status = 'approved'
    GROUP BY a.id
    HAVING b.bid_amount = (SELECT MAX(bid_amount) FROM bids WHERE auction_id = a.id)
");

while ($auction = $auctions->fetch_assoc()) {
    $auction_id = $auction['id'];
    $winner_id = $auction['winner_id'];
    $auction_title = $auction['title'];
    $winner_bid = $auction['bid_amount'];

    // Insert a notification for the winner
    $message = "Congratulations! You won the auction for '{$auction_title}' with a bid of {$winner_bid}.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $winner_id, $message);
    $stmt->execute();

    // Optionally, update the auction's status to 'closed'
    $conn->query("UPDATE auctions SET status = 'closed' WHERE id = $auction_id");
}

echo "Notifications sent to auction winners.";
?>
