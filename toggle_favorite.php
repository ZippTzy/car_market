<?php
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$carId  = $_POST['car_id'] ?? 0;

if (!$carId) {
    header("Location: listings.php");
    exit;
}

// Check if already saved
$check = $pdo->prepare(
    "SELECT id FROM favorites WHERE user_id = ? AND car_id = ?"
);
$check->execute([$userId, $carId]);

if ($check->rowCount() > 0) {
    // ❌ REMOVE favorite
    $delete = $pdo->prepare(
        "DELETE FROM favorites WHERE user_id = ? AND car_id = ?"
    );
    $delete->execute([$userId, $carId]);
} else {
    // ❤️ ADD favorite
    $insert = $pdo->prepare(
        "INSERT INTO favorites (user_id, car_id) VALUES (?, ?)"
    );
    $insert->execute([$userId, $carId]);
}

// Go back and FORCE refresh
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
