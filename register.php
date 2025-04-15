<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $gender   = $_POST['gender'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, gender, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $phone, $gender, $password);

    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
<form action="register.php" method="POST">
  <input name="name" placeholder="Full Name" required>
  <input name="email" type="email" placeholder="Email" required>
  <input name="phone" placeholder="Phone" required>
  <select name="gender">
    <option>Male</option>
    <option>Female</option>
    <option>Other</option>
  </select>
  <input name="password" type="password" placeholder="Password" required>
  <button type="submit">Register</button>
</form>
