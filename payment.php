<?php
session_start();
require_once 'config/database.php';

// Validate input
if (!isset($_GET['ride_id'], $_GET['seats'], $_GET['total_price'])) {
    header('Location: find-ride.php?error=Missing required inputs');
    exit();
}

$ride_id = (int)$_GET['ride_id'];
$seats = (int)$_GET['seats'];
$total_price = (float)$_GET['total_price'];

// Get ride details
$stmt = $pdo->prepare("SELECT * FROM rides WHERE id = ?");
$stmt->execute([$ride_id]);
$ride = $stmt->fetch();

if (!$ride || $seats < 1 || $seats > $ride['available_seats']) {
    header('Location: find-ride.php?error=Invalid booking details');
    exit();
}

// Calculate total price (recalculate for security)
$calculated_total_price = $ride['price_per_seat'] * $seats;
if ($calculated_total_price !== $total_price) {
    header('Location: find-ride.php?error=Price mismatch');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - CabShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-xl mx-auto mt-12 bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Complete Your Payment</h2>
        <p class="mb-2">Ride: <?php echo htmlspecialchars($ride['source']) . ' → ' . htmlspecialchars($ride['destination']); ?></p>
        <p class="mb-2">Seats: <?php echo $seats; ?></p>
        <p class="mb-4 text-lg font-bold text-green-600">Total: ₹<?php echo number_format($calculated_total_price, 2); ?></p>

        <!-- Simulated payment button -->
        <form action="confirm-booking.php" method="POST">
            <input type="hidden" name="ride_id" value="<?php echo $ride_id; ?>">
            <input type="hidden" name="seats" value="<?php echo $seats; ?>">
            <input type="hidden" name="total_price" value="<?php echo $calculated_total_price; ?>">
            <button type="submit" class="bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-semibold py-2 px-6 rounded">
                Pay & Confirm Booking
            </button>
        </form>
    </div>
</body>
</html>
