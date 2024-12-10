<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $auction_id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE auctions SET status = 'approved' WHERE id = ?");
    $stmt->bind_param('i', $auction_id);
    $stmt->execute();
}

header('Location: manage_auctions.php');
exit();
?>
