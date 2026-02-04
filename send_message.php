<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) exit;

$message  = trim($_POST['message'] ?? '');
$receiver = (int)($_POST['receiver_id'] ?? 0);
$car_id   = (int)($_POST['car_id'] ?? 0);
$sender   = $_SESSION['user_id'];

if ($message !== '') {
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, receiver_id, car_id, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$sender, $receiver, $car_id, $message]);

    header("Location: car_details.php?id=$car_id&sent=1");
    exit;
}
