<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Check if ride ID is provided
if (!isset($_GET['id'])) {
    header('Location: find-ride.php');
    exit();
}

$ride_id = $_GET['id'];
$error = '';
$success = '';

// Get ride details
$stmt = $pdo->prepare("SELECT r.*, u.name as driver_name, u.rating as driver_rating 
                      FROM rides r 
                      JOIN users u ON r.driver_id = u.id 
                      WHERE r.id = ? AND r.status = 'active'");
$stmt->execute([$ride_id]);
$ride = $stmt->fetch();

if (!$ride) {
    header('Location: find-ride.php');
    exit();
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seats = $_POST['seats'] ?? 0;
    
    // Validation
    if ($seats < 1) {
        $error = 'Please select at least 1 seat';
    } elseif ($seats > $ride['available_seats']) {
        $error = 'Not enough seats available';
    } else {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Create booking
            $total_price = $seats * $ride['price_per_seat'];
            $stmt = $pdo->prepare("INSERT INTO bookings (ride_id, passenger_id, seats_booked, total_price) 
                                 VALUES (?, ?, ?, ?)");
            $stmt->execute([$ride_id, $_SESSION['user']['id'], $seats, $total_price]);

            // Update available seats
            $stmt = $pdo->prepare("UPDATE rides SET available_seats = available_seats - ? WHERE id = ?");
            $stmt->execute([$seats, $ride_id]);

            // Commit transaction
            $pdo->commit();
            
            $success = 'Booking successful!';
            
            // Redirect to my-rides.php after successful booking
            header('Location: my-rides.php');
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Booking failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ride - CabShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>

    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-6">Book Your Ride</h2>

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

                <!-- Ride Details -->
                <div class="mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($ride['source']); ?> → <?php echo htmlspecialchars($ride['destination']); ?></h3>
                            <p class="text-gray-600 mt-1">
                                <i class="far fa-calendar-alt"></i> 
                                <?php echo date('M d, Y', strtotime($ride['departure_time'])); ?>
                                <i class="far fa-clock ml-4"></i>
                                <?php echo date('h:i A', strtotime($ride['departure_time'])); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-600">
                                <i class="fas fa-user"></i> Driver: <?php echo htmlspecialchars($ride['driver_name']); ?>
                                <span class="ml-2 text-yellow-500">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $ride['driver_rating'] ? '' : '-o'; ?>"></i>
                                    <?php endfor; ?>
                                </span>
                            </p>
                            <p class="text-gray-600 mt-1">
                                <i class="fas fa-car"></i> <?php echo htmlspecialchars($ride['car_model']); ?>
                                (<?php echo htmlspecialchars($ride['car_number']); ?>)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Booking Form -->
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="seats" class="block text-sm font-medium text-gray-700">Number of Seats</label>
                            <input type="number" id="seats" name="seats" min="1" max="<?php echo $ride['available_seats']; ?>" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6]"
                                placeholder="Select number of seats">
                            <p class="mt-1 text-sm text-gray-500">
                                <?php echo $ride['available_seats']; ?> seats available
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price per Seat</label>
                            <p class="mt-1 text-2xl font-bold text-[#8B5CF6]">₹<?php echo number_format($ride['price_per_seat'], 2); ?></p>
                            <p class="text-sm text-gray-500">Total price will be calculated based on seats selected</p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="find-ride.php" 
                            class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#8B5CF6] hover:bg-[#7C3AED] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8B5CF6]">
                            Confirm Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Calculate total price based on seats selected
        document.getElementById('seats').addEventListener('input', function() {
            const seats = this.value;
            const pricePerSeat = <?php echo $ride['price_per_seat']; ?>;
            const totalPrice = seats * pricePerSeat;
            document.getElementById('total-price').textContent = '₹' + totalPrice.toFixed(2);
        });
    </script>
</body>
</html> 