<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$auctions = $conn->query("SELECT * FROM auctions");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Auctions</title>
    <!-- Link to Google Fonts for Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        /* Sidebar animation */
        aside {
            transform: translateX(-100%);
            transition: transform 0.5s ease-in-out;
            animation: slideIn 0.5s ease-in-out forwards; /* Added slide-in animation */
        }

        /* Animation to slide in sidebar */
        @keyframes slideIn {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(0);
            }
        }

        /* Smooth fade-in effect for main content */
        main {
            animation: fadeIn 0.5s ease-in-out;
        }

        /* Keyframes for fade-in animation */
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        /* Hover effects on table rows */
        tr {
            transition: background-color 0.3s ease;
        }

        tr:hover {
            background-color: #f3f4f6; /* Light gray hover effect */
        }

        /* Optional: Sidebar open animation for dynamic interactions */
        aside.open {
            transform: translateX(0);
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-800 font-poppins">

    <!-- Sidebar -->
    <aside class="bg-blue-600 text-white w-64 p-6 fixed inset-y-0 left-0">
        <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>
        <ul class="space-y-4">
            <li><a href="dashboard_admin.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li><a href="manage_users.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-users"></i><span>Manage Users</span></a></li>
            <li><a href="manage_auctions.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-clipboard-list"></i><span>Manage Auctions</span></a></li>
            <li><a href="profile.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-user"></i><span>Profile</span></a></li>
            <li><a href="logout.php" class="flex items-center space-x-4 hover:bg-blue-500 px-4 py-2 rounded">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- Main content -->
    <main class="flex-1 ml-64 p-6">
        <!-- Title -->
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-gray-200 mb-6">Manage Auctions</h1>

        <!-- Auctions Table -->
        <div class="overflow-x-auto bg-white dark:bg-gray-700 p-6 rounded-lg shadow-lg">
            <table class="w-full table-auto text-left border-collapse border border-gray-300">
                <thead>
                    <tr>
                        <th class="border p-3 text-sm text-gray-700 dark:text-gray-300">Title</th>
                        <th class="border p-3 text-sm text-gray-700 dark:text-gray-300">Seller</th>
                        <th class="border p-3 text-sm text-gray-700 dark:text-gray-300">Starting Price</th>
                        <th class="border p-3 text-sm text-gray-700 dark:text-gray-300">Status</th>
                        <th class="border p-3 text-sm text-gray-700 dark:text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($auction = $auctions->fetch_assoc()): ?>
                        <tr class="transition-all duration-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                            <td class="border p-3"><?php echo $auction['title']; ?></td>
                            <td class="border p-3"><?php echo $auction['seller_id']; ?></td>
                            <td class="border p-3"><?php echo $auction['starting_price']; ?></td>
                            <td class="border p-3"><?php echo ucfirst($auction['status']); ?></td>
                            <td class="border p-3">
                                <?php if ($auction['status'] === 'pending'): ?>
                                    <a href="approve_auction.php?id=<?php echo $auction['id']; ?>" 
                                       class="text-green-500 hover:text-green-700 font-medium transition-colors">Approve</a> | 
                                <?php endif; ?>
                                <a href="delete_auction.php?id=<?php echo $auction['id']; ?>" 
                                   class="text-red-500 hover:text-red-700 font-medium transition-colors">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // For toggling sidebar (Optional)
        const toggleSidebar = () => {
            document.querySelector('aside').classList.toggle('open');
        }

        // Call this function to open the sidebar, if needed
        toggleSidebar();
    </script>
</body>
</html>
