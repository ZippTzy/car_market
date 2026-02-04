<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'], $_POST['message_id'])) {
    exit('invalid');
}

$user_id = (int)$_SESSION['user_id'];
$message_id = (int)$_POST['message_id'];

$stmt = $pdo->prepare("
    DELETE FROM messages
    WHERE id = ? AND sender_id = ?
");
$stmt->execute([$message_id, $user_id]);

echo $stmt->rowCount() ? 'deleted' : 'failed';
