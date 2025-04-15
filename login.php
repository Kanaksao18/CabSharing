<?php
session_start();
include 'config.php';
include 'components/header.php';

$success = '';
$error = '';



if (!empty($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (!empty($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
          ];
          
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}

?>

<?php include 'components/navbar.php'; ?>
<div class="max-w-md mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Login</h2>

    <?php if (!empty($success)): ?>
  <p class="text-green-500 mb-2"><?= $success ?></p>
<?php endif; ?>

   
<?php if (!empty($error)): ?>
  <p class="text-red-500 mb-2"><?= $error ?></p>
<?php endif; ?>

    <form method="POST" class="space-y-4">
        <input name="email" type="email" required placeholder="Email" class="w-full p-2 border">
        <input name="password" type="password" required placeholder="Password" class="w-full p-2 border">
        <button class="bg-blue-600 text-white px-4 py-2 rounded">Login</button>
    </form>
</div>
