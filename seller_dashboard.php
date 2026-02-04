<?php 
include 'db.php'; 
include 'header.php';

// Security Check: Only sellers can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    echo "<script>window.location.href='login.php?role=seller';</script>";
    exit;
}

$seller_id = $_SESSION['user_id'] ?? 2; 




// ===== Handle Seller Replies =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'], $_POST['receiver_id'], $_POST['car_id'])) {
    $reply_message = trim($_POST['reply_message']);
    $receiver_id = (int)$_POST['receiver_id'];
    $car_id = (int)$_POST['car_id'];

    if ($reply_message !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, car_id, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$seller_id, $receiver_id, $car_id, $reply_message]);

        exit('success'); // ✅ clean AJAX response
    }
}



// --- 1. Fetch Stats ---
$active_count = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE seller_id = ? AND status = 'Available'");
$active_count->execute([$seller_id]);
$active_count = $active_count->fetchColumn();

$total_views = $pdo->prepare("SELECT SUM(views) FROM cars WHERE seller_id = ?");
$total_views->execute([$seller_id]);
$total_views = $total_views->fetchColumn() ?: 0;

$inquiry_count = $pdo->prepare("SELECT COUNT(DISTINCT m.sender_id) 
                                FROM messages m 
                                JOIN cars c ON m.car_id = c.id 
                                WHERE c.seller_id = ?");
$inquiry_count->execute([$seller_id]);
$inquiry_count = $inquiry_count->fetchColumn();

// --- 2. Handle Add Car ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_car') {

    $imageUrl = 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&q=80&w=800'; // fallback

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'uploads/' . uniqid() . '.' . $ext;
        if(!file_exists('uploads')) mkdir('uploads', 0777, true); // create folder if not exists
        move_uploaded_file($_FILES['image']['tmp_name'], $filename);
        $imageUrl = $filename;
    }

    $stmt = $pdo->prepare("INSERT INTO cars (seller_id, make, model, year, price, mileage, type, description, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([
        $seller_id, $_POST['make'], $_POST['model'], $_POST['year'], $_POST['price'],
        $_POST['mileage'], $_POST['type'], $_POST['description'], $imageUrl
    ]);

    echo "<script>window.location.href='seller_dashboard.php?success=added';</script>";
}


