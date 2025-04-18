<?php
session_start();
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - CabShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="relative bg-[#F5F3FF] py-12">
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-[#8B5CF6] opacity-90"></div>
        </div>
        <div class="relative max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">Terms and Conditions</h1>
            <p class="mt-4 text-xl text-white max-w-3xl mx-auto">
                Please read these terms carefully before using our services.
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- Table of Contents -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Table of Contents</h2>
            <ul class="list-disc list-inside space-y-2 text-[#8B5CF6]">
                <li><a href="#terms" class="hover:underline">Terms of Service</a></li>
                <li><a href="#privacy" class="hover:underline">Privacy Policy</a></li>
                <li><a href="#user-agreement" class="hover:underline">User Agreement</a></li>
                <li><a href="#cancellation" class="hover:underline">Cancellation Policy</a></li>
                <li><a href="#liability" class="hover:underline">Liability and Insurance</a></li>
            </ul>
        </div>

        <!-- Terms of Service -->
        <div id="terms" class="bg-white p-8 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Terms of Service</h2>
            <div class="space-y-4 text-gray-600">
                <p>Last updated: <?php echo date('F d, Y'); ?></p>
                <p>Welcome to CabShare. By accessing or using our services, you agree to be bound by these Terms of Service.</p>
                
                <h3 class="text-xl font-semibold text-gray-900 mt-6">1. Service Description</h3>
                <p>CabShare provides a platform for connecting drivers and passengers for shared rides. We do not provide transportation services but act as an intermediary between drivers and passengers.</p>

                <h3 class="text-xl font-semibold text-gray-900 mt-6">2. User Responsibilities</h3>
                <ul class="list-disc list-inside space-y-2">
                    <li>Provide accurate and complete information</li>
                    <li>Maintain the security of your account</li>
                    <li>Comply with all applicable laws and regulations</li>
                    <li>Treat other users with respect</li>
                </ul>
            </div>
        </div>

        <!-- Privacy Policy -->
        <div id="privacy" class="bg-white p-8 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Privacy Policy</h2>
            <div class="space-y-4 text-gray-600">
                <h3 class="text-xl font-semibold text-gray-900">1. Information We Collect</h3>
                <p>We collect information that you provide directly to us, including:</p>
                <ul class="list-disc list-inside space-y-2">
                    <li>Name, email, and phone number</li>
                    <li>Payment information</li>
                    <li>Location data</li>
                    <li>Communication preferences</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mt-6">2. How We Use Your Information</h3>
                <p>We use the collected information to:</p>
                <ul class="list-disc list-inside space-y-2">
                    <li>Provide and improve our services</li>
                    <li>Process transactions</li>
                    <li>Send important updates</li>
                    <li>Ensure safety and security</li>
                </ul>
            </div>
        </div>

        <!-- User Agreement -->
        <div id="user-agreement" class="bg-white p-8 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">User Agreement</h2>
            <div class="space-y-4 text-gray-600">
                <h3 class="text-xl font-semibold text-gray-900">1. Account Creation</h3>
                <p>To use our services, you must:</p>
                <ul class="list-disc list-inside space-y-2">
                    <li>Be at least 18 years old</li>
                    <li>Provide accurate information</li>
                    <li>Maintain account security</li>
                    <li>Accept these terms</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mt-6">2. Prohibited Activities</h3>
                <p>Users are prohibited from:</p>
                <ul class="list-disc list-inside space-y-2">
                    <li>Sharing account credentials</li>
                    <li>Using the service for illegal purposes</li>
                    <li>Harassing other users</li>
                    <li>Violating safety guidelines</li>
                </ul>
            </div>
        </div>

        <!-- Cancellation Policy -->
        <div id="cancellation" class="bg-white p-8 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Cancellation Policy</h2>
            <div class="space-y-4 text-gray-600">
                <h3 class="text-xl font-semibold text-gray-900">1. Ride Cancellations</h3>
                <p>Cancellation fees may apply based on timing:</p>
                <ul class="list-disc list-inside space-y-2">
                    <li>No fee if cancelled 24 hours before ride</li>
                    <li>50% fee if cancelled within 24 hours</li>
                    <li>Full fee if cancelled within 2 hours</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mt-6">2. Refund Process</h3>
                <p>Refunds will be processed within 5-7 business days to the original payment method.</p>
            </div>
        </div>

        <!-- Liability and Insurance -->
        <div id="liability" class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Liability and Insurance</h2>
            <div class="space-y-4 text-gray-600">
                <h3 class="text-xl font-semibold text-gray-900">1. Insurance Coverage</h3>
                <p>All rides are covered by:</p>
                <ul class="list-disc list-inside space-y-2">
                    <li>Third-party liability insurance</li>
                    <li>Accident coverage</li>
                    <li>Property damage protection</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mt-6">2. User Responsibilities</h3>
                <p>Users are responsible for:</p>
                <ul class="list-disc list-inside space-y-2">
                    <li>Following safety guidelines</li>
                    <li>Reporting incidents immediately</li>
                    <li>Cooperating with investigations</li>
                </ul>
            </div>
        </div>

        <!-- Acceptance Section -->
        <div class="mt-12 text-center">
            <p class="text-gray-600">By using our services, you acknowledge that you have read and agree to these terms and conditions.</p>
            <p class="text-gray-600 mt-2">Last updated: <?php echo date('F d, Y'); ?></p>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>
</html> 