<?php
require 'config.php';

// Hash Password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Check if user exists
function userExists($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Register user
function registerUser($email, $username, $password, $role) {
    global $conn;
    $hashedPassword = hashPassword($password);

    if (userExists($email)) {
        return "User with this email already exists.";
    }

    $stmt = $conn->prepare("INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $email, $username, $hashedPassword, $role);
    $stmt->execute();
    
    return $stmt->affected_rows > 0;
}

// Verify user login
function verifyUserLogin($email, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
    }

    return false;
}

// Get user profile data
function getUserProfile($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Create a new auction
function createAuction($title, $description, $startingPrice, $auctionDuration, $imagePath, $sellerId) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO auctions (title, description, starting_price, auction_duration, image_path, seller_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param('ssdsis', $title, $description, $startingPrice, $auctionDuration, $imagePath, $sellerId);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

// Get Auctions by Status
function getAuctionsByStatus($status) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM auctions WHERE status = ?");
    $stmt->bind_param('s', $status);
    $stmt->execute();
    return $stmt->get_result();
}

// Place a bid on an auction
function placeBid($auctionId, $userId, $bidAmount) {
    global $conn;
    // Get the current highest bid for validation
    $stmt = $conn->prepare("SELECT current_price FROM auctions WHERE id = ?");
    $stmt->bind_param('i', $auctionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $auction = $result->fetch_assoc();

    if ($bidAmount > $auction['current_price']) {
        $stmt = $conn->prepare("INSERT INTO bids (auction_id, user_id, bid_amount) VALUES (?, ?, ?)");
        $stmt->bind_param('iii', $auctionId, $userId, $bidAmount);
        $stmt->execute();
        
        // Update the auction's current price
        $stmt = $conn->prepare("UPDATE auctions SET current_price = ? WHERE id = ?");
        $stmt->bind_param('di', $bidAmount, $auctionId);
        $stmt->execute();

        return true;
    }

    return false;
}

function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes      = round($seconds / 60);
    $hours        = round($seconds / 3600);
    $days         = round($seconds / 86400);
    $weeks        = round($seconds / 604800);
    $months       = round($seconds / 2629440);
    $years        = round($seconds / 31553280);

    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        if ($minutes == 1) {
            return "one minute ago";
        } else {
            return "$minutes minutes ago";
        }
    } else if ($hours <= 24) {
        if ($hours == 1) {
            return "an hour ago";
        } else {
            return "$hours hours ago";
        }
    } else if ($days <= 7) {
        if ($days == 1) {
            return "yesterday";
        } else {
            return "$days days ago";
        }
    } else if ($weeks <= 4.3) {
        if ($weeks == 1) {
            return "one week ago";
        } else {
            return "$weeks weeks ago";
        }
    } else if ($months <= 12) {
        if ($months == 1) {
            return "one month ago";
        } else {
            return "$months months ago";
        }
    } else {
        if ($years == 1) {
            return "one year ago";
        } else {
            return "$years years ago";
        }
    }
}


?>
