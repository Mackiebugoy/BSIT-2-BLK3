<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all approved auctions that are not yet expired
$auctions = $conn->query("
    SELECT id, title, description, starting_price, current_price, end_time, image_path 
    FROM auctions 
    WHERE status = 'approved' AND end_time > NOW()
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Active Auctions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="max-w-5xl mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Active Auctions</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php while ($auction = $auctions->fetch_assoc()): ?>
                <div class="border rounded p-4 shadow-md">
                    <img src="<?php echo $auction['image_path']; ?>" alt="Auction Image" class="w-full h-32 object-cover rounded">
                    <h2 class="text-lg font-bold mt-2"><?php echo $auction['title']; ?></h2>
                    <p class="text-sm mt-1"><?php echo $auction['description']; ?></p>
                    <p class="text-sm mt-1">Starting Price: ₱<?php echo $auction['starting_price']; ?></p>
                    <p class="text-sm mt-1">Current Price: ₱<?php echo $auction['current_price']; ?></p>
                    <p class="text-sm font-bold mt-1 text-red-500" id="timer-<?php echo $auction['id']; ?>">
                        <!-- Timer will be updated here -->
                    </p>
                    <a href="view_item.php?id=<?php echo $auction['id']; ?>" class="mt-2 inline-block bg-green-500 text-white px-4 py-2 rounded">
                        View Auction
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        // JavaScript to handle auction timers
        const auctions = <?php
            echo json_encode($auctions->fetch_all(MYSQLI_ASSOC));
        ?>;

        auctions.forEach(auction => {
            const timerElement = document.getElementById(`timer-${auction.id}`);
            const endTime = new Date(auction.end_time).getTime();

            function updateTimer() {
                const now = new Date().getTime();
                const remaining = endTime - now;

                if (remaining <= 0) {
                    timerElement.textContent = "Auction Ended";
                } else {
                    const days = Math.floor(remaining / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((remaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((remaining % (1000 * 60)) / 1000);

                    timerElement.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                }
            }

            setInterval(updateTimer, 1000);
            updateTimer();
        });
    </script>
</body>
</html>
