<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get booking ID from URL
$booking_id = $_GET['id'] ?? null;
if (!$booking_id) {
    header('Location: bookings.php');
    exit();
}

$error = '';
$success = '';

// Handle booking status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $booking_id]);
        $success = 'Booking status updated successfully';
    } catch (PDOException $e) {
        $error = 'Failed to update booking status';
    }
}

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, 
           u.name as passenger_name,
           u.phone as passenger_phone,
           u.email as passenger_email,
           r.source,
           r.destination,
           r.departure_time,
           r.price_per_seat,
           r.status as ride_status,
           r.seats_available,
           d.name as driver_name,
           d.phone as driver_phone,
           d.car_model,
           d.car_number
    FROM bookings b
    JOIN users u ON b.passenger_id = u.id
    JOIN rides r ON b.ride_id = r.id
    JOIN users d ON r.driver_id = d.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: bookings.php');
    exit();
}

// Get reviews for this booking
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.name as reviewer_name,
           u2.name as reviewed_name
    FROM reviews r
    JOIN users u ON r.reviewer_id = u.id
    JOIN users u2 ON r.reviewed_id = u2.id
    WHERE r.ride_id = ? AND (r.reviewer_id = ? OR r.reviewed_id = ?)
    ORDER BY r.created_at DESC
");
$stmt->execute([$booking['ride_id'], $booking['passenger_id'], $booking['passenger_id']]);
$reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - CabShare Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Admin Navbar -->
        <nav class="bg-gray-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <a href="../index.php" class="text-xl font-bold">CabShare</a>
                        </div>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4">
                                <a href="index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                                <a href="users.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Users</a>
                                <a href="rides.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Rides</a>
                                <a href="bookings.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Bookings</a>
                                <a href="reviews.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Reviews</a>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-300 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
                        <a href="../logout.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Booking Details</h1>
                    <div class="flex space-x-4">
                        <a href="bookings.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Bookings
                        </a>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Booking Information -->
                    <div class="lg:col-span-1">
                        <div class="bg-white shadow rounded-lg p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-ticket-alt text-5xl text-gray-400"></i>
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-lg font-medium text-gray-900">Booking #<?php echo $booking['id']; ?></h2>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            <?php echo $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                                ($booking['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                ($booking['status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')); ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Booking Date</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?>
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Seats Booked</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php echo $booking['seats_booked']; ?> seat(s)
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Total Price</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        ₹<?php echo number_format($booking['total_price'], 2); ?>
                                    </p>
                                </div>

                                <form method="POST" class="pt-4">
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700">Update Status</label>
                                        <select name="status" id="status"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                            <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" name="update_status"
                                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            Update Status
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger Information -->
                    <div class="lg:col-span-1">
                        <div class="bg-white shadow rounded-lg p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user text-5xl text-gray-400"></i>
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-lg font-medium text-gray-900">Passenger</h2>
                                    <p class="mt-1 text-sm text-gray-500">
                                        <?php echo htmlspecialchars($booking['passenger_name']); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Contact Information</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($booking['passenger_phone']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($booking['passenger_email']); ?>
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Booking History</h3>
                                    <?php
                                    $stmt = $pdo->prepare("
                                        SELECT COUNT(*) as total_bookings,
                                               SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings
                                        FROM bookings
                                        WHERE passenger_id = ?
                                    ");
                                    $stmt->execute([$booking['passenger_id']]);
                                    $history = $stmt->fetch();
                                    ?>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php echo $history['total_bookings']; ?> total bookings
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo $history['completed_bookings']; ?> completed
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ride Information -->
                    <div class="lg:col-span-1">
                        <div class="bg-white shadow rounded-lg p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-car text-5xl text-gray-400"></i>
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-lg font-medium text-gray-900">Ride Details</h2>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            <?php echo $booking['ride_status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                                ($booking['ride_status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo ucfirst($booking['ride_status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Route</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($booking['source']); ?> → <?php echo htmlspecialchars($booking['destination']); ?>
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Departure Time</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php echo date('M d, Y H:i', strtotime($booking['departure_time'])); ?>
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Price per Seat</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        ₹<?php echo number_format($booking['price_per_seat'], 2); ?>
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Driver</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($booking['driver_name']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($booking['driver_phone']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($booking['car_model']); ?> (<?php echo htmlspecialchars($booking['car_number']); ?>)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div class="lg:col-span-3">
                        <div class="bg-white shadow rounded-lg p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Reviews</h2>
                            <?php if (empty($reviews)): ?>
                                <p class="text-gray-600">No reviews yet</p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($reviews as $review): ?>
                                        <div class="border-b border-gray-200 pb-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-semibold">
                                                        <?php echo htmlspecialchars($review['reviewer_name']); ?> → 
                                                        <?php echo htmlspecialchars($review['reviewed_name']); ?>
                                                    </p>
                                                    <div class="text-yellow-500">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <span class="text-sm text-gray-500">
                                                    <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                                </span>
                                            </div>
                                            <p class="mt-2 text-gray-600"><?php echo htmlspecialchars($review['comment']); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>