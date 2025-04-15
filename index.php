<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CabShare - Share Rides, Save Money</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>
    
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-green-500 to-green-700 text-white">
        <div class="max-w-7xl mx-auto px-4 py-24 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">Share Your Journey</h1>
            <p class="text-xl mb-8">Find or offer rides, save money, and make new connections</p>
            <div class="flex flex-col md:flex-row gap-4 justify-center">
                <a href="find-ride.php" class="bg-white text-green-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">Find a Ride</a>
                <a href="offer-ride.php" class="bg-transparent border-2 border-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-green-600 transition">Offer a Ride</a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="max-w-7xl mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-center mb-12">Why Choose CabShare?</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <i class="fas fa-money-bill-wave text-4xl text-green-500 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">Save Money</h3>
                <p class="text-gray-600">Split the cost of your journey with fellow travelers</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <i class="fas fa-users text-4xl text-green-500 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">Meet People</h3>
                <p class="text-gray-600">Connect with like-minded travelers in your area</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <i class="fas fa-leaf text-4xl text-green-500 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">Eco-Friendly</h3>
                <p class="text-gray-600">Reduce your carbon footprint by sharing rides</p>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 py-16">
            <h2 class="text-3xl font-bold text-center mb-12">How It Works</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-green-500">1</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Create an Account</h3>
                    <p class="text-gray-600">Sign up and complete your profile</p>
                </div>
                <div class="text-center">
                    <div class="bg-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-green-500">2</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Find or Offer a Ride</h3>
                    <p class="text-gray-600">Search for rides or post your own journey</p>
                </div>
                <div class="text-center">
                    <div class="bg-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-green-500">3</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Connect & Travel</h3>
                    <p class="text-gray-600">Message your ride partner and hit the road</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-semibold mb-4">CabShare</h3>
                    <p class="text-gray-400">Making travel more affordable and sustainable</p>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
                        <li><a href="terms.php" class="text-gray-400 hover:text-white">Terms & Conditions</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Connect With Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 CabShare. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>