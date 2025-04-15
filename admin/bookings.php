<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';

// Handle booking status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $booking_id]);
        $success = 'Booking status updated successfully';
    } catch (PDOException $e) {
        $error = 'Failed to update booking status';
    }
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$query = "
    SELECT b.*, 
           u.name as passenger_name,
           u.phone as passenger_phone,
           u.email as passenger_email,
           r.source,
           r.destination,
           r.departure_time,
           r.price_per_seat,
           r.status as ride_status,
           d.name as driver_name,
           d.phone as driver_phone,
           d.car_model,
           d.car_number
    FROM bookings b
    JOIN users u ON b.passenger_id = u.id
    JOIN rides r ON b.ride_id = r.id
    JOIN users d ON r.driver_id = d.id
    WHERE 1=1
";
$params = [];

if ($search) {
    $query .= " AND (u.name LIKE ? OR u.phone LIKE ? OR u.email LIKE ? OR r.source LIKE ? OR r.destination LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
}

if ($status) {
    $query .= " AND b.status = ?";
    $params[] = $status;
}

if ($date_from) {
    $query .= " AND b.created_at >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $query .= " AND b.created_at <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY b.created_at DESC";

// Get bookings
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Get booking statistics
$stats = [
    'total_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'confirmed_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn(),
    'pending_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
    'cancelled_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'cancelled'")->fetchColumn(),
    'completed_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE status = 'completed'")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - CabShare Admin</title>
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
                    <h1 class="text-2xl font-semibold text-gray-900">Manage Bookings</h1>
                    <div class="flex space-x-4">
                        <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Dashboard
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

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-ticket-alt text-3xl text-gray-400"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Bookings</dt>
                                        <dd class="text-lg font-semibold text-gray-900"><?php echo $stats['total_bookings']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-3xl text-green-400"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Confirmed</dt>
                                        <dd class="text-lg font-semibold text-gray-900"><?php echo $stats['confirmed_bookings']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock text-3xl text-yellow-400"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                                        <dd class="text-lg font-semibold text-gray-900"><?php echo $stats['pending_bookings']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-times-circle text-3xl text-red-400"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Cancelled</dt>
                                        <dd class="text-lg font-semibold text-gray-900"><?php echo $stats['cancelled_bookings']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-double text-3xl text-blue-400"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                                        <dd class="text-lg font-semibold text-gray-900"><?php echo $stats['completed_bookings']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-rupee-sign text-3xl text-purple-400"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                                        <dd class="text-lg font-semibold text-gray-900">₹<?php echo number_format($stats['total_revenue'], 2); ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                placeholder="Search by passenger name, phone, email, source or destination">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">From Date</label>
                            <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($date_from); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">To Date</label>
                            <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($date_to); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>

                        <div class="md:col-span-4 flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-search mr-2"></i>
                                Search
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bookings Table -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passenger</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ride</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            Booking #<?php echo $booking['id']; ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo $booking['seats_booked']; ?> seat(s) • 
                                            ₹<?php echo number_format($booking['total_price'], 2); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($booking['passenger_name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($booking['passenger_phone']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($booking['passenger_email']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($booking['source']); ?> → <?php echo htmlspecialchars($booking['destination']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('M d, Y H:i', strtotime($booking['departure_time'])); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($booking['driver_name']); ?> • 
                                            <?php echo htmlspecialchars($booking['car_model']); ?> (<?php echo htmlspecialchars($booking['car_number']); ?>)
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="POST" class="flex items-center">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <select name="status" onchange="this.form.submit()"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="text-green-600 hover:text-green-900 mr-3">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 