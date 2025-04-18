<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get ride ID from URL
$ride_id = $_GET['id'] ?? null;
if (!$ride_id) {
    header('Location: rides.php');
    exit();
}

$error = '';
$success = '';

// Handle ride status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE rides SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $ride_id]);
        $success = 'Ride status updated successfully';
    } catch (PDOException $e) {
        $error = 'Failed to update ride status';
    }
}

// Get ride details
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.name as driver_name,
           u.phone as driver_phone,
           u.car_model,
           u.car_number,
           (SELECT COUNT(*) FROM bookings WHERE ride_id = r.id) as bookings_count,
           (SELECT COUNT(*) FROM bookings WHERE ride_id = r.id AND status = 'completed') as completed_bookings
    FROM rides r
    JOIN users u ON r.driver_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$ride_id]);
$ride = $stmt->fetch();

if (!$ride) {
    header('Location: rides.php');
    exit();
}

// Get bookings for this ride
$stmt = $pdo->prepare("
    SELECT b.*, 
           u.name as passenger_name,
           u.phone as passenger_phone,
           u.email as passenger_email
    FROM bookings b
    JOIN users u ON b.passenger_id = u.id
    WHERE b.ride_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$ride_id]);
$bookings = $stmt->fetchAll();

// Get reviews for this ride
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.name as reviewer_name,
           u2.name as reviewed_name
    FROM reviews r
    JOIN users u ON r.reviewer_id = u.id
    JOIN users u2 ON r.reviewed_id = u2.id
    WHERE r.ride_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$ride_id]);
$reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ride Details - CabShare Admin</title>
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
                                <a href="rides.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Rides</a>
                                <a href="bookings.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Bookings</a>
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
                    <h1 class="text-2xl font-semibold text-gray-900">Ride Details</h1>
                    <div class="flex space-x-4">
                        <a href="rides.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Rides
                        </a>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-[#8B5CF6] bg-opacity-10 border-l-4 border-[#8B5CF6] p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-[#8B5CF6]"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-[#8B5CF6]">
                                    <?php echo $_SESSION['success']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Ride Information -->
                    <div class="lg:col-span-1">
                        <div class="bg-white shadow rounded-lg p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-car text-5xl text-gray-400"></i>
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-lg font-medium text-gray-900">Ride #<?php echo $ride['id']; ?></h2>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            <?php echo $ride['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                                ($ride['status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo ucfirst($ride['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Route</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($ride['source']); ?> → <?php echo htmlspecialchars($ride['destination']); ?>
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Departure Time</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php echo date('M d, Y H:i', strtotime($ride['departure_time'])); ?>
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Price per Seat</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        ₹<?php echo number_format($ride['price_per_seat'], 2); ?>
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Seats</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php echo $ride['bookings_count']; ?> / <?php echo $ride['seats_available']; ?> booked
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Driver</h3>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($ride['driver_name']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($ride['driver_phone']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($ride['car_model']); ?> (<?php echo htmlspecialchars($ride['car_number']); ?>)
                                    </p>
                                </div>

                                <form method="POST" class="pt-4">
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700">Update Status</label>
                                        <select name="status" id="status"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                            <option value="active" <?php echo $ride['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="completed" <?php echo $ride['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $ride['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" name="update_status"
                                            class="bg-[#8B5CF6] text-white px-4 py-2 rounded hover:bg-[#7C3AED] transition-colors">
                                            Update Status
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Bookings -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white shadow rounded-lg p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Bookings</h2>
                            <?php if (empty($bookings)): ?>
                                <p class="text-gray-600">No bookings yet</p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($bookings as $booking): ?>
                                        <div class="border-b border-gray-200 pb-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-semibold"><?php echo htmlspecialchars($booking['passenger_name']); ?></p>
                                                    <p class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($booking['passenger_phone']); ?> • 
                                                        <?php echo htmlspecialchars($booking['passenger_email']); ?>
                                                    </p>
                                                    <p class="text-sm text-gray-500">
                                                        <?php echo $booking['seats_booked']; ?> seat(s) • 
                                                        ₹<?php echo number_format($booking['total_price'], 2); ?>
                                                    </p>
                                                </div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    <?php echo $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                                        ($booking['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Reviews -->
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