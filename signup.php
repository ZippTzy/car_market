<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $role     = $_POST['role'];
    $password = $_POST['password'];

    // 1️⃣ Check if email already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $error = "Email already registered.";
    } else {
        // 2️⃣ Hash password
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // 3️⃣ Insert user
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$name, $email, $hashed, $role]);

        // 4️⃣ Redirect to login with success flag
        header("Location: login.php?registered=1");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>AutoMarket | Sign Up</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-gray-900 flex items-center justify-center relative overflow-hidden">

<!-- Dark overlay -->
<div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

<!-- SIGNUP CARD -->
<div class="relative z-10 w-full max-w-md mx-4">
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl p-8 text-white">

        <!-- Title -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-extrabold">Create Account</h1>
            <p class="text-gray-300 text-sm mt-1">
                Join AutoMarket 🚗
            </p>
        </div>

        <!-- Error -->
        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-400 text-red-200 px-4 py-2 rounded mb-4 text-sm">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <form method="POST" class="space-y-4">

            <div>
                <label class="text-sm text-gray-300">Full Name</label>
                <input type="text" name="name" required
                    class="w-full mt-1 px-4 py-3 rounded-lg bg-white/20 border border-white/30 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="text-sm text-gray-300">Email</label>
                <input type="email" name="email" required
                    class="w-full mt-1 px-4 py-3 rounded-lg bg-white/20 border border-white/30 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="text-sm text-gray-300">Account Type</label>
                <select name="role" required
  class="w-full mt-1 px-4 py-3 rounded-lg
         bg-white/20 border border-white/30 text-white
         focus:outline-none focus:ring-2 focus:ring-blue-400
         backdrop-blur-xl">

  <option value="" hidden class="bg-transparent text-gray-300">
    Choose Role
  </option>

  <option value="buyer" class="bg-gray-900 text-white">
    Buyer
  </option>

  <option value="seller" class="bg-gray-900 text-white">
    Seller
  </option>
</select>
<div>

           <div class="mb-4">
    <label class="text-sm text-gray-300">Password</label>
    <input type="password" name="password" required
        class="w-full mt-1 px-4 py-3 rounded-lg bg-white/20 border border-white/30
               focus:outline-none focus:ring-2 focus:ring-blue-400">
</div>


            <button
                class="w-full bg-blue-600 hover:bg-blue-700 transition py-3 rounded-lg font-bold shadow-lg">
                Create Account
            </button>
        </form>

        <!-- Footer -->
        <div class="text-center mt-6 text-sm text-gray-300">
            Already have an account?
            <a href="login.php" class="text-blue-400 hover:underline">Login</a>
        </div>

    </div>
</div>

</body>
</html>
