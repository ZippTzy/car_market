<?php 
include 'db.php'; 
include 'header.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Car not found");

// 1. Increment View Count
$pdo->prepare("UPDATE cars SET views = views + 1 WHERE id = ?")->execute([$id]);

// 2. Fetch Car Details
$stmt = $pdo->prepare("SELECT cars.*, users.name as seller_name FROM cars JOIN users ON cars.seller_id = users.id WHERE cars.id = ?");
$stmt->execute([$id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) die("Car not found");
?>

<div class="max-w-7xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden grid grid-cols-1 md:grid-cols-2">
        <div class="h-96 md:h-auto relative">
            <img src="<?= $car['image_url'] ?>" class="absolute inset-0 w-full h-full object-cover">
        </div>
        <div class="p-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?= $car['year'] ?> <?= $car['make'] ?> <?= $car['model'] ?></h1>
                    <p class="text-gray-500 mt-2"><?= number_format($car['mileage']) ?> miles • <?= $car['type'] ?></p>
                </div>
                <span class="text-2xl font-bold text-blue-600">$<?= number_format($car['price']) ?></span>
            </div>

            <div class="mt-6 border-t pt-6">
                <h3 class="font-bold text-gray-800 mb-2">Description</h3>
                <p class="text-gray-600"><?= $car['description'] ?></p>
            </div>

            <div class="mt-6 border-t pt-6 bg-gray-50 p-4 rounded-lg">
    <h3 class="font-bold text-gray-800 mb-2">Contact Seller</h3>

    <?php if (isset($_GET['sent'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-3">
            ✅ Message sent to seller successfully.
        </div>
    <?php endif; ?>


                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'buyer'): ?>
                    <form action="send_message.php" method="POST">
                        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                        <input type="hidden" name="receiver_id" value="<?= $car['seller_id'] ?>">
                        <textarea name="message" class="w-full border p-2 rounded mb-2" placeholder="Hi, is this still available?" required></textarea>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded w-full hover:bg-blue-700">Send Inquiry</button>
                    </form>
                <?php else: ?>
                    <p class="text-sm text-red-500">Please <a href="login.php?role=buyer" class="underline font-bold">login as a buyer</a> to contact the seller.</p>
                <?php endif; ?>
            </div>
            
            <p class="text-xs text-gray-400 mt-4 text-center">Listed by <?= $car['seller_name'] ?> • Views: <?= $car['views'] ?></p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>