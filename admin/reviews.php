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

// Handle review deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $review_id = $_POST['review_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $success = 'Review deleted successfully';
    } catch (PDOException $e) {
        $error = 'Failed to delete review';
    }
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$rating = $_GET['rating'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$query = "
    SELECT r.*, 
           u1.name as reviewer_name,
           u1.email as reviewer_email,
           u2.name as reviewed_name,
           u2.email as reviewed_email,
           ride.source,
           ride.destination,
           ride.departure_time
    FROM reviews r
    JOIN users u1 ON r.reviewer_id = u1.id
    JOIN users u2 ON r.reviewed_id = u2.id
    JOIN rides ride ON r.ride_id = ride.id
    WHERE 1=1
";
$params = [];

if ($search) {
    $query .= " AND (u1.name LIKE ? OR u1.email LIKE ? OR u2.name LIKE ? OR u2.email LIKE ? OR r.comment LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
}

if ($rating) {
    $query .= " AND r.rating = ?";
    $params[] = $rating;
}

if ($date_from) {
    $query .= " AND r.created_at >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $query .= " AND r.created_at <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY r.created_at DESC";

// Get reviews
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

// Get review statistics
$stats = [
    'total_reviews' => $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
    'average_rating' => $pdo->query("SELECT AVG(rating) FROM reviews")->fetchColumn(),
    'five_star_reviews' => $pdo->query("SELECT COUNT(*) FROM reviews WHERE rating = 5")->fetchColumn(),
    'one_star_reviews' => $pdo->query("SELECT COUNT(*) FROM reviews WHERE rating = 1")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - CabShare Admin</title>
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
                                <a href="bookings.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Bookings</a>
                                <a href="reviews.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Reviews</a>
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
                    <h1 class="text-2xl font-semibold text-gray-900">Manage Reviews</h1>
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-star text-3xl text-yellow-400"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Reviews</dt>
                                        <dd class="text-lg font-semibold text-gray-900"><?php echo $stats['total_reviews']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-star-half-alt text-3xl text-yellow-400"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Average Rating</dt>
                                        <dd class="text-lg font-semibold text-gray-900">
                                            <?php echo number_format($stats['average_rating'], 1); ?>
                                            <span class="text-yellow-500">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?php echo $i <= round($stats['average_rating']) ? '' : '-o'; ?>"></i>
                                                <?php endfor; ?>
                                            </span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-star text-3xl text-green-400"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">5-Star Reviews</dt>
                                        <dd class="text-lg font-semibold text-gray-900"><?php echo $stats['five_star_reviews']; ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-star text-3xl text-red-400"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">1-Star Reviews</dt>
                                        <dd class="text-lg font-semibold text-gray-900"><?php echo $stats['one_star_reviews']; ?></dd>
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
                                placeholder="Search by name, email or comment">
                        </div>

                        <div>
                            <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
                            <select name="rating" id="rating"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">All Ratings</option>
                                <option value="5" <?php echo $rating === '5' ? 'selected' : ''; ?>>5 Stars</option>
                                <option value="4" <?php echo $rating === '4' ? 'selected' : ''; ?>>4 Stars</option>
                                <option value="3" <?php echo $rating === '3' ? 'selected' : ''; ?>>3 Stars</option>
                                <option value="2" <?php echo $rating === '2' ? 'selected' : ''; ?>>2 Stars</option>
                                <option value="1" <?php echo $rating === '1' ? 'selected' : ''; ?>>1 Star</option>
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

                <!-- Reviews Table -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Review</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ride</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <div class="text-yellow-500">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($review['comment']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($review['reviewer_name']); ?> →
                                            <?php echo htmlspecialchars($review['reviewed_name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($review['reviewer_email']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($review['source']); ?> → <?php echo htmlspecialchars($review['destination']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('M d, Y H:i', strtotime($review['departure_time'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                            <button type="submit" name="delete_review" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
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