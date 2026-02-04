<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/mailer.php';

echo "Mailer file loaded<br>";

if (!function_exists('sendMail')) {
    die("sendMail() not loaded");
}

echo "sendMail() exists<br>";

$result = sendMail(
    'zabtugap2@gmail.com', // put your real email here
    'Test Mail',
    'If you received this, PHPMailer works.'
);

if ($result) {
    echo "Email sent successfully!";
} else {
    echo "Email failed to send.";
}
