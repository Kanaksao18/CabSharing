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
    if (isset($_POST['update_profile'])) {
        try {
            // Validate input
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            // Get current user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user']['id']]);
            $user = $stmt->fetch();

            // Verify current password if changing password
            if (!empty($current_password)) {
                if (!password_verify($current_password, $user['password'])) {
                    throw new Exception('Current password is incorrect');
                }
                if ($new_password !== $confirm_password) {
                    throw new Exception('New passwords do not match');
                }
                if (strlen($new_password) < 8) {
                    throw new Exception('New password must be at least 8 characters long');
                }
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            }

            // Update user profile
            $sql = "UPDATE users SET name = ?, email = ?, phone = ?";
            $params = [$name, $email, $phone];

            if (!empty($current_password)) {
                $sql .= ", password = ?";
                $params[] = $password_hash;
            }

            $sql .= " WHERE id = ?";
            $params[] = $_SESSION['user']['id'];

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Update session data
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;

            $success_message = 'Profile updated successfully!';
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    }
}

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Admin Dashboard</title>
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
                                <a href="settings.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Settings</a>
                                <a href="profile.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">Profile</a>
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
                            Profile Settings
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

                <div class="mt-8">
                    <div class="md:grid md:grid-cols-3 md:gap-6">
                        <div class="md:col-span-1">
                            <div class="px-4 sm:px-0">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Profile Information</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Update your personal information and account settings.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 md:mt-0 md:col-span-2">
                            <form method="POST">
                                <div class="shadow sm:rounded-md sm:overflow-hidden">
                                    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                                        <div class="grid grid-cols-6 gap-6">
                                            <div class="col-span-6 sm:col-span-4">
                                                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>"
                                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            </div>

                                            <div class="col-span-6 sm:col-span-4">
                                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            </div>

                                            <div class="col-span-6 sm:col-span-4">
                                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                                <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"
                                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                        </div>

                                        <div class="border-t border-gray-200 pt-6">
                                            <h3 class="text-lg font-medium leading-6 text-gray-900">Change Password</h3>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Leave these fields blank if you don't want to change your password.
                                            </p>

                                            <div class="mt-6 grid grid-cols-6 gap-6">
                                                <div class="col-span-6 sm:col-span-4">
                                                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                                    <input type="password" name="current_password" id="current_password"
                                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                                </div>

                                                <div class="col-span-6 sm:col-span-4">
                                                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                                                    <input type="password" name="new_password" id="new_password"
                                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                                </div>

                                                <div class="col-span-6 sm:col-span-4">
                                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                                    <input type="password" name="confirm_password" id="confirm_password"
                                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                        <button type="submit" name="update_profile"
                                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 