<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        try {
            // Update system settings
            $settings = [
                'site_name' => $_POST['site_name'],
                'site_description' => $_POST['site_description'],
                'contact_email' => $_POST['contact_email'],
                'max_seats_per_ride' => (int)$_POST['max_seats_per_ride'],
                'min_ride_price' => (float)$_POST['min_ride_price'],
                'max_ride_price' => (float)$_POST['max_ride_price'],
                'commission_rate' => (float)$_POST['commission_rate'],
                'driver_verification_required' => isset($_POST['driver_verification_required']) ? 1 : 0,
                'auto_approve_drivers' => isset($_POST['auto_approve_drivers']) ? 1 : 0,
                'enable_reviews' => isset($_POST['enable_reviews']) ? 1 : 0,
                'enable_ratings' => isset($_POST['enable_ratings']) ? 1 : 0,
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0
            ];

            foreach ($settings as $key => $value) {
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) 
                                     VALUES (?, ?) 
                                     ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$key, $value, $value]);
            }

            $success_message = 'Settings updated successfully!';
        } catch (PDOException $e) {
            $error_message = 'Error updating settings: ' . $e->getMessage();
        }
    }
}

// Get current settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$current_settings = [];
while ($row = $stmt->fetch()) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}

// Set default values if not set
$settings = [
    'site_name' => $current_settings['site_name'] ?? 'CabShare',
    'site_description' => $current_settings['site_description'] ?? 'Ride Sharing Platform',
    'contact_email' => $current_settings['contact_email'] ?? 'contact@cabshare.com',
    'max_seats_per_ride' => $current_settings['max_seats_per_ride'] ?? 4,
    'min_ride_price' => $current_settings['min_ride_price'] ?? 50,
    'max_ride_price' => $current_settings['max_ride_price'] ?? 5000,
    'commission_rate' => $current_settings['commission_rate'] ?? 10,
    'driver_verification_required' => $current_settings['driver_verification_required'] ?? 1,
    'auto_approve_drivers' => $current_settings['auto_approve_drivers'] ?? 0,
    'enable_reviews' => $current_settings['enable_reviews'] ?? 1,
    'enable_ratings' => $current_settings['enable_ratings'] ?? 1,
    'maintenance_mode' => $current_settings['maintenance_mode'] ?? 0
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Dashboard</title>
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
                                <a href="reviews.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Reviews</a>
                                <a href="settings.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Settings</a>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-300 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
                        <a href="logout.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            System Settings
                        </h2>
                    </div>
                </div>

                <?php if ($success_message): ?>
                    <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline"><?php echo $success_message; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline"><?php echo $error_message; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="mt-8 space-y-8 divide-y divide-gray-200">
                    <div class="space-y-8 divide-y divide-gray-200">
                        <!-- General Settings -->
                        <div class="pt-8">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">General Settings</h3>
                                <p class="mt-1 text-sm text-gray-500">Basic information about your platform.</p>
                            </div>
                            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-4">
                                    <label for="site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                                    <div class="mt-1">
                                        <input type="text" name="site_name" id="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>"
                                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="site_description" class="block text-sm font-medium text-gray-700">Site Description</label>
                                    <div class="mt-1">
                                        <textarea name="site_description" id="site_description" rows="3"
                                                  class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                                    </div>
                                </div>

                                <div class="sm:col-span-4">
                                    <label for="contact_email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                                    <div class="mt-1">
                                        <input type="email" name="contact_email" id="contact_email" value="<?php echo htmlspecialchars($settings['contact_email']); ?>"
                                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ride Settings -->
                        <div class="pt-8">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Ride Settings</h3>
                                <p class="mt-1 text-sm text-gray-500">Configure ride-related parameters.</p>
                            </div>
                            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-2">
                                    <label for="max_seats_per_ride" class="block text-sm font-medium text-gray-700">Max Seats per Ride</label>
                                    <div class="mt-1">
                                        <input type="number" name="max_seats_per_ride" id="max_seats_per_ride" value="<?php echo $settings['max_seats_per_ride']; ?>"
                                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="min_ride_price" class="block text-sm font-medium text-gray-700">Min Ride Price (₹)</label>
                                    <div class="mt-1">
                                        <input type="number" name="min_ride_price" id="min_ride_price" value="<?php echo $settings['min_ride_price']; ?>"
                                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="max_ride_price" class="block text-sm font-medium text-gray-700">Max Ride Price (₹)</label>
                                    <div class="mt-1">
                                        <input type="number" name="max_ride_price" id="max_ride_price" value="<?php echo $settings['max_ride_price']; ?>"
                                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="commission_rate" class="block text-sm font-medium text-gray-700">Commission Rate (%)</label>
                                    <div class="mt-1">
                                        <input type="number" name="commission_rate" id="commission_rate" value="<?php echo $settings['commission_rate']; ?>"
                                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Driver Settings -->
                        <div class="pt-8">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Driver Settings</h3>
                                <p class="mt-1 text-sm text-gray-500">Configure driver-related settings.</p>
                            </div>
                            <div class="mt-6 space-y-6">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="driver_verification_required" id="driver_verification_required"
                                               <?php echo $settings['driver_verification_required'] ? 'checked' : ''; ?>
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="driver_verification_required" class="font-medium text-gray-700">Require Driver Verification</label>
                                        <p class="text-gray-500">Drivers must submit verification documents before being approved.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="auto_approve_drivers" id="auto_approve_drivers"
                                               <?php echo $settings['auto_approve_drivers'] ? 'checked' : ''; ?>
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="auto_approve_drivers" class="font-medium text-gray-700">Auto-approve Drivers</label>
                                        <p class="text-gray-500">Automatically approve new driver registrations.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Review Settings -->
                        <div class="pt-8">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Review Settings</h3>
                                <p class="mt-1 text-sm text-gray-500">Configure review and rating settings.</p>
                            </div>
                            <div class="mt-6 space-y-6">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="enable_reviews" id="enable_reviews"
                                               <?php echo $settings['enable_reviews'] ? 'checked' : ''; ?>
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="enable_reviews" class="font-medium text-gray-700">Enable Reviews</label>
                                        <p class="text-gray-500">Allow users to write reviews for rides.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="enable_ratings" id="enable_ratings"
                                               <?php echo $settings['enable_ratings'] ? 'checked' : ''; ?>
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="enable_ratings" class="font-medium text-gray-700">Enable Ratings</label>
                                        <p class="text-gray-500">Allow users to rate rides with stars.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Settings -->
                        <div class="pt-8">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">System Settings</h3>
                                <p class="mt-1 text-sm text-gray-500">Configure system-wide settings.</p>
                            </div>
                            <div class="mt-6 space-y-6">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="maintenance_mode" id="maintenance_mode"
                                               <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="maintenance_mode" class="font-medium text-gray-700">Maintenance Mode</label>
                                        <p class="text-gray-500">Put the site in maintenance mode. Only administrators can access the site.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-5">
                        <div class="flex justify-end">
                            <button type="submit" name="update_settings"
                                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 