
<?php 
include 'db.php'; 

// 🔹 AJAX Inventory Actions (NO PAGE RELOAD)
if (isset($_POST['ajax_action'])) {
    $id = (int)$_POST['id'];

    switch ($_POST['ajax_action']) {
     case 'mark_sold':

    // 1️⃣ Get car info
    $stmt = $pdo->prepare("SELECT price FROM cars WHERE id=?");
    $stmt->execute([$id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$car) break;

    // 2️⃣ Commission logic
    $commissionRate = 0.02; // 2%
    $platformRevenue = $car['price'] * $commissionRate;

    // 3️⃣ Buyer (temporary)
    $buyerId = $_SESSION['user_id'] ?? 1;

    // 4️⃣ Mark car as sold
    $pdo->prepare("UPDATE cars SET status='Sold' WHERE id=?")->execute([$id]);

    // 5️⃣ Insert PLATFORM REVENUE (NOT CAR PRICE)
    $pdo->prepare("
        INSERT INTO transactions (user_id, car_id, type, amount, status)
        VALUES (?, ?, 'Commission Fee', ?, 'Paid')
    ")->execute([$buyerId, $id, $platformRevenue]);

    break;


        case 'remove':
            $pdo->prepare("UPDATE cars SET status='Removed' WHERE id=?")->execute([$id]);
            break;

       case 'restore':

    // 1️⃣ Mark car available
    $pdo->prepare("UPDATE cars SET status='Available' WHERE id=?")
        ->execute([$id]);

     // 2️⃣ Mark related transaction as FAILED (or Reversed)
    $pdo->prepare("
        UPDATE transactions 
        SET status='Failed'
        WHERE car_id=? AND status='Paid'
        ORDER BY created_at DESC
        LIMIT 1
    ")->execute([$id]);

    break;

    }

    echo json_encode(['success' => true]);
    exit;
}

include 'header.php';



if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<script>window.location.href='login.php?role=admin';</script>";
    exit;
}

// 1. Fetch Real Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$active_listings = $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'Available'")->fetchColumn();
$pending_approvals = $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'Pending'")->fetchColumn();
$revenue = $pdo->query("
    SELECT COALESCE(SUM(amount), 0) 
    FROM transactions 
    WHERE status = 'Paid'
")->fetchColumn();

  
// Inventory Data (ACTIVE)
$inventory = $pdo->query("
    SELECT *, STATUS AS status
    FROM cars
    WHERE STATUS != 'Removed'
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$removedCars = $pdo->query("
    SELECT *, STATUS AS status
    FROM cars
    WHERE STATUS = 'Removed'
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);



// 2. Handle Approve/Reject
if (isset($_GET['approve'])) {
    $car_id = $_GET['approve'];
    $car = $pdo->query("SELECT * FROM cars WHERE id=$car_id")->fetch(PDO::FETCH_ASSOC);

    if ($car['action_request'] === 'mark_sold') {
        $pdo->prepare("UPDATE cars SET status='Sold', action_request=NULL WHERE id=?")->execute([$car_id]);
    } elseif ($car['action_request'] === 'remove') {
        $pdo->prepare("UPDATE cars SET status='Removed', action_request=NULL WHERE id=?")->execute([$car_id]);
    } else {
        $pdo->prepare("UPDATE cars SET status='Available', action_request=NULL WHERE id=?")->execute([$car_id]);
    }

    echo "<script>window.location.href='admin_dashboard.php';</script>";
    exit;
}

// 3. Inventory Actions
if (isset($_GET['mark_sold'])) {
    $pdo->prepare("UPDATE cars SET status = 'Sold' WHERE id = ?")
        ->execute([$_GET['mark_sold']]);
    echo "<script>window.location.href='admin_dashboard.php';</script>";
}

if (isset($_GET['remove'])) {
    $pdo->prepare("UPDATE cars SET status = 'Removed' WHERE id = ?")
        ->execute([$_GET['remove']]);
    echo "<script>window.location.href='admin_dashboard.php';</script>";
}
// Restore Removed Car
if (isset($_GET['restore'])) {
    $pdo->prepare("UPDATE cars SET status = 'Available' WHERE id = ?")
        ->execute([$_GET['restore']]);
    echo "<script>window.location.href='admin_dashboard.php';</script>";
}


?>

<div class="max-w-7xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Admin Control Panel</h1>

    <!-- Real Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
            <p class="text-gray-500 text-sm">Total Users</p>
            <h2 class="text-3xl font-bold text-blue-600"><?= number_format($total_users) ?></h2>
        </div>
        <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
            <p class="text-gray-500 text-sm">Active Listings</p>
            <h2 class="text-3xl font-bold text-green-600"><?= number_format($active_listings) ?></h2>
        </div>
        <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
            <p class="text-gray-500 text-sm">Revenue (Total)</p>
            <h2 class="text-3xl font-bold text-purple-600">₱<?= number_format($revenue, 2) ?></h2>
        </div>
        <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
            <p class="text-gray-500 text-sm">Pending Approvals</p>
            <h2 class="text-3xl font-bold text-orange-600"><?= number_format($pending_approvals) ?></h2>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        
        <!-- Transaction Cashier Log -->
      <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="font-bold text-gray-700">Transaction Log</h3>
        <span class="text-xs bg-gray-200 px-2 py-1 rounded">Real-time</span>
    </div>

    <!-- ✅ SCROLL CONTAINER -->
    <div class="max-h-[250px] overflow-y-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Type</th>
                    <th class="px-4 py-2 text-left">Amount</th>
                    <th class="px-4 py-2 text-left">Status</th>
                </tr>
            </thead>

                <tbody class="divide-y divide-gray-200">
                    <?php
                    $stmt = $pdo->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 10");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $statusColor = $row['status'] == 'Paid' ? 'text-green-600' : 'text-orange-600';
                        echo "<tr>
                            <td class='px-4 py-3 text-gray-500'>#{$row['id']}</td>
                            <td class='px-4 py-3'>{$row['type']}</td>
                            <td class='px-4 py-3 font-bold'>₱" . number_format($row['amount'], 2) . "</td>
                            <td class='px-4 py-3 {$statusColor} font-bold'>{$row['status']}</td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
      </div>

        <!-- Pending Approvals -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="font-bold text-gray-700">Pending Approvals</h3>
            </div>

             <!-- ✅ SCROLL CONTAINER -->
    <div class="max-h-[250px] overflow-y-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                        <th class="px-4 py-2 text-left">Vehicle</th>
                        <th class="px-4 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php
                    $stmt = $pdo->query("SELECT * FROM cars WHERE status = 'Pending'");
                    $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                  if(count($pending) > 0) {
    foreach ($pending as $car) {
        $requestLabel = match($car['action_request']) {
            'mark_sold' => 'Mark as Sold',
            'remove' => 'Remove',
            default => 'Unknown',
        };
                            echo "<tr>
                                <td class='px-4 py-3'>{$car['year']} {$car['make']} {$car['model']}</td>
                                <td class='px-4 py-3 text-right'>
                                    <a href='?approve={$car['id']}' class='bg-green-100 text-green-700 px-3 py-1 rounded hover:bg-green-200'>Approve</a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2' class='px-4 py-4 text-center text-gray-500'>No pending approvals.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        </div>
        </div>  <!-- END grid grid-cols-1 lg:grid-cols-2 -->



    <!-- Inventory Management -->
    <div class="mt-12 bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
            <h3 class="font-bold text-gray-700">Inventory Management</h3>
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">
                Total: <?= count($inventory) ?>
            </span>
        </div>

         <!-- ✅ SCROLL CONTAINER -->
    <div class="max-h-[250px] overflow-y-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-2 text-left">Vehicle</th>
                    <th class="px-4 py-2 text-left">Price</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-right">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                <?php foreach($inventory as $car): 
                    $statusColor = match($car['status']) {
                        'Available' => 'text-green-600',
                        'Sold' => 'text-blue-600',
                        'Pending' => 'text-orange-600',
                        default => 'text-gray-500'
                    };
                ?>
                <tr>
                    <td class="px-4 py-3"><?= $car['year'] ?> <?= $car['make'] ?> <?= $car['model'] ?></td>
                    <td class="px-4 py-3 font-bold">₱<?= number_format($car['price'], 2) ?></td>
                    <td class="px-4 py-3 font-semibold <?= $statusColor ?>"><?= $car['status'] ?></td>
                    <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">

    <?php if($car['status'] === 'Available'): ?>
        <button onclick="confirmAction('mark_sold', <?= $car['id'] ?>, 'Mark this vehicle as SOLD?')"
            class="bg-blue-100 text-blue-700 px-3 py-1 rounded hover:bg-blue-200">
            Mark Sold
        </button>

    <?php elseif($car['status'] === 'Sold'): ?>
        <button onclick="confirmAction('restore', <?= $car['id'] ?>, 'Mark this vehicle as AVAILABLE?')"
            class="bg-green-100 text-green-700 px-3 py-1 rounded hover:bg-green-200">
            Mark Available
        </button>
    <?php endif; ?>

    <button onclick="confirmAction('remove', <?= $car['id'] ?>, 'Remove this vehicle from inventory?')"
        class="bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200">
        Remove
    </button>

</td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


<!-- Removed Inventory (Recovery) -->
<div class="mt-12 bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="font-bold text-gray-700">Removed Inventory (Recovery)</h3>
        <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded">
            <?= count($removedCars) ?> Removed
        </span>
    </div>

     <!-- ✅ SCROLL CONTAINER -->
    <div class="max-h-[250px] overflow-y-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-2 text-left">Vehicle</th>
                    <th class="px-4 py-2 text-left">Price</th>
                    <th class="px-4 py-2 text-right">Action</th>
                </tr>
            </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if(count($removedCars) > 0): ?>
                <?php foreach($removedCars as $car): ?>
                <tr>
                    <td class="px-4 py-3">
                        <?= $car['year'] ?> <?= $car['make'] ?> <?= $car['model'] ?>
                    </td>
                    <td class="px-4 py-3 font-bold">
                        ₱<?= number_format($car['price'], 2) ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button onclick="confirmAction('restore', <?= $car['id'] ?>, 'Restore this vehicle?')"
                         class="bg-green-100 text-green-700 px-3 py-1 rounded hover:bg-green-200">
                             Restore
                               </button>

                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="px-4 py-4 text-center text-gray-500">
                        No removed vehicles.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-sm">
        <h3 class="text-lg font-bold text-gray-800 mb-4" id="confirmTitle">Confirm Action</h3>
        <p class="text-gray-600 mb-6" id="confirmMessage">Are you sure?</p>

        <div class="flex justify-end space-x-3">
            <button onclick="closeModal()" class="px-4 py-2 rounded bg-gray-200">Cancel</button>
            <button id="confirmBtn" class="px-4 py-2 rounded bg-red-600 text-white">Confirm</button>


        </div>
    </div>
</div>
<script>
let pendingAction = null;
let pendingId = null;

function confirmAction(action, id, message) {
    pendingAction = action;
    pendingId = id;
    document.getElementById('confirmMessage').innerText = message;
    document.getElementById('confirmModal').classList.remove('hidden');
    document.getElementById('confirmModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('confirmModal').classList.add('hidden');
}

document.getElementById('confirmBtn').onclick = function () {
    fetch('admin_dashboard.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ajax_action=${pendingAction}&id=${pendingId}`
    })
    .then(res => res.json())
    .then(() => location.reload());
};
</script>


<?php include 'footer.php'; ?>
