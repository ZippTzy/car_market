<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoMarket - PHP Version</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 text-gray-900 font-sans flex flex-col min-h-screen">

<nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- LOGO -->
            <div class="flex items-center">
                <a href="index.php" class="text-xl font-bold text-gray-900">
                    Auto<span class="text-blue-600">Market</span>
                </a>
            </div>

            <!-- NAV LINKS -->
            <div class="flex items-center space-x-6">
                <a href="index.php" class="text-gray-500 hover:text-blue-600 font-medium">
                    Home
                </a>

                <a href="listings.php" class="text-gray-500 hover:text-blue-600 font-medium">
                    Listings
                </a>

                <a href="how_it_works.php" class="text-gray-500 hover:text-blue-600 font-medium">
                    How It Works
                </a>

                <a href="about.php" class="text-gray-500 hover:text-blue-600 font-medium">
                    About
                </a>

                <!-- AUTH / ROLE LINKS -->
                <?php if (isset($_SESSION['role'])): ?>

                    <?php if ($_SESSION['role'] === 'seller'): ?>
                        <a href="seller_dashboard.php" class="text-blue-600 font-medium">
                            <i class="fas fa-gauge"></i> Dashboard
                        </a>

                    <?php elseif ($_SESSION['role'] === 'buyer'): ?>
                        <a href="buyer_dashboard.php" class="text-blue-600 font-medium">
                            <i class="fas fa-gauge"></i> Dashboard
                        </a>

                    <?php elseif ($_SESSION['role'] === 'admin'): ?>
                        <a href="admin_dashboard.php" class="text-blue-600 font-medium">
                            <i class="fas fa-gauge"></i> Dashboard
                        </a>

                    <?php endif; ?>

                    <a href="logout.php" class="text-red-500 font-medium">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>

                <?php else: ?>

                    <!-- LOGIN -->
                    <a href="login.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700">
                 <i class="fas fa-sign-in-alt"></i> Login
                   </a>


                <?php endif; ?>

            </div>
        </div>
    </div>
</nav>

<main class="flex-grow">
