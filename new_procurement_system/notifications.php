<?php
session_start();
require 'config.php';

$user_id = $_SESSION['user_id'];

// Get notifications for the logged-in user
$notifications = $conn->query("
    SELECT * FROM notifications 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .fade-in {
            animation: fadeIn 1s ease-in-out;
        }

        .slide-in {
            animation: slideIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-Poppins transition-colors duration-300">

    <div class="flex">
        <!-- Sidebar -->
        <aside class="bg-blue-600 text-white w-64 p-6 fixed inset-y-0 left-0 transition-all duration-300 slide-in">
            <h1 class="text-2xl font-bold mb-6">PBS</h1>
            <ul class="space-y-4">
                <li><a href="dashboard_buyer.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded transition duration-300 ease-in-out">
                    <i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li><a href="product_list.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded transition duration-300 ease-in-out">
                    <i class="fas fa-list"></i><span>Product lists</span></a></li>
                <li><a href="notifications.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded transition duration-300 ease-in-out">
                    <i class="fas fa-bell"></i><span>Notifications</span></a></li>
                <li><a href="bids.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded transition duration-300 ease-in-out">
                    <i class="fas fa-gavel"></i><span>My bids</span></a></li>
                <li><a href="logout.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded transition duration-300 ease-in-out">
                    <i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="ml-64 flex-1 p-8 fade-in">
            <h1 class="text-3xl font-semibold text-center mb-6 text-blue-600">My Notifications</h1>

            <?php while ($notification = $notifications->fetch_assoc()): ?>
                <div class="border p-6 rounded-xl shadow-lg mb-4 <?php echo $notification['is_read'] ? 'bg-gray-100' : 'bg-white-100'; ?> transition duration-300 ease-in-out hover:shadow-2xl hover:scale-105">
                    <p class="text-lg font-medium"><?php echo $notification['message']; ?></p>
                    <p class="text-sm text-gray-500 mt-2">Received on: <?php echo date('F j, Y, g:i a', strtotime($notification['created_at'])); ?></p>

                    <?php if (!$notification['is_read']): ?>
                        <div class="mt-4">
                            <a href="notifications.php?notification_id=<?php echo $notification['id']; ?>" class="text-blue-500 hover:text-blue-700 font-semibold transition duration-300 ease-in-out transform hover:scale-105">
                                Mark as Read
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>

            <?php if ($notifications->num_rows == 0): ?>
                <div class="text-center text-gray-600 mt-8">
                    <p>No new notifications.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
