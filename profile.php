<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
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
    
    // Validation
    if (empty($name)) {
        $error = 'Name is required';
    } elseif (empty($phone)) {
        $error = 'Phone number is required';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, car_model = ?, car_number = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $car_model, $car_number, $_SESSION['user']['id']]);
            
            // Update session data
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['car_model'] = $car_model;
            $_SESSION['user']['car_number'] = $car_number;
            
            $success = 'Profile updated successfully';
        } catch (PDOException $e) {
            $error = 'Failed to update profile';
        }
    }
}

// Get user's ride statistics
$stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM rides WHERE driver_id = ?) as rides_offered,
        (SELECT COUNT(*) FROM bookings WHERE passenger_id = ?) as rides_booked,
        (SELECT COUNT(*) FROM rides WHERE driver_id = ? AND status = 'completed') as completed_rides,
        (SELECT COUNT(*) FROM bookings WHERE passenger_id = ? AND status = 'completed') as completed_bookings,
        (SELECT AVG(rating) FROM reviews WHERE reviewed_id = ?) as average_rating
");
$stmt->execute([
    $_SESSION['user']['id'],
    $_SESSION['user']['id'],
    $_SESSION['user']['id'],
    $_SESSION['user']['id'],
    $_SESSION['user']['id']
]);
$stats = $stmt->fetch();

// Get recent reviews
$stmt = $pdo->prepare("
    SELECT r.*, u.name as reviewer_name 
    FROM reviews r 
    JOIN users u ON r.reviewer_id = u.id 
    WHERE r.reviewed_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user']['id']]);
$recent_reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CabShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>

    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Profile Information -->
                <div class="md:col-span-2">
                    <div class="bg-white shadow-md rounded-lg p-6">
                        <h2 class="text-2xl font-bold mb-6">Profile Information</h2>
                        <form method="POST" class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_SESSION['user']['name'] ?? ''); ?>" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" id="email" value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>" disabled
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            </div>

                            <div>
                                <label for="car_model" class="block text-sm font-medium text-gray-700">Car Model</label>
                                <input type="text" id="car_model" name="car_model" value="<?php echo htmlspecialchars($_SESSION['user']['car_model'] ?? ''); ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            </div>

                            <div>
                                <label for="car_number" class="block text-sm font-medium text-gray-700">Car Number</label>
                                <input type="text" id="car_number" name="car_number" value="<?php echo htmlspecialchars($_SESSION['user']['car_number'] ?? ''); ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" name="update_profile"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Statistics and Reviews -->
                <div class="space-y-8">
                    <!-- Statistics -->
                    <div class="bg-white shadow-md rounded-lg p-6">
                        <h2 class="text-2xl font-bold mb-6">Ride Statistics</h2>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Rides Offered</span>
                                <span class="font-semibold"><?php echo $stats['rides_offered']; ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Rides Booked</span>
                                <span class="font-semibold"><?php echo $stats['rides_booked']; ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Completed Rides</span>
                                <span class="font-semibold"><?php echo $stats['completed_rides']; ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Completed Bookings</span>
                                <span class="font-semibold"><?php echo $stats['completed_bookings']; ?></span>
                            </div>
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

                    <!-- Recent Reviews -->
                    <div class="bg-white shadow-md rounded-lg p-6">
                        <h2 class="text-2xl font-bold mb-6">Recent Reviews</h2>
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
</body>
</html> 