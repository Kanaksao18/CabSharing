<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get system statistics
$stats = [];

// User statistics
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'driver' THEN 1 ELSE 0 END) as total_drivers,
        SUM(CASE WHEN role = 'passenger' THEN 1 ELSE 0 END) as total_passengers,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended_users
    FROM users
");
$stats['users'] = $stmt->fetch();

// Ride statistics
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_rides,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_rides,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_rides,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_rides,
        AVG(price_per_seat) as avg_price_per_seat
    FROM rides
");
$stats['rides'] = $stmt->fetch();

// Booking statistics
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
        SUM(price) as total_revenue
    FROM bookings
");
$stats['bookings'] = $stmt->fetch();

// Review statistics
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star_reviews,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star_reviews
    FROM reviews
");
$stats['reviews'] = $stmt->fetch();

// Get recent activities
$stmt = $pdo->query("
    SELECT 
        n.*,
        CASE 
            WHEN n.type = 'user' THEN u.name
            WHEN n.type = 'ride' THEN CONCAT('Ride #', r.id)
            WHEN n.type = 'booking' THEN CONCAT('Booking #', b.id)
            ELSE 'System'
        END as source_name
    FROM notifications n
    LEFT JOIN users u ON n.source_id = u.id AND n.type = 'user'
    LEFT JOIN rides r ON n.source_id = r.id AND n.type = 'ride'
    LEFT JOIN bookings b ON n.source_id = b.id AND n.type = 'booking'
    ORDER BY n.created_at DESC
    LIMIT 5
");
$recent_activities = $stmt->fetchAll();

// Get recent users
$stmt = $pdo->query("
    SELECT * FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recent_users = $stmt->fetchAll();

// Get recent rides
$stmt = $pdo->query("
    SELECT r.*, u.name as driver_name 
    FROM rides r
    JOIN users u ON r.driver_id = u.id
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$recent_rides = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
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
                                <a href="index.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                                <a href="users.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Users</a>
                                <a href="rides.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Rides</a>
                                <a href="bookings.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Bookings</a>
                                <a href="reviews.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Reviews</a>
                                <a href="settings.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Settings</a>
                                <a href="profile.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Profile</a>
                                <a href="notifications.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Notifications</a>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-300 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
                        <a href="logout.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>

                <!-- Stats Grid -->
                <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Users Stats -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-users text-blue-500 text-2xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900"><?php echo $stats['users']['total_users']; ?></div>
                                            <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                                <span class="text-xs">Active: <?php echo $stats['users']['active_users']; ?></span>
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <span class="text-gray-500">Drivers: <?php echo $stats['users']['total_drivers']; ?></span>
                                <span class="ml-2 text-gray-500">Passengers: <?php echo $stats['users']['total_passengers']; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Rides Stats -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-car text-green-500 text-2xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Rides</dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900"><?php echo $stats['rides']['total_rides']; ?></div>
                                            <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                                <span class="text-xs">Completed: <?php echo $stats['rides']['completed_rides']; ?></span>
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <span class="text-gray-500">Avg. Price: $<?php echo number_format($stats['rides']['avg_price_per_seat'], 2); ?></span>
                                <span class="ml-2 text-gray-500">In Progress: <?php echo $stats['rides']['in_progress_rides']; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Bookings Stats -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-ticket-alt text-purple-500 text-2xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Bookings</dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900"><?php echo $stats['bookings']['total_bookings']; ?></div>
                                            <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                                <span class="text-xs">Confirmed: <?php echo $stats['bookings']['confirmed_bookings']; ?></span>
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <span class="text-gray-500">Total Revenue: $<?php echo number_format($stats['bookings']['total_revenue'], 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Stats -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-star text-yellow-500 text-2xl"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Reviews</dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900"><?php echo $stats['reviews']['total_reviews']; ?></div>
                                            <div class="ml-2 flex items-baseline text-sm font-semibold text-yellow-600">
                                                <span class="text-xs">Avg: <?php echo number_format($stats['reviews']['avg_rating'], 1); ?>/5</span>
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <span class="text-gray-500">5★: <?php echo $stats['reviews']['five_star_reviews']; ?></span>
                                <span class="ml-2 text-gray-500">1★: <?php echo $stats['reviews']['one_star_reviews']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="mt-8">
                    <h2 class="text-lg font-medium text-gray-900">Recent Activities</h2>
                    <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-md">
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($recent_activities as $activity): ?>
                                <li>
                                    <div class="px-4 py-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <?php if ($activity['type'] === 'user'): ?>
                                                        <i class="fas fa-user text-blue-500"></i>
                                                    <?php elseif ($activity['type'] === 'ride'): ?>
                                                        <i class="fas fa-car text-green-500"></i>
                                                    <?php elseif ($activity['type'] === 'booking'): ?>
                                                        <i class="fas fa-ticket-alt text-purple-500"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-bell text-gray-500"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($activity['title']); ?>
                                                    </p>
                                                    <p class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($activity['message']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="ml-4 flex-shrink-0">
                                                <p class="text-sm text-gray-500">
                                                    <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Recent Users and Rides -->
                <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <!-- Recent Users -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">Recent Users</h2>
                        <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-md">
                            <ul class="divide-y divide-gray-200">
                                <?php foreach ($recent_users as $user): ?>
                                    <li>
                                        <div class="px-4 py-4 sm:px-6">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="fas fa-user-circle text-gray-400 text-2xl"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-sm font-medium text-gray-900">
                                                            <?php echo htmlspecialchars($user['name']); ?>
                                                        </p>
                                                        <p class="text-sm text-gray-500">
                                                            <?php echo htmlspecialchars($user['email']); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="ml-4 flex-shrink-0">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                        <?php echo $user['role'] === 'driver' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Recent Rides -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">Recent Rides</h2>
                        <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-md">
                            <ul class="divide-y divide-gray-200">
                                <?php foreach ($recent_rides as $ride): ?>
                                    <li>
                                        <div class="px-4 py-4 sm:px-6">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="fas fa-car text-green-500"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-sm font-medium text-gray-900">
                                                            <?php echo htmlspecialchars($ride['source']); ?> → <?php echo htmlspecialchars($ride['destination']); ?>
                                                        </p>
                                                        <p class="text-sm text-gray-500">
                                                            Driver: <?php echo htmlspecialchars($ride['driver_name']); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="ml-4 flex-shrink-0">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                        <?php echo $ride['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                                              ($ride['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $ride['status'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 