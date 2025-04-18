<?php
session_start();
require_once 'config/database.php';

// Initialize database connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $error = "Database connection failed: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $success = false;
    $error = "";

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Insert into database
        try {
            if (isset($conn)) {
                $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $email, $subject, $message]);
                $success = true;
            } else {
                $error = "Database connection is not available.";
            }
        } catch(PDOException $e) {
            $error = "Something went wrong. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - CabShare</title>
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
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">Get in Touch</h1>
            <p class="mt-4 text-xl text-white max-w-3xl mx-auto">
                Have questions or suggestions? We're here to help and listen to your feedback.
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- Contact Information Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <!-- Email Card -->
            <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-[#F5F3FF] rounded-full flex items-center justify-center">
                        <i class="fas fa-envelope text-[#8B5CF6] text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Email Us</h3>
                <p class="text-gray-600 text-center">support@cabshare.com</p>
                <p class="text-gray-600 text-center">info@cabshare.com</p>
            </div>

            <!-- Phone Card -->
            <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-[#F5F3FF] rounded-full flex items-center justify-center">
                        <i class="fas fa-phone text-[#8B5CF6] text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Call Us</h3>
                <p class="text-gray-600 text-center">+91 1234567890</p>
                <p class="text-gray-600 text-center">Mon-Fri, 9am-6pm IST</p>
            </div>

            <!-- Office Card -->
            <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-[#F5F3FF] rounded-full flex items-center justify-center">
                        <i class="fas fa-map-marker-alt text-[#8B5CF6] text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Visit Us</h3>
                <p class="text-gray-600 text-center">123 Tech Park</p>
                <p class="text-gray-600 text-center">Bangalore, India</p>
            </div>
        </div>

        <!-- Contact Form and Map Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Contact Form -->
            <div class="bg-white p-8 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h2>
                
                <?php if (isset($success) && $success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        Thank you for your message. We'll get back to you soon!
                    </div>
                <?php endif; ?>

                <?php if (isset($error) && !empty($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" id="name" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6] text-gray-900 px-4 py-3">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6] text-gray-900 px-4 py-3">
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" id="subject" name="subject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6] text-gray-900 px-4 py-3">
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea id="message" name="message" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#8B5CF6] focus:ring-[#8B5CF6] text-gray-900 px-4 py-3"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-[#F5F3FF] text-[#8B5CF6] py-3 px-6 rounded-md hover:bg-white hover:shadow-lg transform hover:scale-105 transition-all duration-300 font-medium focus:outline-none focus:ring-2 focus:ring-[#8B5CF6] focus:ring-offset-2">
                        Send Message
                    </button>
                </form>
            </div>

            <!-- Map Section -->
            <div class="bg-white p-8 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Our Location</h2>
                <div class="w-full h-[400px] rounded-lg overflow-hidden">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3887.985619587854!2d77.59796531482292!3d12.971598890855892!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae1670c9b44e6d%3A0xf8dfc3e8517e4fe0!2sBengaluru%2C%20Karnataka%2C%20India!5e0!3m2!1sen!2sin!4v1647850821114!5m2!1sen!2sin"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Frequently Asked Questions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">How do I report an issue?</h3>
                    <p class="text-gray-600">You can report any issues through our contact form above or email us directly at support@cabshare.com. Our support team is available 24/7 to assist you.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">What are your support hours?</h3>
                    <p class="text-gray-600">Our customer support team is available Monday through Friday, 9 AM to 6 PM IST. For urgent matters, we provide 24/7 emergency support.</p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>
</html> 