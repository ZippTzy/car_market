<?php include 'db.php'; include 'header.php'; ?>

<div class="relative bg-blue-900 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-blue-900 to-blue-800 opacity-90"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-24 text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-6">Find Your Dream Car</h1>
        <p class="text-xl text-gray-300 max-w-2xl mx-auto mb-8">
            The Ultimate Car Marketplace Where You Can Discover, Compare, and Buy Your Perfect Vehicle with Ease. 🚗 
        </p>
      <div class="flex justify-center gap-4">
<?php
// Default values (guest)
$primaryLink = "listings.php";
$secondaryText = "Sell Your Car";
$secondaryLink = "login.php";

if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] === 'admin') {
        $secondaryText = "Manage Your Dashboard";
        $secondaryLink = "admin_dashboard.php";

    } elseif ($_SESSION['role'] === 'seller') {
        $secondaryLink = "seller_dashboard.php";

    } elseif ($_SESSION['role'] === 'buyer') {
        $secondaryLink = "login.php";
    }
}
?>

<a href="<?= $primaryLink ?>"
   class="bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700">
   Browse Inventory
</a>

<a href="<?= $secondaryLink ?>"
   class="bg-white text-blue-900 px-8 py-3 rounded-lg font-bold hover:bg-gray-100">
   <?= $secondaryText ?>
</a>
</div>


    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-16">
    <h2 class="text-3xl font-bold text-gray-900 mb-8">Recent Additions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php
        $stmt = $pdo->query("SELECT * FROM cars WHERE status = 'Available' ORDER BY id DESC LIMIT 3");

        while ($car = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "
            <div class='bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-md transition'>
                <img src='{$car['image_url']}' class='w-full h-48 object-cover'>
                <div class='p-4'>
                    <h3 class='text-lg font-bold'>{$car['year']} {$car['make']} {$car['model']}</h3>
                    <p class='text-gray-500 text-sm'>{$car['type']} • " . number_format($car['mileage']) . " mi</p>
                    <div class='mt-4 flex justify-between items-center'>
                        <span class='text-xl font-bold text-blue-600'>₱" . number_format($car['price']) . "</span>
                        <a href='listings.php' class='text-blue-600 text-sm font-medium'>View Details</a>
                    </div>
                </div>
            </div>";
        }
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>