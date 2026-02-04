<?php
include 'db.php';
include 'header.php';

// Security Check: Only sellers can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    echo "<script>window.location.href='login.php?role=seller';</script>";
    exit;
}

$seller_id = $_SESSION['user_id'] ?? 0;

// Get car ID from URL
$car_id = $_GET['id'] ?? 0;
if (!$car_id) {
    echo "<script>window.location.href='seller_dashboard.php';</script>";
    exit;
}

// Fetch the car to edit
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id=? AND seller_id=?");
$stmt->execute([$car_id, $seller_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    echo "<script>window.location.href='seller_dashboard.php';</script>";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $mileage = $_POST['mileage'];
    $type = $_POST['type'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("
    UPDATE cars 
    SET make = ?, model = ?, year = ?, price = ?, mileage = ?, type = ?, description = ?, status = 'Pending'
    WHERE id = ? AND seller_id = ?
");
$stmt->execute([
    $_POST['make'],
    $_POST['model'],
    $_POST['year'],
    $_POST['price'],
    $_POST['mileage'],
    $_POST['type'],
    $_POST['description'],
    $car_id,
    $seller_id
]);

    echo "<script>window.location.href='seller_dashboard.php?success=pending';</script>";
    exit;
}
?>

<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Edit Listing</h1>

    <form method="POST" action="">
        <div class="grid grid-cols-2 gap-4 mb-4">
            <input type="text" name="make" placeholder="Make" class="p-2 border rounded" value="<?= htmlspecialchars($car['make']) ?>" required>
            <input type="text" name="model" placeholder="Model" class="p-2 border rounded" value="<?= htmlspecialchars($car['model']) ?>" required>
            <input type="number" name="year" placeholder="Year" class="p-2 border rounded" value="<?= htmlspecialchars($car['year']) ?>" required>
            <input type="number" name="price" placeholder="Price" class="p-2 border rounded" value="<?= htmlspecialchars($car['price']) ?>" required>
            <input type="number" name="mileage" placeholder="Mileage" class="p-2 border rounded" value="<?= htmlspecialchars($car['mileage']) ?>" required>
            <select name="type" class="p-2 border rounded">
                <option <?= $car['type'] == 'Sedan' ? 'selected' : '' ?>>Sedan</option>
                <option <?= $car['type'] == 'SUV' ? 'selected' : '' ?>>SUV</option>
                <option <?= $car['type'] == 'Truck' ? 'selected' : '' ?>>Truck</option>
            </select>
        </div>

        <textarea name="description" class="w-full p-2 border rounded mb-4" placeholder="Description" required><?= htmlspecialchars($car['description']) ?></textarea>

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded w-full">
            Submit for Admin Approval
        </button>
    </form>
</div>

<?php include 'footer.php'; ?>
