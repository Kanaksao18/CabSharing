<?php
include 'config.php';
include 'components/header.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $gender   = $_POST['gender'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $_SESSION['error'] = "⚠️ Account already exists. Please log in.";
        header("Location: login.php");
        exit;
    }

    // Register new user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, gender, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $gender, $password]);

    $_SESSION['success'] = "✅ Account created successfully. Please log in.";
    header("Location: login.php");
    exit;
}
?>

<?php include 'components/navbar.php'; ?>

<div class="max-w-md mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Register</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <p class="text-red-500"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <input name="name" required placeholder="Name" class="w-full p-2 border">
        <input name="email" type="email" required placeholder="Email" class="w-full p-2 border">
        <input name="phone" required placeholder="Phone" class="w-full p-2 border">
        
        <select name="gender" required class="w-full p-2 border">
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
        </select>

        <input name="password" type="password" required placeholder="Password" class="w-full p-2 border">

        <button class="bg-green-600 text-white px-4 py-2 rounded">Register</button>
    </form>
</div>
