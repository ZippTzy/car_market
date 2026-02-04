<?php
$host = 'sql203.infinityfree.com';
$db   = 'if0_41074102_XXX';
$user = 'if0_41074102';
$pass = 'cedricdomingo26'; // Default XAMPP password is empty

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // ✅ FIX: Sync MySQL time with Philippines time
    $pdo->exec("SET time_zone = '+08:00'");

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ✅ PHP timezone (VERY IMPORTANT)
date_default_timezone_set('Asia/Manila');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
