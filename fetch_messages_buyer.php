<?php
session_start();
include 'db.php';

$buyer_id  = (int)($_SESSION['user_id'] ?? 0);
$seller_id = (int)($_GET['seller_id'] ?? 0);

if (!$buyer_id || !$seller_id) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, sender_id, message
    FROM messages
    WHERE 
        (sender_id = ? AND receiver_id = ?)
        OR
        (sender_id = ? AND receiver_id = ?)
    ORDER BY created_at ASC
");

$stmt->execute([
    $buyer_id, $seller_id,
    $seller_id, $buyer_id
]);

$messages = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $messages[] = [
        'id'     => $row['id'],
        'sender' => $row['sender_id'] == $buyer_id ? 'buyer' : 'seller',
        'message'=> $row['message']
    ];
}

echo json_encode($messages);
