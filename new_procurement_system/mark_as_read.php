<?php
session_start();
require 'config.php';

// If notification_id is set, mark it as read
if (isset($_GET['notification_id'])) {
    $notification_id = $_GET['notification_id'];
    $conn->query("UPDATE notifications SET is_read = TRUE WHERE id = $notification_id");

    // Redirect back to the notifications page
    header("Location: notifications.php");
    exit();
}
?>
