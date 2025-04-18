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

// Get search parameters
$source = $_GET['source'] ?? '';
$destination = $_GET['destination'] ?? '';
$date = $_GET['date'] ?? '';
$seats = $_GET['seats'] ?? 1;

// Build the query
$query = "SELECT r.*, u.name as driver_name, u.rating as driver_rating 
          FROM rides r 
          JOIN users u ON r.driver_id = u.id 
          WHERE r.status = 'active' 
          AND r.available_seats >= ?";

$params = [$seats];

if (!empty($source)) {
    $query .= " AND r.source LIKE ?";
    $params[] = "%$source%";
}

if (!empty($destination)) {
    $query .= " AND r.destination LIKE ?";
    $params[] = "%$destination%";
}

if (!empty($date)) {
    $query .= " AND DATE(r.departure_time) = ?";
    $params[] = $date;
}

$query .= " ORDER BY r.departure_time ASC";

// Execute the query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$rides = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find a Ride - CabShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Maps API -->
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key='AIzaSyB101yxK-k-5FlUHJnUoDAQeG1LRmrTx24'&callback=initAllMaps">
    </script>
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>

    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Search Form -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-bold mb-6">Find Your Ride</h2>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="source" class="block text-sm font-medium text-gray-700">From</label>
                        <input type="text" id="source" name="source" value="<?php echo htmlspecialchars($source); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6]"
                            placeholder="Enter source">
                    </div>
                    <div>
                        <label for="destination" class="block text-sm font-medium text-gray-700">To</label>
                        <input type="text" id="destination" name="destination" value="<?php echo htmlspecialchars($destination); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6]"
                            placeholder="Enter destination">
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6]">
                    </div>
                    <div>
                        <label for="seats" class="block text-sm font-medium text-gray-700">Seats</label>
                        <input type="number" id="seats" name="seats" value="<?php echo htmlspecialchars($seats); ?>" min="1"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6]">
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#8B5CF6] hover:bg-[#7C3AED] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8B5CF6]">
                            Search Rides
                        </button>
                    </div>
                </form>
            </div>

            <!-- Rides List -->
            <div class="space-y-6">
                <?php if (empty($rides)): ?>
                    <div class="bg-white shadow-md rounded-lg p-6 text-center">
                        <p class="text-gray-600">No rides found matching your criteria.</p>
                        <a href="offer-ride.php" class="text-[#8B5CF6] hover:text-[#7C3AED]">Be the first to offer a ride!</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($rides as $index => $ride): ?>
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
                                        <i class="fas fa-user"></i> Driver: <?php echo htmlspecialchars($ride['driver_name']); ?>
                                        <span class="ml-2 text-[#8B5CF6]">
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
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-[#8B5CF6]">₹<?php echo number_format($ride['price_per_seat'], 2); ?></p>
                                    <p class="text-gray-600">per seat</p>
                                    <p class="text-gray-600 mt-1">
                                        <i class="fas fa-chair"></i> <?php echo $ride['available_seats']; ?> seats available
                                    </p>
                                    <?php if (isset($_SESSION['user'])): ?>
                                        <a href="book-ride.php?id=<?php echo $ride['id']; ?>" 
                                            class="mt-2 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#8B5CF6] hover:bg-[#7C3AED] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8B5CF6]">
                                            Book Now
                                        </a>
                                    <?php else: ?>
                                        <a href="login.php" 
                                            class="mt-2 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#8B5CF6] hover:bg-[#7C3AED] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8B5CF6]">
                                            Login to Book
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Google Map Container -->
                            <div id="map-<?php echo $index; ?>" class="w-full h-64 mt-4 rounded-lg border"></div>
                        </div>

                        <script>
                            function initMap<?php echo $index; ?>() {
                                const directionsService = new google.maps.DirectionsService();
                                const directionsRenderer = new google.maps.DirectionsRenderer();
                                const map = new google.maps.Map(document.getElementById("map-<?php echo $index; ?>"), {
                                    zoom: 7,
                                    center: { lat: 20.5937, lng: 78.9629 },
                                });
                                directionsRenderer.setMap(map);
                                directionsService.route({
                                    origin: "<?php echo htmlspecialchars($ride['source']); ?>",
                                    destination: "<?php echo htmlspecialchars($ride['destination']); ?>",
                                    travelMode: 'DRIVING'
                                }, (result, status) => {
                                    if (status === 'OK') {
                                        directionsRenderer.setDirections(result);
                                    } else {
                                        document.getElementById("map-<?php echo $index; ?>").innerHTML = "<p class='text-center text-gray-500'>Map unavailable</p>";
                                    }
                                });
                            }

                            window.addEventListener('load', () => {
                                if (typeof google !== "undefined") {
                                    initMap<?php echo $index; ?>();
                                }
                            });
                        </script>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
