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
    <div class="bg-white  min-h-[600px]">
    <div class="max-w-7xl mx-auto px-4 py-20 grid grid-cols-1 md:grid-cols-2 items-center gap-8 ">
        <!-- Left Column - Text -->
        <div class="text-center md:text-left relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Share Your Journey</h1>
            <p class="text-xl mb-8">Find or offer rides, save money, and make new connections</p>
            <div class="flex flex-col md:flex-row gap-4 justify-center md:justify-start">
                <a href="find-ride.php" class="bg-[#8B5CF6] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#7C3AED] transition">Find a Ride</a>
                <a href="offer-ride.php" class="bg-transparent border-2 border-black px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-[#8B5CF6] transition">Offer a Ride</a>
            </div>
        </div>

        <!-- Right Column - Image -->
        <div class="relative">
            <img src="assets/car.png" alt="Hero Image" class="w-full h-full object-cover rounded-lg shadow-lg opacity-100">
        </div>
    </div>
</div>



    <!-- Features Section -->
    <div class="max-w-7xl mx-auto px-4 py-16">
        <h2 class="text-4xl font-bold text-center mb-4">Why Choose <span class="text-[#8B5CF6]">CabShare</span>?</h2>
        <p class="text-gray-600 text-center text-lg mb-16 max-w-3xl mx-auto">Our platform makes ride-sharing simple, affordable and secure. Discover the benefits of sharing your journey with others.</p>
        
        <div class="grid md:grid-cols-3 gap-8">
            <!-- Split Fares Card -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="w-12 h-12 bg-[#8B5CF6]/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="fas fa-dollar-sign text-[#8B5CF6] text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Split Fares</h3>
                <p class="text-gray-600">Divide cab costs evenly among passengers and save up to 60% on your daily commute.</p>
            </div>

            <!-- Save Time Card -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="w-12 h-12 bg-[#8B5CF6]/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="fas fa-clock text-[#8B5CF6] text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Save Time</h3>
                <p class="text-gray-600">Optimize your route with others going the same way and reduce wait times.</p>
            </div>

            <!-- Verified Users Card -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="w-12 h-12 bg-[#8B5CF6]/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="fas fa-shield-alt text-[#8B5CF6] text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Verified Users</h3>
                <p class="text-gray-600">Feel secure with our thorough verification process for all platform members.</p>
            </div>

            <!-- Route Matching Card -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="w-12 h-12 bg-[#8B5CF6]/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="fas fa-map-marker-alt text-[#8B5CF6] text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Route Matching</h3>
                <p class="text-gray-600">Our smart algorithm finds the perfect match for your route and schedule.</p>
            </div>

            <!-- Community Card -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="w-12 h-12 bg-[#8B5CF6]/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="fas fa-users text-[#8B5CF6] text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Community</h3>
                <p class="text-gray-600">Join a community of like-minded commuters and expand your professional network.</p>
            </div>

            <!-- Easy Communication Card -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                <div class="w-12 h-12 bg-[#8B5CF6]/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="fas fa-comments text-[#8B5CF6] text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Easy Communication</h3>
                <p class="text-gray-600">Chat with potential ride-shares before confirming your journey together.</p>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="bg-[#F5F3FF] py-20">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-4">How <span class="text-[#8B5CF6]">CabShare</span> Works</h2>
            <p class="text-gray-600 text-center text-lg mb-16 max-w-3xl mx-auto">Get started with CabShare in just a few simple steps and transform your daily commute.</p>
            
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="relative w-24 h-24 bg-[#8B5CF6] rounded-full mx-auto mb-6 flex items-center justify-center">
                        <span class="absolute -top-2 -right-2 w-8 h-8 bg-[#8B5CF6] rounded-full flex items-center justify-center text-white font-bold">1</span>
                        <i class="fas fa-map-marker-alt text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Enter Your Route</h3>
                    <p class="text-gray-600">Input your pickup and drop-off locations along with your preferred time.</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="relative w-24 h-24 bg-[#8B5CF6] rounded-full mx-auto mb-6 flex items-center justify-center">
                        <span class="absolute -top-2 -right-2 w-8 h-8 bg-[#8B5CF6] rounded-full flex items-center justify-center text-white font-bold">2</span>
                        <i class="fas fa-search text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Find Matches</h3>
                    <p class="text-gray-600">Browse through potential cab-share partners going your way.</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="relative w-24 h-24 bg-[#8B5CF6] rounded-full mx-auto mb-6 flex items-center justify-center">
                        <span class="absolute -top-2 -right-2 w-8 h-8 bg-[#8B5CF6] rounded-full flex items-center justify-center text-white font-bold">3</span>
                        <i class="fas fa-calendar-check text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Book Your Ride</h3>
                    <p class="text-gray-600">Schedule your journey and confirm details with your cab-share partners.</p>
                </div>

                <!-- Step 4 -->
                <div class="text-center">
                    <div class="relative w-24 h-24 bg-[#8B5CF6] rounded-full mx-auto mb-6 flex items-center justify-center">
                        <span class="absolute -top-2 -right-2 w-8 h-8 bg-[#8B5CF6] rounded-full flex items-center justify-center text-white font-bold">4</span>
                        <i class="fas fa-car text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Share & Save</h3>
                    <p class="text-gray-600">Enjoy your ride and split the fare automatically through our app.</p>
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
                        <li><a href="about.php" class="text-gray-400 hover:text-[#8B5CF6]">About Us</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-[#8B5CF6]">Contact</a></li>
                        <li><a href="terms.php" class="text-gray-400 hover:text-[#8B5CF6]">Terms & Conditions</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Connect With Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-[#8B5CF6]"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-[#8B5CF6]"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-[#8B5CF6]"><i class="fab fa-instagram"></i></a>
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