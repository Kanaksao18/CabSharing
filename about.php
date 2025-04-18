<?php
session_start();
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - CabShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="assets/js/counter.js"></script>
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="relative bg-[#F5F3FF] py-12">
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-[#8B5CF6] "></div>
        </div>
        <div class="relative max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">About CabShare</h1>
            <p class="mt-4 text-lg text-white max-w-3xl">
                Revolutionizing the way people share rides and travel together.
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- Our Story and Image -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Our Story</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white p-8 rounded-lg shadow-md">
                    <p class="text-lg text-gray-600 mb-4">
                        CabShare was founded in 2023 with a simple mission: to make travel more affordable, sustainable, and social. We believe that by connecting people who are going the same way, we can reduce traffic congestion, lower carbon emissions, and create meaningful connections between travelers.
                    </p>
                    <p class="text-lg text-gray-600">
                        What started as a small project has grown into a trusted platform used by thousands of people every day to share rides across cities and states. Our community of drivers and passengers continues to grow, making travel more accessible and enjoyable for everyone.
                    </p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <img src="assets/images/about-image.jpg" alt="CabShare Community" class="w-full h-full object-cover rounded-lg">
                </div>
            </div>
        </div>

        <!-- Our Impact -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Our Impact</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Rides Shared Card -->
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                    <div class="flex items-center justify-center mb-6">
                        <div class="w-16 h-16 bg-[#F5F3FF] rounded-lg flex items-center justify-center">
                            <i class="fas fa-car text-[#8B5CF6] text-2xl"></i>
                        </div>
                    </div>
                    <div class="text-center">
                        <p id="rides-counter" class="text-3xl font-bold text-gray-900 mb-2">0+</p>
                        <p class="text-lg text-gray-600">Rides Shared</p>
                    </div>
                </div>

                <!-- Active Users Card -->
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                    <div class="flex items-center justify-center mb-6">
                        <div class="w-16 h-16 bg-[#F5F3FF] rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-[#8B5CF6] text-2xl"></i>
                        </div>
                    </div>
                    <div class="text-center">
                        <p id="users-counter" class="text-3xl font-bold text-gray-900 mb-2">0+</p>
                        <p class="text-lg text-gray-600">Active Users</p>
                    </div>
                </div>

                <!-- Kilometers Saved Card -->
                <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                    <div class="flex items-center justify-center mb-6">
                        <div class="w-16 h-16 bg-[#F5F3FF] rounded-lg flex items-center justify-center">
                            <i class="fas fa-leaf text-[#8B5CF6] text-2xl"></i>
                        </div>
                    </div>
                    <div class="text-center">
                        <p id="kilometers-counter" class="text-3xl font-bold text-gray-900 mb-2">0+</p>
                        <p class="text-lg text-gray-600">Kilometers Saved</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Our Mission -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Our Mission</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="text-[#8B5CF6] text-3xl mb-4">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Connect People</h3>
                    <p class="text-gray-600">Bringing together travelers going the same way, creating opportunities for meaningful connections and shared experiences.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="text-[#8B5CF6] text-3xl mb-4">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Reduce Impact</h3>
                    <p class="text-gray-600">Minimizing carbon footprint by optimizing vehicle occupancy and reducing the number of cars on the road.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="text-[#8B5CF6] text-3xl mb-4">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Save Money</h3>
                    <p class="text-gray-600">Making travel more affordable by sharing costs between drivers and passengers.</p>
                </div>
            </div>
        </div>

        <!-- Our Values -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Our Values</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Safety First</h3>
                    <p class="text-gray-600 mb-4">We prioritize the safety of our community through:</p>
                    <ul class="list-disc list-inside text-gray-600 space-y-2">
                        <li>Driver verification process</li>
                        <li>User reviews and ratings</li>
                        <li>24/7 support system</li>
                        <li>Emergency assistance</li>
                    </ul>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Community Focus</h3>
                    <p class="text-gray-600 mb-4">We build trust through:</p>
                    <ul class="list-disc list-inside text-gray-600 space-y-2">
                        <li>Transparent pricing</li>
                        <li>Clear communication</li>
                        <li>User feedback integration</li>
                        <li>Community guidelines</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="bg-[#8B5CF6] rounded-lg p-8 text-white">
            <h2 class="text-3xl font-bold mb-6">Get in Touch</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <p class="text-lg mb-4">Have questions or suggestions? We'd love to hear from you!</p>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-2xl mr-4"></i>
                            <p>support@cabshare.com</p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone text-2xl mr-4"></i>
                            <p>+91 1234567890</p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-2xl mr-4"></i>
                            <p>123 Tech Park, Bangalore, India</p>
                        </div>
                    </div>
                </div>
                <div>
                    <form class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium">Name</label>
                            <input type="text" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white text-gray-900 px-4 py-3">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium">Email</label>
                            <input type="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white text-gray-900 px-4 py-3">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium">Message</label>
                            <textarea id="message" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white text-gray-900 px-4 py-3"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-[#F5F3FF] text-[#8B5CF6] py-3 px-6 rounded-md hover:bg-white hover:shadow-lg transform hover:scale-105 transition-all duration-300 font-medium focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-[#8B5CF6]">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>
</html> 