// --- 3. Fetch Seller Cars ---
$my_cars_stmt = $pdo->prepare("SELECT * FROM cars WHERE seller_id = ? ORDER BY created_at DESC");
$my_cars_stmt->execute([$seller_id]);
$my_cars = $my_cars_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 4. Fetch Reservations for Seller Cars ---
$reservations_stmt = $pdo->prepare("
    SELECT r.*, u.name as buyer_name, c.make, c.model, c.year
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN cars c ON r.car_id = c.id
    WHERE c.seller_id = ?
    ORDER BY r.created_at DESC
");
$reservations_stmt->execute([$seller_id]);
$reservations = $reservations_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 5. Fetch Messages ---
$msg_stmt = $pdo->prepare("
    SELECT 
        m.*,
        u.name AS buyer_name,
        c.make,
        c.model
    FROM messages m
    JOIN cars c ON c.id = m.car_id
    JOIN users u ON u.id = m.sender_id
    WHERE c.seller_id = ?
      AND m.sender_id != ?
      AND m.id IN (
          SELECT MAX(id)
          FROM messages
          WHERE sender_id != ?
          GROUP BY sender_id, car_id
      )
    ORDER BY m.created_at DESC
");
$msg_stmt->execute([$seller_id, $seller_id, $seller_id]);
$messages = $msg_stmt->fetchAll(PDO::FETCH_ASSOC);



?>

<div class="max-w-7xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Seller Dashboard</h1>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow text-center border-t-4 border-blue-500">
            <p class="text-gray-500">Active Listings</p>
            <p class="text-3xl font-bold"><?= $active_count ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow text-center border-t-4 border-green-500">
            <p class="text-gray-500">Total Views</p>
            <p class="text-3xl font-bold"><?= number_format($total_views) ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow text-center border-t-4 border-purple-500">
            <p class="text-gray-500">Total Inquiries</p>
            <p class="text-3xl font-bold"><?= $inquiry_count ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Add Car Form -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-blue-100">
            <h2 class="font-bold text-xl mb-4 text-blue-900">Add New Listing</h2>
            <form method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="action" value="add_car">
    <div class="grid grid-cols-2 gap-4 mb-4">
        <input type="text" name="make" placeholder="Make" class="p-2 border rounded" required>
        <input type="text" name="model" placeholder="Model" class="p-2 border rounded" required>
        <input type="number" name="year" placeholder="Year" class="p-2 border rounded" required>
        <input type="number" name="price" placeholder="Price" class="p-2 border rounded" required>
        <input type="number" name="mileage" placeholder="Mileage" class="p-2 border rounded" required>
        <select name="type" class="p-2 border rounded">
            <option>Sedan</option><option>SUV</option><option>Truck</option><option>Pickup</option><option>Coupe</option>
        </select>
    </div>
    <textarea name="description" class="w-full p-2 border rounded mb-4" placeholder="Description" required></textarea>
    <input type="file" name="image" accept="image/*" class="mb-4" required>
    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded w-full">Submit for Approval</button>
</form>

        </div>

     <!-- Messages -->
<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50">
        <h3 class="font-bold text-gray-700">Recent Inquiries</h3>
    </div>
    <div class="overflow-y-auto max-h-96">
        <?php if(count($messages) > 0): ?>
            <?php foreach($messages as $msg): ?>
                <div class="p-4 border-b hover:bg-gray-50 cursor-pointer"
                     onclick="openChat(<?= $msg['sender_id'] ?>, <?= $msg['car_id'] ?>, '<?= addslashes($msg['buyer_name']) ?>', '<?= addslashes($msg['make'].' '.$msg['model']) ?>')">


                    <div class="flex justify-between items-start">
                        <span class="font-bold text-sm"><?= $msg['buyer_name'] ?></span>
                        <span class="text-xs text-gray-400"><?= date('M d H:i', strtotime($msg['created_at'])) ?></span>
                    </div>
                    <p class="text-xs text-blue-600 mb-1">Re: <?= $msg['make'] ?> <?= $msg['model'] ?></p>
                    <p class="text-gray-600 text-sm"><?= $msg['message'] ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="p-4 text-center text-gray-500">No inquiries yet.</div>
        <?php endif; ?>
    </div>
</div>

    </div>

    <!-- Manage Listings -->
    <div class="mt-12 bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
            <h3 class="font-bold text-gray-700">My Listings</h3>
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">Total: <?= count($my_cars) ?></span>
        </div>
        <div class="max-h-[300px] overflow-y-auto">
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
                    <?php foreach($my_cars as $car):
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
                            <button onclick="location.href='edit_car.php?id=<?= $car['id'] ?>'" class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded hover:bg-yellow-200">Edit</button>
                            <?php if($car['status'] != 'Sold'): ?>
                            <button onclick="location.href='update_car.php?id=<?= $car['id'] ?>&action=mark_sold'" 
                              class="bg-blue-100 text-blue-700 px-3 py-1 rounded hover:bg-blue-200">
                               Mark Sold
                                </button>
                            <?php endif; ?>
                            <button onclick="location.href='update_car.php?id=<?= $car['id'] ?>&action=remove'" class="bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200">Remove</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- Reservations -->
<div class="mt-12 bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="font-bold text-gray-700">Reservations</h3>
        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">Total: <?= count($reservations) ?></span>
    </div>
    <div class="max-h-[300px] overflow-y-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-2 text-left">Buyer</th>
                    <th class="px-4 py-2 text-left">Vehicle</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Amount</th>
                    <th class="px-4 py-2 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach($reservations as $res): 
                    $resStatus = $res['status'] ?? 'Pending';
                ?>
                <tr>
                    <td class="px-4 py-3"><?= $res['buyer_name'] ?></td>
                    <td class="px-4 py-3"><?= ($res['year'] ?? '') . ' ' . ($res['make'] ?? '') . ' ' . ($res['model'] ?? '') ?></td>
                    <td class="px-4 py-3 font-semibold text-orange-600"><?= $resStatus ?></td>
                   <td class="px-4 py-3">
    <?php if($resStatus == 'Pending'): ?>
        <input type="number" name="amount_<?= $res['id'] ?>" id="amount_<?= $res['id'] ?>" 
               class="border p-1 rounded w-24" placeholder="₱" value="<?= $res['amount'] ?? '' ?>">
    <?php else: ?>
        $<?= number_format($res['amount'] ?? 0, 2) ?>
    <?php endif; ?>
</td>
<td class="px-4 py-3 text-right space-x-2">
    <?php if($resStatus == 'Pending'): ?>
        <button onclick="approveReservation(<?= $res['id'] ?>)" 
                class="bg-green-100 text-green-700 px-3 py-1 rounded hover:bg-green-200">Approve</button>
        <a href="update_reservation.php?id=<?= $res['id'] ?>&action=cancel" 
           class="bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200">Cancel</a>
    <?php else: ?>
        <span class="text-gray-500">No actions</span>
    <?php endif; ?>
</td>

                </tr>
                <?php endforeach; ?>
                <?php if(count($reservations) == 0): ?>
                    <tr><td colspan="5" class="text-center text-gray-500 py-4">No reservations yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
<!-- Chat Modal -->
<div id="chatModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-lg p-4 w-full max-w-md flex flex-col h-96">
      <div class="flex justify-between items-center mb-2">
          <h3 id="chatModalTitle" class="font-bold text-gray-700">Chat</h3>
          <button onclick="closeChat()" class="text-gray-500 hover:text-gray-800">&times;</button>
      </div>
      <div id="chatMessages" class="flex-1 overflow-y-auto border p-2 rounded mb-2"></div>
      <form id="chatForm" class="flex gap-2">
          <input type="hidden" name="receiver_id" id="chatReceiver">
          <input type="hidden" name="car_id" id="chatCar">
          <input type="text" name="reply_message" id="chatInput" placeholder="Type a message..." class="flex-1 p-2 border rounded" required>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Send</button>
      </form>
  </div>
<script>
let currentBuyerId = null;
let currentCarId = null;






// Load messages
function loadMessages() {
    fetch('fetch_messages.php?buyer_id=' + currentBuyerId + '&car_id=' + currentCarId)
        .then(res => res.json())
        .then(data => {
            const chatBox = document.getElementById('chatMessages');
            chatBox.innerHTML = '';

            data.forEach(msg => {
                const div = document.createElement('div');
                div.className = msg.sender === 'seller'
                    ? 'text-right mb-2'
                    : 'text-left mb-2';

                div.innerHTML = `
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded
                        ${msg.sender === 'seller'
                            ? 'bg-blue-200 text-blue-800'
                            : 'bg-gray-200 text-gray-800'}">
                        ${msg.message}

                        ${msg.sender === 'seller'
                            ? `<button onclick="deleteMessage(${msg.id})"
                                class="text-red-600 text-xs hover:text-red-800">🗑️</button>`
                            : ''}
                    </span>
                `;
                chatBox.appendChild(div);
            });

            chatBox.scrollTop = chatBox.scrollHeight;
        });
}

function deleteMessage(messageId) {
    if (!confirm("Delete this message?")) return;

    const fd = new FormData();
    fd.append('message_id', messageId);

    fetch('delete_message.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.text())
    .then(resp => {
        console.log("DELETE RESPONSE:", resp);
        if(resp.trim() === 'deleted'){
            loadMessages();
        } else {
            alert("Delete failed: " + resp);
        }
    })
    .catch(err => {
        console.error("FETCH ERROR", err);
    });
}




// Send message
document.getElementById('chatForm').addEventListener('submit', function(e){
    e.preventDefault();

    const input = document.getElementById('chatInput');
    const text = input.value.trim();
    if(!text) return;

    const formData = new FormData(this);

    // ✅ CLEAR IMMEDIATELY
    input.value = '';

    fetch('send_message.php', {
        method: 'POST',
        body: formData
    }).then(() => loadMessages());
});

</script>

</div>
<script>
let chatInterval = null;

function openChat(receiver_id, car_id, buyer_name, car_name) {
    currentBuyerId = receiver_id;
    currentCarId = car_id;

    document.getElementById('chatModal').classList.remove('hidden');
    document.getElementById('chatModal').classList.add('flex');
    document.getElementById('chatModalTitle').innerText = buyer_name + " - " + car_name;
    document.getElementById('chatReceiver').value = receiver_id;
    document.getElementById('chatCar').value = car_id;

    loadMessages();

    // 🔥 auto refresh every 3 seconds
    chatInterval = setInterval(loadMessages, 1000);
}

function closeChat() {
    document.getElementById('chatModal').classList.add('hidden');
    document.getElementById('chatModal').classList.remove('flex');

    if(chatInterval){
        clearInterval(chatInterval);
        chatInterval = null;
    }
}
</script>


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
   
    closeModal();
    alert('Action executed for demo'); // Replace with real AJAX call
};
function approveReservation(resId) {
    let amount = document.getElementById('amount_' + resId).value;
    if(!amount || amount <= 0) {
        alert("Please enter a valid amount.");
        return;
    }

    window.location.href = `update_reservation.php?id=${resId}&action=approve&amount=${amount}`;
}

</script>





<?php include 'footer.php'; ?>

