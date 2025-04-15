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

// Get ride ID from URL
$ride_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get ride details and check if user can review
$stmt = $pdo->prepare("
    SELECT r.*, 
           CASE 
               WHEN r.driver_id = ? THEN 'driver'
               WHEN EXISTS (
                   SELECT 1 FROM bookings b 
                   WHERE b.ride_id = r.id 
                   AND b.passenger_id = ? 
                   AND b.status = 'completed'
               ) THEN 'passenger'
               ELSE NULL
           END as user_role
    FROM rides r
    WHERE r.id = ? AND r.status = 'completed'
");
$stmt->execute([$_SESSION['user']['id'], $_SESSION['user']['id'], $ride_id]);
$ride = $stmt->fetch();

// Check if user can review this ride
if (!$ride || !$ride['user_role']) {
    header('Location: my-rides.php');
    exit();
}

// Get the other party's details
if ($ride['user_role'] === 'driver') {
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.rating
        FROM bookings b
        JOIN users u ON b.passenger_id = u.id
        WHERE b.ride_id = ? AND b.status = 'completed'
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.rating
        FROM users u
        WHERE u.id = ?
    ");
    $stmt->execute([$ride['driver_id']]);
}
$stmt->execute([$ride_id]);
$other_party = $stmt->fetch();

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    // Validation
    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a valid rating';
    } elseif (empty($comment)) {
        $error = 'Please enter a review comment';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Check if review already exists
            $stmt = $pdo->prepare("
                SELECT id FROM reviews 
                WHERE ride_id = ? AND reviewer_id = ? AND reviewed_id = ?
            ");
            $stmt->execute([$ride_id, $_SESSION['user']['id'], $other_party['id']]);
            
            if ($stmt->fetch()) {
                $error = 'You have already reviewed this ride';
            } else {
                // Insert review
                $stmt = $pdo->prepare("
                    INSERT INTO reviews (ride_id, reviewer_id, reviewed_id, rating, comment)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $ride_id,
                    $_SESSION['user']['id'],
                    $other_party['id'],
                    $rating,
                    $comment
                ]);
                
                // Update user's average rating
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET rating = (
                        SELECT AVG(rating) 
                        FROM reviews 
                        WHERE reviewed_id = ?
                    )
                    WHERE id = ?
                ");
                $stmt->execute([$other_party['id'], $other_party['id']]);
                
                $pdo->commit();
                $success = 'Review submitted successfully';
                
                // Redirect to my-rides.php after successful review
                header('Location: my-rides.php');
                exit();
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Failed to submit review';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave a Review - CabShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>

    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
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

            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-6">Leave a Review</h2>

                <!-- Ride Details -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4">Ride Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-gray-600">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($ride['source']); ?> → <?php echo htmlspecialchars($ride['destination']); ?>
                            </p>
                            <p class="text-gray-600 mt-1">
                                <i class="far fa-calendar-alt"></i> 
                                <?php echo date('M d, Y', strtotime($ride['departure_time'])); ?>
                                <i class="far fa-clock ml-4"></i>
                                <?php echo date('h:i A', strtotime($ride['departure_time'])); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-600">
                                <i class="fas fa-user"></i> 
                                <?php echo $ride['user_role'] === 'driver' ? 'Passenger' : 'Driver'; ?>: 
                                <?php echo htmlspecialchars($other_party['name']); ?>
                            </p>
                            <p class="text-gray-600 mt-1">
                                <i class="fas fa-car"></i> 
                                <?php echo htmlspecialchars($ride['car_model']); ?>
                                (<?php echo htmlspecialchars($ride['car_number']); ?>)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Review Form -->
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex space-x-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" required
                                        class="sr-only">
                                    <i class="fas fa-star text-3xl <?php echo isset($_POST['rating']) && $_POST['rating'] == $i ? 'text-yellow-500' : 'text-gray-300'; ?> hover:text-yellow-500"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Review Comment</label>
                        <textarea id="comment" name="comment" rows="4" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            placeholder="Share your experience..."><?php echo isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : ''; ?></textarea>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="my-rides.php" 
                            class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Cancel
                        </a>
                        <button type="submit" name="submit_review"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Update star colors on rating selection
        document.querySelectorAll('input[name="rating"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const stars = document.querySelectorAll('.fa-star');
                const rating = this.value;
                
                stars.forEach((star, index) => {
                    star.classList.toggle('text-yellow-500', index < rating);
                    star.classList.toggle('text-gray-300', index >= rating);
                });
            });
        });
    </script>
</body>
</html> 