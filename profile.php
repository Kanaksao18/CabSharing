<?php
session_start();
require_once 'config/database.php';

// Initialize database connection
try {
    if (!isset($conn)) {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $car_model = $_POST['car_model'] ?? '';
    $car_number = $_POST['car_number'] ?? '';

    try {
        $stmt = $conn->prepare("
            UPDATE users 
            SET name = ?, email = ?, phone = ?, car_model = ?, car_number = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $email, $phone, $car_model, $car_number, $user_id]);
        $success = 'Profile updated successfully';
        
        // Update session data
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['car_model'] = $car_model;
        $_SESSION['user']['car_number'] = $car_number;
    } catch(PDOException $e) {
        $error = 'Failed to update profile: ' . $e->getMessage();
    }
}

// Get user data
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set default values if fields are not set
    $user['car_model'] = $user['car_model'] ?? '';
    $user['car_number'] = $user['car_number'] ?? '';
} catch(PDOException $e) {
    $error = 'Failed to fetch user data: ' . $e->getMessage();
}

// Get user's ride statistics
$stmt = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM rides WHERE driver_id = ?) as rides_offered,
        (SELECT COUNT(*) FROM bookings WHERE passenger_id = ?) as rides_booked,
        (SELECT COUNT(*) FROM rides WHERE driver_id = ? AND status = 'completed') as completed_rides,
        (SELECT COUNT(*) FROM bookings WHERE passenger_id = ? AND status = 'completed') as completed_bookings,
        (SELECT AVG(rating) FROM reviews WHERE reviewed_id = ?) as average_rating
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
$stats = $stmt->fetch();

// Get recent reviews
$stmt = $conn->prepare("
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
    <title>My Profile - CabShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>

    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Success/Error Messages -->
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-[#F5F3FF] border border-[#8B5CF6] text-[#8B5CF6] px-4 py-3 rounded-lg mb-6" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Profile Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-[#8B5CF6] p-6 text-center">
                            <div class="w-24 h-24 mx-auto rounded-full bg-white flex items-center justify-center mb-4">
                                <i class="fas fa-user text-4xl text-[#8B5CF6]"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($user['name']); ?></h2>
                            <p class="text-white/80"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <i class="fas fa-phone text-[#8B5CF6] w-6"></i>
                                    <span class="ml-3 text-gray-600"><?php echo htmlspecialchars($user['phone']); ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-car text-[#8B5CF6] w-6"></i>
                                    <span class="ml-3 text-gray-600"><?php echo htmlspecialchars($user['car_model']); ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-id-card text-[#8B5CF6] w-6"></i>
                                    <span class="ml-3 text-gray-600"><?php echo htmlspecialchars($user['car_number']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-[#F5F3FF] text-[#8B5CF6]">
                                    <i class="fas fa-car-side text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-500">Rides Offered</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['rides_offered']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-[#F5F3FF] text-[#8B5CF6]">
                                    <i class="fas fa-ticket-alt text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-500">Rides Booked</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['rides_booked']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-[#F5F3FF] text-[#8B5CF6]">
                                    <i class="fas fa-star text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-500">Average Rating</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['average_rating'], 1); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Update Form -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-6">Update Profile</h3>
                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6]">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6]">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6]">
                                </div>
                                <div>
                                    <label for="car_model" class="block text-sm font-medium text-gray-700">Car Model</label>
                                    <input type="text" id="car_model" name="car_model" value="<?php echo htmlspecialchars($user['car_model']); ?>"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6]">
                                </div>
                                <div>
                                    <label for="car_number" class="block text-sm font-medium text-gray-700">Car Number</label>
                                    <input type="text" id="car_number" name="car_number" value="<?php echo htmlspecialchars($user['car_number']); ?>"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6]">
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-6 py-3 bg-[#8B5CF6] text-white rounded-lg hover:bg-[#7C3AED] transition-colors duration-200">
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Recent Reviews -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-6">Recent Reviews</h3>
                        <?php if (empty($recent_reviews)): ?>
                            <p class="text-gray-500 text-center py-4">No reviews yet</p>
                        <?php else: ?>
                            <div class="space-y-6">
                                <?php foreach ($recent_reviews as $review): ?>
                                    <div class="border-b border-gray-100 pb-6 last:border-0">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 rounded-full bg-[#F5F3FF] flex items-center justify-center">
                                                    <i class="fas fa-user text-[#8B5CF6]"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($review['reviewer_name']); ?></p>
                                                    <div class="flex items-center text-[#8B5CF6]">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?> text-sm"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                            </span>
                                        </div>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>
</html> 