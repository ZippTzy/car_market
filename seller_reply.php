<?php
include 'db.php';
session_start();
$seller_id = $_SESSION['user_id'];

if(isset($_POST['receiver_id'], $_POST['car_id'], $_POST['reply_message'])){
    $receiver_id = (int)$_POST['receiver_id'];
    $car_id = (int)$_POST['car_id'];
    $msg = trim($_POST['reply_message']);
    if($msg != ''){
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, car_id, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$seller_id, $receiver_id, $car_id, $msg]);
    }
}
?>
<script>
    // After sending the reply, refresh the messages
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const buyer_id = urlParams.get('buyer_id');
        const car_id = urlParams.get('car_id');
        if(buyer_id && car_id) {
            fetchMessages(buyer_id, car_id);
        }
    };