<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

if (!isset($_POST['car_id'])) {
    die("Car ID missing");
}

$user_id = (int) $_SESSION['user_id'];
$car_id  = (int) $_POST['car_id'];

/* ✅ CHECK IF CAR EXISTS */
$check = $pdo->prepare("SELECT id FROM cars WHERE id = ?");
$check->execute([$car_id]);

if ($check->rowCount() === 0) {
    die("Car does not exist");
}

/* ✅ INSERT RESERVATION */
$stmt = $pdo->prepare("
    INSERT INTO reservations (user_id, car_id, status)
    VALUES (?, ?, 'Pending')
");
$stmt->execute([$user_id, $car_id]);

/* ✅ UPDATE CAR STATUS */
$pdo->prepare("
    UPDATE cars SET status = 'Pending' WHERE id = ?
")->execute([$car_id]);

header("Location: buyer_dashboard.php?reserved=success");
exit;
