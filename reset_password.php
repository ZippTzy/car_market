<?php
session_start();
include 'db.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (!$token) {
    die("Invalid reset request.");
}

$stmt = $pdo->prepare(
    "SELECT id FROM users WHERE reset_token=? AND reset_expires > NOW() LIMIT 1"
);
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Invalid or expired reset link.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (strlen($_POST['password']) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Passwords do not match.";
    } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $update = $pdo->prepare(
            "UPDATE users 
             SET password=?, reset_token=NULL, reset_expires=NULL 
             WHERE id=? AND reset_token=?"
        );
        $update->execute([$password, $user['id'], $token]);

        if ($update->rowCount()) {
            $success = "Password successfully reset. You may login.";
        } else {
            $error = "Reset failed. Please request a new link.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-gray-900
flex items-center justify-center">

<div class="bg-white/10 backdrop-blur-xl border border-white/20
rounded-2xl shadow-2xl p-8 w-full max-w-md text-white">

<h2 class="text-3xl font-bold text-center mb-6">Reset Password</h2>

<?php if($success): ?>
<div class="bg-green-500/20 border border-green-400 p-3 rounded mb-4">
<?= $success ?>
</div>
<a href="login.php" class="block text-center text-blue-400 underline">Login</a>
<?php else: ?>

<form method="POST" class="space-y-4">
<input type="password" name="password" placeholder="New Password" required
class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30">

<input type="password" name="confirm_password" placeholder="Confirm Password" required
class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30">

<button class="w-full bg-blue-600 hover:bg-blue-700 py-3 rounded-lg font-bold">
Update Password
</button>
</form>


<?php endif; ?>
</div>

</body>
</html>
