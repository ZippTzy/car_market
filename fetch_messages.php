<?php
session_start();
include 'db.php';

$seller_id = (int)($_SESSION['user_id'] ?? 0);
$buyer_id  = (int)($_GET['buyer_id'] ?? 0);
$car_id    = (int)($_GET['car_id'] ?? 0);


if (!$seller_id || !$buyer_id || !$car_id) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, sender_id, receiver_id, message
    FROM messages
    WHERE car_id = ?
      AND (
           (sender_id = ? AND receiver_id = ?)
        OR (sender_id = ? AND receiver_id = ?)
      )
    ORDER BY created_at ASC
");

$stmt->execute([
    $car_id,
    $seller_id, $buyer_id,
    $buyer_id, $seller_id
]);

$messages = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $messages[] = [
        'id'      => $row['id'],
        'sender'  => $row['sender_id'] == $seller_id ? 'seller' : 'buyer',
        'message' => $row['message']
    ];
}

header('Content-Type: application/json');
echo json_encode($messages);
