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

// Handle ride cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_ride'])) {
    $ride_id = $_POST['ride_id'];
    try {
        $pdo->beginTransaction();
        
        // Update ride status
        $stmt = $pdo->prepare("UPDATE rides SET status = 'cancelled' WHERE id = ? AND driver_id = ?");
        $stmt->execute([$ride_id, $_SESSION['user']['id']]);
        
        // Update all bookings for this ride
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE ride_id = ?");
        $stmt->execute([$ride_id]);
        
        $pdo->commit();
        $success = 'Ride cancelled successfully';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Failed to cancel ride';
    }
}

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    try {
        $pdo->beginTransaction();
        
        // Get booking details
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND passenger_id = ?");
        $stmt->execute([$booking_id, $_SESSION['user']['id']]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            // Update booking status
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$booking_id]);
            
            // Update available seats
            $stmt = $pdo->prepare("UPDATE rides SET available_seats = available_seats + ? WHERE id = ?");
            $stmt->execute([$booking['seats_booked'], $booking['ride_id']]);
            
            $pdo->commit();
            $success = 'Booking cancelled successfully';
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Failed to cancel booking';
    }
}

// Get user's offered rides
$stmt = $pdo->prepare("SELECT r.*, COUNT(b.id) as total_bookings 
                      FROM rides r 
                      LEFT JOIN bookings b ON r.id = b.ride_id 
                      WHERE r.driver_id = ? 
                      GROUP BY r.id 
                      ORDER BY r.departure_time DESC");
$stmt->execute([$_SESSION['user']['id']]);
$offered_rides = $stmt->fetchAll();

// Get user's booked rides
$stmt = $pdo->prepare("SELECT b.*, r.source, r.destination, r.departure_time, r.car_model, r.car_number, 
                      u.name as driver_name, u.rating as driver_rating 
                      FROM bookings b 
                      JOIN rides r ON b.ride_id = r.id 
                      JOIN users u ON r.driver_id = u.id 
                      WHERE b.passenger_id = ? 
                      ORDER BY r.departure_time DESC");
$stmt->execute([$_SESSION['user']['id']]);
$booked_rides = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rides - CabShare</title>
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

            <!-- Offered Rides Section -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Rides I'm Offering</h2>
                <?php if (empty($offered_rides)): ?>
                    <div class="bg-white shadow-md rounded-lg p-6 text-center">
                        <p class="text-gray-600">You haven't offered any rides yet.</p>
                        <a href="offer-ride.php" class="text-green-600 hover:text-green-700">Offer a ride now!</a>
                    </div>
                <?php else: ?>
                    <div class="grid gap-6">
                        <?php foreach ($offered_rides as $ride): ?>
                            <div class="bg-white shadow-md rounded-lg p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                                            <i class="fas fa-car"></i> <?php echo htmlspecialchars($ride['car_model']); ?>
                                            (<?php echo htmlspecialchars($ride['car_number']); ?>)
                                        </p>
                                        <p class="text-gray-600 mt-1">
                                            <i class="fas fa-chair"></i> <?php echo $ride['available_seats']; ?> seats available
                                        </p>
                                        <p class="text-gray-600 mt-1">
                                            <i class="fas fa-users"></i> <?php echo $ride['total_bookings']; ?> bookings
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-bold text-green-600">₹<?php echo number_format($ride['price_per_seat'], 2); ?></p>
                                        <p class="text-gray-600">per seat</p>
                                        <?php if ($ride['status'] === 'active'): ?>
                                            <form method="POST" class="mt-4">
                                                <input type="hidden" name="ride_id" value="<?php echo $ride['id']; ?>">
                                                <button type="submit" name="cancel_ride"
                                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    Cancel Ride
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                Cancelled
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Booked Rides Section -->
            <div>
                <h2 class="text-2xl font-bold mb-6">Rides I've Booked</h2>
                <?php if (empty($booked_rides)): ?>
                    <div class="bg-white shadow-md rounded-lg p-6 text-center">
                        <p class="text-gray-600">You haven't booked any rides yet.</p>
                        <a href="find-ride.php" class="text-green-600 hover:text-green-700">Find a ride now!</a>
                    </div>
                <?php else: ?>
                    <div class="grid gap-6">
                        <?php foreach ($booked_rides as $booking): ?>
                            <div class="bg-white shadow-md rounded-lg p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($booking['source']); ?> → <?php echo htmlspecialchars($booking['destination']); ?></h3>
                                        <p class="text-gray-600 mt-1">
                                            <i class="far fa-calendar-alt"></i> 
                                            <?php echo date('M d, Y', strtotime($booking['departure_time'])); ?>
                                            <i class="far fa-clock ml-4"></i>
                                            <?php echo date('h:i A', strtotime($booking['departure_time'])); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600">
                                            <i class="fas fa-user"></i> Driver: <?php echo htmlspecialchars($booking['driver_name']); ?>
                                            <span class="ml-2 text-yellow-500">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?php echo $i <= $booking['driver_rating'] ? '' : '-o'; ?>"></i>
                                                <?php endfor; ?>
                                            </span>
                                        </p>
                                        <p class="text-gray-600 mt-1">
                                            <i class="fas fa-car"></i> <?php echo htmlspecialchars($booking['car_model']); ?>
                                            (<?php echo htmlspecialchars($booking['car_number']); ?>)
                                        </p>
                                        <p class="text-gray-600 mt-1">
                                            <i class="fas fa-chair"></i> <?php echo $booking['seats_booked']; ?> seats booked
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-bold text-green-600">₹<?php echo number_format($booking['total_price'], 2); ?></p>
                                        <p class="text-gray-600">total</p>
                                        <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                            <form method="POST" class="mt-4">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" name="cancel_booking"
                                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    Cancel Booking
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                Cancelled
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 