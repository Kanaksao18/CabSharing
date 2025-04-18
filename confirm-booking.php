<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user'], $_POST['ride_id'], $_POST['seats'], $_POST['total_price'])) {
    header('Location: find-ride.php?error=Missing required inputs');
    exit();
}

$ride_id = (int)$_POST['ride_id'];
$seats = (int)$_POST['seats'];
$total_price = (float)$_POST['total_price'];
$user_id = $_SESSION['user']['id'];

try {
    // Validate ride
    $stmt = $pdo->prepare("SELECT available_seats, status FROM rides WHERE id = ?");
    $stmt->execute([$ride_id]);
    $ride = $stmt->fetch();

    if (!$ride) {
        throw new Exception("Ride not found.");
    }

    if ($ride['status'] !== 'active') {
        throw new Exception("Ride is not active.");
    }

    if ($ride['available_seats'] < $seats) {
        throw new Exception("Not enough seats available.");
    }

    // Transaction begins
    $pdo->beginTransaction();

    // Insert booking with status 'pending'
    $stmt = $pdo->prepare("INSERT INTO bookings (ride_id, passenger_id, seats_booked, total_price, status) 
                           VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$ride_id, $user_id, $seats, $total_price]);

    // Update available seats in the ride
    $stmt = $pdo->prepare("UPDATE rides SET available_seats = available_seats - ? WHERE id = ?");
    $stmt->execute([$seats, $ride_id]);

    // If ride full now, mark it completed (optional)
    $remainingSeats = $ride['available_seats'] - $seats;
    if ($remainingSeats <= 0) {
        $stmt = $pdo->prepare("UPDATE rides SET status = 'completed' WHERE id = ?");
        $stmt->execute([$ride_id]);
    }

    $pdo->commit();

    // Redirect to payment.php with necessary parameters
    header("Location: payment.php?ride_id=$ride_id&seats=$seats&total_price=$total_price");
    exit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Redirect with error message
    header("Location: find-ride.php?error=" . urlencode($e->getMessage()));
    exit();
}