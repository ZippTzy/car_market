<?php
include 'db.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    header("Location: login.php?role=seller");
    exit;
}

$seller_id = $_SESSION['user_id'] ?? 2;

if (isset($_GET['id'], $_GET['action'])) {
    $res_id = (int)$_GET['id'];
    $action = $_GET['action'];

    // First, check if this reservation belongs to this seller
    $stmt = $pdo->prepare("
        SELECT r.id 
        FROM reservations r
        JOIN cars c ON r.car_id = c.id
        WHERE r.id=? AND c.seller_id=?
    ");
    $stmt->execute([$res_id, $seller_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        die("Reservation not found or not yours."); // shows error if something is wrong
    }

    if ($action === 'cancel') {
        $stmt = $pdo->prepare("UPDATE reservations SET status='Cancelled' WHERE id=?");
        $stmt->execute([$res_id]);
        header("Location: seller_dashboard.php?msg=cancelled");
        exit;
    }

  if ($action === 'approve') {
    $amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0;

    // Update reservation status AND save the entered amount
    $stmt = $pdo->prepare("UPDATE reservations SET status='Approved', amount=? WHERE id=?");
    $stmt->execute([$amount, $res_id]);

    // ✅ Calculate 2% platform revenue instead of full amount
    $stmtCar = $pdo->prepare("SELECT car_id FROM reservations WHERE id=?");
    $stmtCar->execute([$res_id]);
    $car_id = $stmtCar->fetchColumn();

    $stmtPrice = $pdo->prepare("SELECT price FROM cars WHERE id=?");
    $stmtPrice->execute([$car_id]);
    $price = $stmtPrice->fetchColumn();

    $platformRevenue = $price * 0.02; // 2%

    // Insert commission transaction
    $stmt = $pdo->prepare("
        INSERT INTO transactions (user_id, car_id, type, amount, status)
        SELECT r.user_id, r.car_id, 'Commission Fee', ?, 'Paid'
        FROM reservations r
        WHERE r.id=?
    ");
    $stmt->execute([$platformRevenue, $res_id]);

    header("Location: seller_dashboard.php?msg=approved");
    exit;
}


}
?>
