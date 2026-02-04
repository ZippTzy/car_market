<?php


session_start();
include 'db.php';


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role']      = $user['role'];

        if ($user['role'] === 'buyer') header("Location: buyer_dashboard.php");
        elseif ($user['role'] === 'seller') header("Location: seller_dashboard.php");
        else header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>AutoMarket Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Tailwind CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


<style>
/* ===== CAR DRIVE ===== */
@keyframes drive {
    0% {
        transform: translateX(-200px) translateY(0);
    }
    10% {
        transform: translateX(5vw) translateY(-1px);
    }
    50% {
        transform: translateX(50vw) translateY(1px);
    }
    90% {
        transform: translateX(95vw) translateY(-1px);
    }
    100% {
        transform: translateX(120vw) translateY(0);
    }
}

@keyframes suspension {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(2px); }
}

.car-wrapper {
    position: absolute;
    bottom: 50px;
    left: 0;
    animation: drive 10s linear infinite;
}

.car {
    width: 600px;
    max-width: 90vw;
    animation: suspension 0.6s ease-in-out infinite;
}

/* ROAD */
.road {
    position: absolute;
    bottom: 40px;
    left: 0;
    width: 200%;
    height: 4px;
    background: repeating-linear-gradient(
        to right,
        #fff 0 40px,
        transparent 40px 80px
    );
    opacity: 0.5;
    animation: roadMove 1.5s linear infinite;
}

@keyframes roadMove {
    from { transform: translateX(0); }
    to { transform: translateX(-80px); }
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.wheel {
    animation: spin 0.5s linear infinite;
}


</style>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-gray-900 flex items-center justify-center relative overflow-hidden">

<!-- ROAD -->
<div class="road"></div>

<!-- CAR -->
<div class="car-wrapper pointer-events-none">
    <img src="uploads/CAR_DESIGN/car-removebg-preview.png" class="car">
</div>


<!-- Blur Overlay -->
<div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

<!-- LOGIN CARD -->
<div class="relative z-10 w-full max-w-md mx-4 fade-in">
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl p-8 text-white">

        <!-- Logo -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-extrabold">AutoMarket</h1>
            <p class="text-gray-300 text-sm mt-1">
                Buy & Sell Cars Securely 🚗
            </p>
        </div>
        <?php if (isset($_GET['registered'])): ?>
<div class="bg-green-500/20 border border-green-400 text-green-200 px-4 py-2 rounded mb-4 text-sm">
    🎉 Account created successfully! You can now log in.
</div>
<?php endif; ?>


        <!-- Error -->
        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-400 text-red-200 px-4 py-2 rounded mb-4 text-sm">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <form method="POST" class="space-y-4">
            <div>
                <label class="text-sm text-gray-300">Email</label>
                <input type="email" name="email" required
                       class="w-full mt-1 px-4 py-3 rounded-lg bg-white/20 border border-white/30 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div class="relative">
    <label class="text-sm text-gray-300">Password</label>

    <input
        type="password"
        name="password"
        id="password"
        required
        class="w-full mt-1 px-4 py-3 pr-12 rounded-lg bg-white/20 border border-white/30
               focus:outline-none focus:ring-2 focus:ring-blue-400"
    >

    <!-- Eye Icon -->
    <button type="button"
        onclick="togglePassword()"
        class="absolute right-4 top-10 text-gray-300 hover:text-white">
        <i id="eyeIcon" class="fa-solid fa-eye-slash"></i>

    </button>
</div>

<div class="text-right">
    <a href="forgot_password.php"
       class="text-sm text-blue-400 hover:text-blue-300 hover:underline transition">
        Forgot password?
    </a>
</div>

            <button
                class="w-full bg-blue-600 hover:bg-blue-700 transition py-3 rounded-lg font-bold shadow-lg">
                Login
            </button>
        </form>

        <!-- Footer -->
        <div class="text-center mt-6 text-sm text-gray-300">
            Don’t have an account?
            <a href="signup.php" class="text-blue-400 hover:underline">Sign up</a>
        </div>
    </div>
</div>


<script>
function togglePassword() {
    const passwordInput = document.getElementById("password");
    const eyeIcon = document.getElementById("eyeIcon");

    if (passwordInput.type === "password") {
        // SHOW password
        passwordInput.type = "text";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
    } else {
        // HIDE password
        passwordInput.type = "password";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
    }
}
</script>


</body>
</html>


