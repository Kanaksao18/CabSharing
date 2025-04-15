<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get user ID from URL
$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header('Location: users.php');
    exit();
}

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $car_model = trim($_POST['car_model']);
    $car_number = trim($_POST['car_number']);
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, car_model = ?, car_number = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $car_model, $car_number, $user_id]);
        $success = 'Profile updated successfully';
    } catch (PDOException $e) {
        $error = 'Failed to update profile';
    }
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: users.php');
    exit();
}

// Get user statistics
$stats = [
    'rides_offered' => $pdo->prepare("SELECT COUNT(*) FROM rides WHERE driver_id = ?")->execute([$user_id])->fetchColumn(),
    'rides_booked' => $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE passenger_id = ?")->execute([$user_id])->fetchColumn(),
    'completed_rides' => $pdo->prepare("SELECT COUNT(*) FROM rides WHERE driver_id = ? AND status = 'completed'")->execute([$user_id])->fetchColumn(),
    'completed_bookings' => $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE passenger_id = ? AND status = 'completed'")->execute([$user_id])->fetchColumn(),
    'average_rating' => $pdo->prepare("SELECT AVG(rating) FROM reviews WHERE reviewed_id = ?")->execute([$user_id])->fetchColumn()
];

// Get recent rides (as driver)
$stmt = $pdo->prepare("
    SELECT r.*, 
           (SELECT COUNT(*) FROM bookings WHERE ride_id = r.id) as bookings_count,
           (SELECT COUNT(*) FROM bookings WHERE ride_id = r.id AND status = 'completed') as completed_bookings
    FROM rides r 
    WHERE r.driver_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_rides = $stmt->fetchAll();

// Get recent bookings (as passenger)
$stmt = $pdo->prepare("
    SELECT b.*, r.source, r.destination, r.departure_time, u.name as driver_name
    FROM bookings b
    JOIN rides r ON b.ride_id = r.id
    JOIN users u ON r.driver_id = u.id
    WHERE b.passenger_id = ?
    ORDER BY b.created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_bookings = $stmt->fetchAll();

// Get recent reviews
$stmt = $pdo->prepare("
    SELECT r.*, u.name as reviewer_name 
    FROM reviews r 
    JOIN users u ON r.reviewer_id = u.id 
    WHERE r.reviewed_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - CabShare Admin</title>
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
                                <a href="users.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Users</a>
                                <a href="rides.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Rides</a>
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
                    <h1 class="text-2xl font-semibold text-gray-900">User Details</h1>
                    <div class="flex space-x-4">
                        <a href="users.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Users
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
                    <!-- Profile Information -->
                    <div class="lg:col-span-1">
                        <div class="bg-white shadow rounded-lg p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0 h-16 w-16">
                                    <i class="fas fa-user-circle text-5xl text-gray-400"></i>
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></h2>
                                    <p class="text-sm text-gray-500">ID: <?php echo $user['id']; ?></p>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                                ($user['role'] === 'driver' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ml-2
                                            <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                </div>

                                <?php if ($user['role'] === 'driver'): ?>
                                    <div>
                                        <label for="car_model" class="block text-sm font-medium text-gray-700">Car Model</label>
                                        <input type="text" id="car_model" name="car_model" value="<?php echo htmlspecialchars($user['car_model'] ?? ''); ?>"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    </div>

                                    <div>
                                        <label for="car_number" class="block text-sm font-medium text-gray-700">Car Number</label>
                                        <input type="text" id="car_number" name="car_number" value="<?php echo htmlspecialchars($user['car_number'] ?? ''); ?>"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    </div>
                                <?php endif; ?>

                                <div class="flex justify-end">
                                    <button type="submit" name="update_profile"
                                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Statistics -->
                        <div class="bg-white shadow rounded-lg p-6 mt-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Statistics</h2>
                            <div class="space-y-4">
                                <?php if ($user['role'] === 'driver'): ?>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Rides Offered</span>
                                        <span class="font-semibold"><?php echo $stats['rides_offered']; ?></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Completed Rides</span>
                                        <span class="font-semibold"><?php echo $stats['completed_rides']; ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($user['role'] === 'passenger'): ?>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Rides Booked</span>
                                        <span class="font-semibold"><?php echo $stats['rides_booked']; ?></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Completed Bookings</span>
                                        <span class="font-semibold"><?php echo $stats['completed_bookings']; ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Average Rating</span>
                                    <div class="flex items-center">
                                        <span class="font-semibold mr-2"><?php echo number_format($stats['average_rating'], 1); ?></span>
                                        <div class="text-yellow-500">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= round($stats['average_rating']) ? '' : '-o'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity History -->
                    <div class="lg:col-span-2 space-y-6">
                        <?php if ($user['role'] === 'driver'): ?>
                            <!-- Recent Rides -->
                            <div class="bg-white shadow rounded-lg p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Rides</h2>
                                <?php if (empty($recent_rides)): ?>
                                    <p class="text-gray-600">No rides offered yet</p>
                                <?php else: ?>
                                    <div class="space-y-4">
                                        <?php foreach ($recent_rides as $ride): ?>
                                            <div class="border-b border-gray-200 pb-4">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="font-semibold">
                                                            <?php echo htmlspecialchars($ride['source']); ?> → <?php echo htmlspecialchars($ride['destination']); ?>
                                                        </p>
                                                        <p class="text-sm text-gray-500">
                                                            <?php echo date('M d, Y', strtotime($ride['departure_time'])); ?> • 
                                                            <?php echo $ride['bookings_count']; ?> bookings
                                                        </p>
                                                    </div>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                        <?php echo $ride['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                                            ($ride['status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'); ?>">
                                                        <?php echo ucfirst($ride['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($user['role'] === 'passenger'): ?>
                            <!-- Recent Bookings -->
                            <div class="bg-white shadow rounded-lg p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Bookings</h2>
                                <?php if (empty($recent_bookings)): ?>
                                    <p class="text-gray-600">No bookings yet</p>
                                <?php else: ?>
                                    <div class="space-y-4">
                                        <?php foreach ($recent_bookings as $booking): ?>
                                            <div class="border-b border-gray-200 pb-4">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="font-semibold">
                                                            <?php echo htmlspecialchars($booking['source']); ?> → <?php echo htmlspecialchars($booking['destination']); ?>
                                                        </p>
                                                        <p class="text-sm text-gray-500">
                                                            <?php echo htmlspecialchars($booking['driver_name']); ?> • 
                                                            <?php echo date('M d, Y', strtotime($booking['departure_time'])); ?>
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
                        <?php endif; ?>

                        <!-- Recent Reviews -->
                        <div class="bg-white shadow rounded-lg p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Reviews</h2>
                            <?php if (empty($recent_reviews)): ?>
                                <p class="text-gray-600">No reviews yet</p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($recent_reviews as $review): ?>
                                        <div class="border-b border-gray-200 pb-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-semibold"><?php echo htmlspecialchars($review['reviewer_name']); ?></p>
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