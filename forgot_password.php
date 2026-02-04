<?php
include 'db.php';
require 'mailer.php'; // 👈 ADD THIS

$message = '';
$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        $update = $pdo->prepare(
            "UPDATE users SET reset_token=?, reset_expires=? WHERE email=?"
        );
        $update->execute([$token, $expires, $email]);

        // 🔹 RESET LINK
        $resetLink = "http://localhost/car_market/reset_password.php?token=$token";


        // 🔹 EMAIL CONTENT
        $subject = "Password Reset - AutoMarket";
        $body = "
            <h2>Password Reset Request</h2>
            <p>Click the link below to reset your password:</p>
            <p><a href='$resetLink'>Reset Password</a></p>
            <p>This link expires in 15 minutes.</p>
        ";

        // 🔹 SEND EMAIL
        if (sendMail($email, $subject, $body)) {
            $message = "Password reset link has been sent to your email.";
        } else {
            $error = "Failed to send email. Please try again.";
        }
        usleep(500000); // 0.5 second delay

   } else {
    usleep(500000); // 0.5 second delay
    $message = "If the email exists, a reset link has been sent.";
}
            
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password | AutoMarket</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-gray-900
flex items-center justify-center">

<div class="bg-white/10 backdrop-blur-xl border border-white/20
rounded-2xl shadow-2xl p-8 w-full max-w-md text-white">

<h2 class="text-3xl font-bold text-center mb-2">Forgot Password</h2>
<p class="text-gray-300 text-center mb-6">Enter your email to reset</p>

<?php if($error): ?>
<div class="bg-red-500/20 border border-red-400 text-red-200 p-3 rounded mb-4">
<?= $error ?>
</div>
<?php endif; ?>

<?php if($message): ?>
<div class="bg-green-500/20 border border-green-400 text-green-200 p-3 rounded mb-4">
<?= $message ?>
</div>
<?php endif; ?>

<form method="POST" class="space-y-4">
<input type="email" name="email" placeholder="Email" required
class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30
focus:ring-2 focus:ring-blue-400">

<button class="w-full bg-blue-600 hover:bg-blue-700 py-3 rounded-lg font-bold">
Send Reset Link
</button>
</form>

<div class="text-center mt-4 text-sm">
<a href="login.php" class="text-blue-400 hover:underline">Back to login</a>
</div>
</div>

</body>
</html>
