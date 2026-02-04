<?php
include 'db.php';

echo "PHP Time: " . date("Y-m-d H:i:s") . "<br>";

$stmt = $pdo->query("SELECT NOW() as db_time");
$row = $stmt->fetch();

echo "MySQL Time: " . $row['db_time'];
