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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $source = $_POST['source'] ?? '';
    $destination = $_POST['destination'] ?? '';
    $departure_time = $_POST['departure_time'] ?? '';
    $available_seats = $_POST['available_seats'] ?? '';
    $price_per_seat = $_POST['price_per_seat'] ?? '';
    $car_model = $_POST['car_model'] ?? '';
    $car_number = $_POST['car_number'] ?? '';

    // Validation
    if (empty($source) || empty($destination) || empty($departure_time) || 
        empty($available_seats) || empty($price_per_seat) || empty($car_model) || 
        empty($car_number)) {
        $error = 'Please fill in all required fields';
    } elseif ($available_seats < 1) {
        $error = 'Available seats must be at least 1';
    } elseif ($price_per_seat <= 0) {
        $error = 'Price per seat must be greater than 0';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO rides (driver_id, source, destination, departure_time, 
                available_seats, price_per_seat, car_model, car_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([
                $_SESSION['user']['id'],
                $source,
                $destination,
                $departure_time,
                $available_seats,
                $price_per_seat,
                $car_model,
                $car_number
            ])) {
                $success = 'Ride posted successfully!';
            } else {
                $error = 'Failed to post ride. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offer a Ride - CabShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>

    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-6">Offer a Ride</h2>

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

                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="source" class="block text-sm font-medium text-gray-700">Source</label>
                            <input type="text" id="source" name="source" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                placeholder="Enter pickup location">
                        </div>

                        <div>
                            <label for="destination" class="block text-sm font-medium text-gray-700">Destination</label>
                            <input type="text" id="destination" name="destination" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                placeholder="Enter drop-off location">
                        </div>

                        <div>
                            <label for="departure_time" class="block text-sm font-medium text-gray-700">Departure Time</label>
                            <input type="datetime-local" id="departure_time" name="departure_time" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>

                        <div>
                            <label for="available_seats" class="block text-sm font-medium text-gray-700">Available Seats</label>
                            <input type="number" id="available_seats" name="available_seats" required min="1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                placeholder="Number of seats available">
                        </div>

                        <div>
                            <label for="price_per_seat" class="block text-sm font-medium text-gray-700">Price per Seat (₹)</label>
                            <input type="number" id="price_per_seat" name="price_per_seat" required min="1" step="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                placeholder="Price per seat">
                        </div>

                        <div>
                            <label for="car_model" class="block text-sm font-medium text-gray-700">Car Model</label>
                            <input type="text" id="car_model" name="car_model" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                placeholder="e.g., Maruti Swift">
                        </div>

                        <div>
                            <label for="car_number" class="block text-sm font-medium text-gray-700">Car Number</label>
                            <input type="text" id="car_number" name="car_number" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                placeholder="e.g., MH12AB1234">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Post Ride
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 