<?php
$host = 'localhost';
$db   = 'cabshare';
$user = 'root';
$pass = ''; // use your db password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
