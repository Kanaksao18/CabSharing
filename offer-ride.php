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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $source = $_POST['source'] ?? '';
    $source_formatted = $_POST['source_formatted'] ?? '';
    $destination = $_POST['destination'] ?? '';
    $destination_formatted = $_POST['destination_formatted'] ?? '';
    $departure_time = $_POST['departure_time'] ?? '';
    $available_seats = $_POST['available_seats'] ?? '';
    $price_per_seat = $_POST['price_per_seat'] ?? '';
    $car_model = $_POST['car_model'] ?? '';
    $car_number = $_POST['car_number'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($source) || empty($destination) || empty($departure_time) || 
        empty($available_seats) || empty($price_per_seat)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO rides (
                    driver_id, source, source_formatted, destination, destination_formatted,
                    departure_time, available_seats, price_per_seat, car_model, car_number, 
                    description, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available')
            ");
            
            $stmt->execute([
                $_SESSION['user']['id'],
                $source,
                $source_formatted,
                $destination,
                $destination_formatted,
                $departure_time,
                $available_seats,
                $price_per_seat,
                $car_model,
                $car_number,
                $description
            ]);
            
            $success = 'Ride offered successfully!';
        } catch(PDOException $e) {
            $error = 'Failed to offer ride: ' . $e->getMessage();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add Google Maps API -->
    <script>
        // Global variables
        let sourceAutocomplete;
        let destinationAutocomplete;
        let map;
        let directionsService;
        let directionsRenderer;
        let distanceSpan;
        let currentRoute = 0;
        let routes = [];
        let isMapInitialized = false;
        let apiLoadAttempts = 0;
        const MAX_API_LOAD_ATTEMPTS = 3;

        // Replace this with your actual Google Maps API key
        const GOOGLE_MAPS_API_KEY = 'AIzaSyB101yxK-k-5FlUHJnUoDAQeG1LRmrTx24';

        // Fuel prices (in INR per liter)
        const FUEL_PRICES = {
            petrol: 100,    // Average petrol price
            diesel: 90,     // Average diesel price
            cng: 80        // Average CNG price
        };

        // Average fuel efficiency (in km/l)
        const FUEL_EFFICIENCY = {
            petrol: 15,
            diesel: 18,
            cng: 20
        };

        // Debug function
        function debug(message) {
            console.log(`[Google Maps Debug] ${message}`);
            const debugDiv = document.getElementById('debugInfo');
            if (debugDiv) {
                debugDiv.style.display = 'block';
                debugDiv.innerHTML += `<p>${message}</p>`;
            }
        }

        // Show loading state
        function showLoading(message) {
            const mapLoading = document.getElementById('mapLoading');
            if (mapLoading) {
                mapLoading.style.display = 'block';
                mapLoading.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#8B5CF6]"></div>
                        <p class="mt-2 text-gray-600">${message}</p>
                    </div>
                `;
            }
        }

        // Hide loading state
        function hideLoading() {
            const mapLoading = document.getElementById('mapLoading');
            if (mapLoading) {
                mapLoading.style.display = 'none';
            }
        }

        // Load Google Maps API
        function loadGoogleMaps() {
            debug('Starting Google Maps API load...');
            showLoading('Initializing Google Maps...');
            
            if (!GOOGLE_MAPS_API_KEY || GOOGLE_MAPS_API_KEY === 'YOUR_API_KEY_HERE') {
                const errorMessage = 'Google Maps API key is not configured. Please set up a valid API key.';
                debug(errorMessage);
                showError(errorMessage);
                hideLoading();
                return;
            }

            if (apiLoadAttempts >= MAX_API_LOAD_ATTEMPTS) {
                const errorMessage = 'Failed to load Google Maps after multiple attempts. Please check your internet connection and try again later.';
                debug(errorMessage);
                showError(errorMessage);
                hideLoading();
                return;
            }

            apiLoadAttempts++;
            debug(`Attempt ${apiLoadAttempts} of ${MAX_API_LOAD_ATTEMPTS}`);

            // Check if script already exists
            const existingScript = document.querySelector('script[src*="maps.googleapis.com"]');
            if (existingScript) {
                debug('Removing existing Google Maps script');
                existingScript.remove();
            }

            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_MAPS_API_KEY}&libraries=places,geometry&callback=initMap`;
            script.async = true;
            script.defer = true;
            
            script.onerror = function(error) {
                debug(`Script load error: ${error}`);
                hideLoading();
                const mapLoading = document.getElementById('mapLoading');
                if (mapLoading) {
                    mapLoading.innerHTML = `
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                            <p>Failed to load Google Maps. Please check your internet connection.</p>
                            <p class="text-sm mt-2">Error details: ${error}</p>
                            <button onclick="loadGoogleMaps()" class="mt-2 px-4 py-2 bg-[#8B5CF6] text-white rounded-lg hover:bg-[#7C3AED]">
                                Retry (Attempt ${apiLoadAttempts}/${MAX_API_LOAD_ATTEMPTS})
                            </button>
                        </div>
                    `;
                }
            };

            // Set a timeout to check if the API loaded
            const timeout = setTimeout(() => {
                if (typeof google === 'undefined' || !google.maps) {
                    const errorMessage = 'Google Maps API failed to load within timeout period.';
                    debug(errorMessage);
                    showError(errorMessage);
                    hideLoading();
                    if (script.parentNode) {
                        script.parentNode.removeChild(script);
                    }
                    loadGoogleMaps();
                }
            }, 10000); // 10 seconds timeout

            script.onload = function() {
                debug('Script loaded successfully');
                clearTimeout(timeout);
            };

            document.head.appendChild(script);
            debug('Script element added to document head');
        }

        // Initialize Google Maps
        function initMap() {
            debug('Starting map initialization...');
            showLoading('Setting up map...');
            
            try {
                if (typeof google === 'undefined' || !google.maps) {
                    throw new Error('Google Maps API not loaded');
                }
                debug('Google Maps API is available');

                // Initialize map
                const mapOptions = {
                    zoom: 7,
                    center: { lat: 20.5937, lng: 78.9629 }, // Center of India
                    mapTypeControl: true,
                    mapTypeControlOptions: {
                        style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                        position: google.maps.ControlPosition.TOP_RIGHT
                    }
                };
                
                const mapElement = document.getElementById('map');
                if (!mapElement) {
                    throw new Error('Map container not found');
                }
                debug('Map container found');

                map = new google.maps.Map(mapElement, mapOptions);
                debug('Map instance created');

                directionsService = new google.maps.DirectionsService();
                directionsRenderer = new google.maps.DirectionsRenderer({
                    map: map,
                    suppressMarkers: false,
                    polylineOptions: {
                        strokeColor: '#8B5CF6',
                        strokeWeight: 5
                    }
                });
                debug('Directions service and renderer initialized');

                // Get distance span element
                distanceSpan = document.getElementById('distance');
                const routeSelector = document.getElementById('routeSelector');

                // Initialize source autocomplete
                const sourceInput = document.getElementById('source');
                if (sourceInput) {
                    try {
                        sourceAutocomplete = new google.maps.places.Autocomplete(sourceInput, {
                            types: ['(cities)'],
                            componentRestrictions: { country: 'in' }
                        });

                        sourceAutocomplete.addListener('place_changed', function() {
                            try {
                                const place = sourceAutocomplete.getPlace();
                                if (place.geometry) {
                                    const sourceFormatted = document.getElementById('source_formatted');
                                    if (sourceFormatted) {
                                        sourceFormatted.value = place.formatted_address;
                                    }
                                    sourceInput.value = place.name;
                                    updateMapAndDistance();
                                } else {
                                    console.error('No details available for input: ' + place.name);
                                    showError('Please select a valid city from the dropdown');
                                }
                            } catch (error) {
                                console.error('Error in source place selection:', error);
                                showError('Error selecting source city. Please try again.');
                            }
                        });
                    } catch (error) {
                        console.error('Error initializing source autocomplete:', error);
                        showError('Error initializing location search. Please refresh the page.');
                    }
                }

                // Initialize destination autocomplete
                const destinationInput = document.getElementById('destination');
                if (destinationInput) {
                    try {
                        destinationAutocomplete = new google.maps.places.Autocomplete(destinationInput, {
                            types: ['(cities)'],
                            componentRestrictions: { country: 'in' }
                        });

                        destinationAutocomplete.addListener('place_changed', function() {
                            try {
                                const place = destinationAutocomplete.getPlace();
                                if (place.geometry) {
                                    const destinationFormatted = document.getElementById('destination_formatted');
                                    if (destinationFormatted) {
                                        destinationFormatted.value = place.formatted_address;
                                    }
                                    destinationInput.value = place.name;
                                    updateMapAndDistance();
                                } else {
                                    console.error('No details available for input: ' + place.name);
                                    showError('Please select a valid city from the dropdown');
                                }
                            } catch (error) {
                                console.error('Error in destination place selection:', error);
                                showError('Error selecting destination city. Please try again.');
                            }
                        });
                    } catch (error) {
                        console.error('Error initializing destination autocomplete:', error);
                        showError('Error initializing location search. Please refresh the page.');
                    }
                }

                // Route selector event listener
                if (routeSelector) {
                    routeSelector.addEventListener('change', function() {
                        currentRoute = parseInt(this.value);
                        if (routes[currentRoute]) {
                            showRoute(routes[currentRoute]);
                        }
                    });
                }

                // Hide loading and show map
                hideLoading();
                mapElement.style.display = 'block';
                debug('Map display updated');

                isMapInitialized = true;
                debug('Map initialization completed successfully');

            } catch (error) {
                debug(`Error during map initialization: ${error.message}`);
                console.error('Error initializing Google Maps:', error);
                showError(`Error initializing location services: ${error.message}`);
                hideLoading();
            }
        }

        function updateMapAndDistance() {
            if (!isMapInitialized) {
                console.error('Map not initialized');
                return;
            }

            const sourcePlace = sourceAutocomplete.getPlace();
            const destinationPlace = destinationAutocomplete.getPlace();

            if (sourcePlace && sourcePlace.geometry && destinationPlace && destinationPlace.geometry) {
                const request = {
                    origin: sourcePlace.geometry.location,
                    destination: destinationPlace.geometry.location,
                    travelMode: google.maps.TravelMode.DRIVING,
                    provideRouteAlternatives: true
                };

                directionsService.route(request, function(result, status) {
                    if (status === 'OK') {
                        routes = result.routes;
                        currentRoute = 0;
                        
                        // Update route selector
                        const routeSelector = document.getElementById('routeSelector');
                        if (routeSelector) {
                            routeSelector.innerHTML = '';
                            routes.forEach((route, index) => {
                                const option = document.createElement('option');
                                option.value = index;
                                option.textContent = `Route ${index + 1} (${route.legs[0].distance.text})`;
                                routeSelector.appendChild(option);
                            });
                        }
                        
                        showRoute(routes[currentRoute]);
                    } else {
                        console.error('Directions request failed:', status);
                        if (distanceSpan) {
                            distanceSpan.innerHTML = `
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                                    <p>Could not find a route between these cities. Please try different locations.</p>
                                </div>
                            `;
                        }
                    }
                });
            }
        }

        function showRoute(route) {
            if (!isMapInitialized) {
                console.error('Map not initialized');
                return;
            }

            try {
                directionsRenderer.setDirections({ routes: [route] });
                
                const leg = route.legs[0];
                const distance = leg.distance;
                const duration = leg.duration;
                
                // Calculate fuel costs
                const distanceInKm = distance.value / 1000;
                const petrolCost = (distanceInKm / FUEL_EFFICIENCY.petrol) * FUEL_PRICES.petrol;
                const dieselCost = (distanceInKm / FUEL_EFFICIENCY.diesel) * FUEL_PRICES.diesel;
                const cngCost = (distanceInKm / FUEL_EFFICIENCY.cng) * FUEL_PRICES.cng;
                
                // Check for toll roads
                const hasTolls = route.legs[0].steps.some(step => step.tolls);
                
                if (distanceSpan) {
                    distanceSpan.innerHTML = `
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900">Route Information</h4>
                                    <p class="text-sm text-gray-600">Distance: ${distance.text}</p>
                                    <p class="text-sm text-gray-600">Duration: ${duration.text}</p>
                                    ${hasTolls ? '<p class="text-sm text-yellow-600">⚠️ This route includes toll roads</p>' : ''}
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Estimated Fuel Cost</h4>
                                    <p class="text-sm text-gray-600">Petrol: ₹${Math.round(petrolCost)}</p>
                                    <p class="text-sm text-gray-600">Diesel: ₹${Math.round(dieselCost)}</p>
                                    <p class="text-sm text-gray-600">CNG: ₹${Math.round(cngCost)}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error showing route:', error);
                if (distanceSpan) {
                    distanceSpan.innerHTML = `
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                            <p>Error displaying route information: ${error.message}</p>
                        </div>
                    `;
                }
            }
        }

        function showError(message) {
            debug(`Showing error: ${message}`);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4';
            errorDiv.innerHTML = `
                <p>${message}</p>
                <button onclick="this.parentElement.remove()" class="mt-2 text-sm text-red-600 hover:text-red-800">
                    Dismiss
                </button>
            `;
            
            const form = document.querySelector('form');
            if (form) {
                form.insertBefore(errorDiv, form.firstChild);
            }
        }

        // Initialize when the page loads
        window.onload = function() {
            debug('Page loaded, starting Google Maps initialization');
            loadGoogleMaps();
        };
    </script>

</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>

    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- Header Section -->
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold text-gray-900">Offer a Ride</h1>
                <p class="mt-2 text-gray-600">Share your journey and help others travel conveniently</p>
            </div>

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

            <!-- Main Form -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <form method="POST" class="p-6 space-y-8">
                    <!-- Route Information -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900">Route Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="source" class="block text-sm font-medium text-gray-700">From</label>
                                <input type="text" id="source" name="source" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6] px-4 py-3"
                                    placeholder="Enter source city">
                                <input type="hidden" id="source_formatted" name="source_formatted">
                            </div>
                            <div>
                                <label for="destination" class="block text-sm font-medium text-gray-700">To</label>
                                <input type="text" id="destination" name="destination" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6] px-4 py-3"
                                    placeholder="Enter destination city">
                                <input type="hidden" id="destination_formatted" name="destination_formatted">
                            </div>
                        </div>
                        
                        <!-- Map Preview -->
                        <div class="mt-4 space-y-4">
                            <div class="flex justify-between items-center">
                                <label for="routeSelector" class="block text-sm font-medium text-gray-700">Select Route</label>
                                <select id="routeSelector" class="rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6] px-4 py-2">
                                    <option value="0">Select a route...</option>
                                </select>
                            </div>
                            <div id="mapLoading" class="w-full h-64 rounded-lg shadow-md flex items-center justify-center bg-gray-50">
                                <div class="text-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#8B5CF6] mx-auto"></div>
                                    <p class="mt-2 text-gray-600">Loading map...</p>
                                </div>
                            </div>
                            <div id="map" class="w-full h-64 rounded-lg shadow-md" style="display: none;"></div>
                            <div id="distance" class="mt-2"></div>
                        </div>
                    </div>

                    <!-- Timing and Seats -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900">Timing and Seats</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="departure_time" class="block text-sm font-medium text-gray-700">Departure Time</label>
                                <input type="datetime-local" id="departure_time" name="departure_time" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6] px-4 py-3">
                            </div>
                            <div>
                                <label for="available_seats" class="block text-sm font-medium text-gray-700">Available Seats</label>
                                <input type="number" id="available_seats" name="available_seats" min="1" max="7" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6] px-4 py-3">
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900">Pricing</h3>
                        <div>
                            <label for="price_per_seat" class="block text-sm font-medium text-gray-700">Price per Seat (₹)</label>
                            <div class="mt-1 relative rounded-lg shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₹</span>
                                </div>
                                <input type="number" id="price_per_seat" name="price_per_seat" min="0" step="10" required
                                    class="block w-full pl-7 rounded-lg border-gray-300 focus:border-[#8B5CF6] focus:ring-[#8B5CF6] px-4 py-3"
                                    placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Information -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900">Vehicle Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="car_model" class="block text-sm font-medium text-gray-700">Car Model</label>
                                <input type="text" id="car_model" name="car_model"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6] px-4 py-3"
                                    placeholder="Enter car model">
                            </div>
                            <div>
                                <label for="car_number" class="block text-sm font-medium text-gray-700">Car Number</label>
                                <input type="text" id="car_number" name="car_number"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6] px-4 py-3"
                                    placeholder="Enter car number">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900">Additional Information</h3>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="4"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6] px-4 py-3"
                                placeholder="Add any additional information about your ride"></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-6 py-3 bg-[#8B5CF6] text-white rounded-lg hover:bg-[#7C3AED] transition-colors duration-200">
                            Offer Ride
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>
</html> 