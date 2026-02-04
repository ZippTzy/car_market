<?php
include 'db.php';

$seller_id = $_SESSION['user_id'] ?? 0;
$car_id = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? '';

if (!$seller_id || !$car_id || !$action) {
    header("Location: seller_dashboard.php");
    exit;
}

switch ($action) {
    case 'remove':
        $pdo->prepare("UPDATE cars SET status='Pending', action_request='remove' WHERE id=? AND seller_id=?")
            ->execute([$car_id, $seller_id]);
        break;

    case 'mark_sold':
        $pdo->prepare("UPDATE cars SET status='Pending', action_request='mark_sold' WHERE id=? AND seller_id=?")
            ->execute([$car_id, $seller_id]);
        break;

    case 'edit':
        header("Location: edit_car.php?id=$car_id");
        exit;
}

header("Location: seller_dashboard.php?success=pending");
exit;
?>
