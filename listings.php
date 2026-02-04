<?php include 'db.php'; include 'header.php'; ?>
<?php
$typeFilter = $_GET['type'] ?? 'all';
?>


<div class="max-w-7xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Browse Inventory</h1>
    
   <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

    <!-- LEFT SIDEBAR (FILTERS) -->
    <div class="md:col-span-1 bg-white p-4 rounded-xl shadow-sm border
            max-h-[80vh] overflow-y-auto sticky top-24">

        <h2 class="font-bold text-lg mb-4">Filters</h2>

        <div class="space-y-2">
            <a href="listings.php?type=all"
               class="block px-3 py-2 rounded
               <?= $typeFilter === 'all' ? 'bg-blue-100 text-blue-700 font-bold' : 'hover:bg-gray-100' ?>">
                All
            </a>

            <a href="listings.php?type=Electric"
               class="block px-3 py-2 rounded
               <?= $typeFilter === 'Electric' ? 'bg-blue-100 text-blue-700 font-bold' : 'hover:bg-gray-100' ?>">
                Electric
            </a>

            <a href="listings.php?type=Coupe"
               class="block px-3 py-2 rounded
               <?= $typeFilter === 'Coupe' ? 'bg-blue-100 text-blue-700 font-bold' : 'hover:bg-gray-100' ?>">
                Coupe
            </a>
            <a href="listings.php?type=Suv"
               class="block px-3 py-2 rounded
               <?= $typeFilter === 'Suv' ? 'bg-blue-100 text-blue-700 font-bold' : 'hover:bg-gray-100' ?>">
                Suv
            </a>
            <a href="listings.php?type=Truck"
               class="block px-3 py-2 rounded
               <?= $typeFilter === 'Truck' ? 'bg-blue-100 text-blue-700 font-bold' : 'hover:bg-gray-100' ?>">
                Truck
            </a>
            <a href="listings.php?type=Sedan"
               class="block px-3 py-2 rounded
               <?= $typeFilter === 'Sedan' ? 'bg-blue-100 text-blue-700 font-bold' : 'hover:bg-gray-100' ?>">
                Sedan
            </a>
            <a href="listings.php?type=Pickup"
               class="block px-3 py-2 rounded
               <?= $typeFilter === 'Pickup' ? 'bg-blue-100 text-blue-700 font-bold' : 'hover:bg-gray-100' ?>">
                Pickup
            </a>
        </div>
    </div>

    <!-- RIGHT SIDE (CAR LISTINGS) -->
    <div class="md:col-span-3">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
             

       <?php
$sql = "SELECT * FROM cars WHERE status = 'Available'";
$params = [];

if ($typeFilter !== 'all') {
    $sql .= " AND type = ?";
    $params[] = $typeFilter;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

while ($car = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $isSaved = false;

    if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer') {
        $check = $pdo->prepare(
            "SELECT 1 FROM favorites WHERE user_id = ? AND car_id = ?"
        );
        $check->execute([$_SESSION['user_id'], $car['id']]);
        $isSaved = $check->rowCount() > 0;
    }
?>



           <div class="bg-white rounded-xl shadow-sm border overflow-hidden group hover:shadow-lg transition">
    <div class="relative h-48 overflow-hidden">
        <img src="<?= $car['image_url'] ?>"
             class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
    </div>

    <div class="p-4">
        <h3 class="text-lg font-bold">
            <?= $car['year'] ?> <?= $car['make'] ?> <?= $car['model'] ?>
        </h3>

        <p class="text-sm text-gray-500 mb-2">
            <?= $car['type'] ?> • <?= number_format($car['mileage']) ?> mi
        </p>

        <div class="mt-4 flex justify-between items-center gap-2">
            <span class="text-xl font-bold text-blue-600">
                ₱<?= number_format($car['price']) ?>
            </span>

            <div class="flex gap-2">
                <a href="car_details.php?id=<?= $car['id'] ?>"
                   class="bg-gray-900 text-white px-3 py-2 rounded text-sm hover:bg-gray-800">
                    View
                </a>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'buyer'): ?>
                    <form action="toggle_favorite.php" method="POST">
                        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                        <button type="submit"
                            class="px-3 py-2 rounded text-sm
                            <?= $isSaved
                                ? 'bg-red-500 text-white'
                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                            <?= $isSaved ? '❤️ Saved' : '🤍 Save' ?>
                        </button>

                        
                    </form>

                    
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

            <?php
        }
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>