<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<nav class="bg-white shadow-md">
  <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
    <a href="index.php" class="flex items-center space-x-2 text-xl font-semibold text-gray-900">
      
      <span>CabShare</span>
    </a>

    <!-- Desktop Navigation -->
    <div class="hidden md:flex space-x-8">
      <a href="index.php" class="hover:text-green-600 font-medium">Home</a>
      <a href="offer-ride.php" class="hover:text-green-600 font-medium">Offer Ride</a>
      <a href="find-ride.php" class="hover:text-green-600 font-medium">Find Ride</a>
      <a href="my-rides.php" class="hover:text-green-600 font-medium">My Rides</a>
      <a href="profile.php" class="hover:text-green-600 font-medium">Profile</a>
    </div>

    <!-- Right Section -->
    <div class="flex items-center space-x-4">
      <input type="text" placeholder="Search..." class="hidden md:block px-3 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-400">

      <?php if (isset($_SESSION['user'])): ?>
  <span class="text-gray-700 font-medium hidden sm:inline">Hello, <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
  <a href="logout.php" class="font-medium text-red-500 hover:text-red-700">Logout</a>
<?php else: ?>
  <a href="login.php" class="font-medium hover:text-green-600">Login</a>
  <a href="register.php" class="font-medium hover:text-green-600">Register</a>
<?php endif; ?>

    </div>
  </div>
</nav>
