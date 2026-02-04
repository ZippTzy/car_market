<?php 
include 'db.php'; 
include 'header.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'buyer') {
    echo "<script>window.location.href='login.php?role=buyer';</script>";
    exit;
}

$userId = $_SESSION['user_id'] ?? 1; 
?>

<div class="max-w-7xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">My Garage</h1>
    <p class="text-gray-500 mb-8">Manage your saved vehicles and reservations.</p>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Sidebar -->
        <div class="col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-xl">
                        <?= substr($_SESSION['user_name'] ?? 'U', 0, 1) ?>
                    </div>
                    <div>
                        <div class="font-bold text-gray-900"><?= $_SESSION['user_name'] ?? 'Buyer' ?></div>
                        <div class="text-xs text-gray-500">Buyer Account</div>
                    </div>
                </div>
                <nav class="space-y-1">
                    <a href="#" class="block px-4 py-2 bg-blue-50 text-blue-700 font-medium rounded-lg">
                        <i class="fas fa-heart mr-2"></i> Saved Cars
                    </a>
                   <a href="buyer_messages.php"
   class="block px-4 py-2 text-gray-600 hover:bg-gray-50 font-medium rounded-lg">
   <i class="fas fa-envelope mr-2"></i> Messages
</a>

                    <a href="#" class="block px-4 py-2 text-gray-600 hover:bg-gray-50 font-medium rounded-lg">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-span-1 md:col-span-3">
            <div class="bg-white rounded-xl shadow-sm border p-6 min-h-[400px]">
                <h2 class="text-xl font-bold text-gray-900 mb-6 border-b pb-4">Saved Vehicles</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php
                    // Fetch saved cars for this buyer
                    $sql = "SELECT cars.* FROM favorites 
                            JOIN cars ON favorites.car_id = cars.id 
                            WHERE favorites.user_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$userId]);
                    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if(count($favorites) > 0) {
                        foreach($favorites as $car):
                    ?>
                    <div class="border rounded-lg p-4 hover:shadow-md transition relative group">
                        <img src="<?= $car['image_url'] ?>" class="w-full h-40 object-cover rounded-lg mb-4">
                        <h3 class="font-bold text-lg"><?= $car['year'] ?> <?= $car['make'] ?> <?= $car['model'] ?></h3>
                        <p class="text-blue-600 font-bold text-xl mb-2">$<?= number_format($car['price']) ?></p>
                        <div class="flex gap-2">
                            <button
                               onclick="openChat(<?= $car['seller_id'] ?>, <?= $car['id'] ?>, 'Seller', '<?= addslashes($car['make'].' '.$car['model']) ?>')"
                              class="flex-1 bg-blue-600 text-white py-2 rounded text-sm hover:bg-blue-700">
                                           Contact Seller
                                             </button>


                            <!-- Reserve Car Form -->
                            <form action="reserve_car.php" method="POST" class="flex-1">
                                <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                <button type="submit" class="w-full bg-yellow-500 text-white py-2 rounded hover:bg-yellow-600">
                                    Reserve Car
                                </button>
                            </form>

                            <form action="toggle_favorite.php" method="POST">
    <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
    <input type="hidden" name="action" value="remove">
    <button type="submit"
        class="bg-red-50 text-red-600 p-2 rounded hover:bg-red-100"
        title="Remove">
        <i class="fas fa-trash"></i>
    </button>
</form>

                        </div>
                    </div>
                    <?php
                        endforeach;
                    } else {
                        echo "<div class='col-span-2 text-center py-10'>
                                <div class='text-gray-300 text-6xl mb-4'><i class='fas fa-heart-broken'></i></div>
                                <p class='text-gray-500'>You haven't saved any cars yet.</p>
                                <a href='listings.php' class='text-blue-600 font-bold mt-2 inline-block'>Browse Inventory</a>
                              </div>";
                    }
                    ?>
                </div>

                <!-- Current Reservations -->
                <h2 class="text-xl font-bold text-gray-900 mt-12 mb-6 border-b pb-4">My Reservations</h2>
                <div class="space-y-4 max-h-64 overflow-y-auto pr-2">

                    <?php
                
// Current Reservations
$resSql = "SELECT reservations.*, cars.make, cars.model, cars.year, cars.price 
           FROM reservations 
           JOIN cars ON reservations.car_id = cars.id 
           WHERE reservations.user_id = ? 
           ORDER BY reservations.created_at DESC";
$resStmt = $pdo->prepare($resSql);
$resStmt->execute([$userId]);
$reservations = $resStmt->fetchAll(PDO::FETCH_ASSOC);

if(count($reservations) > 0) {
    foreach($reservations as $res):
?>
<div class="border p-4 rounded-lg flex justify-between items-center">
    <div>
        <h3 class="font-bold"><?= $res['year'] ?> <?= $res['make'] ?> <?= $res['model'] ?></h3>
        <p class="text-gray-600">Price: $<?= number_format($res['price']) ?></p>
        <p class="text-sm text-gray-500">Status: <?= $res['status'] ?></p>
    </div>
    <div>
        <?php if($res['status'] == 'Pending'): ?>
            <span class="px-3 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">Waiting Approval</span>
        <?php elseif($res['status'] == 'Approved'): ?>
            <span class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded">Approved</span>
        <?php else: ?>
            <span class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded">Cancelled</span>
        <?php endif; ?>
    </div>
</div>
<?php
    endforeach;
} else {
    echo "<p class='text-gray-500'>No reservations yet.</p>";
}
?>

                </div>
            </div>
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
          <input type="text" name="reply_message" id="chatInput"
                 placeholder="Type a message..."
                 class="flex-1 p-2 border rounded" required>
          <button class="bg-blue-600 text-white px-4 py-2 rounded">Send</button>
      </form>
  </div>
</div>
<script>
let currentSellerId = null;
let currentCarId = null;
let chatInterval = null;

function openChat(seller_id, car_id, seller_name, car_name) {
    currentSellerId = seller_id;
    currentCarId = car_id;

    document.getElementById('chatModal').classList.remove('hidden');
    document.getElementById('chatModal').classList.add('flex');

    document.getElementById('chatModalTitle').innerText =
        seller_name + " - " + car_name;

    document.getElementById('chatReceiver').value = seller_id;
    document.getElementById('chatCar').value = car_id;

    loadMessages();
    chatInterval = setInterval(loadMessages, 1000);
}

function closeChat() {
    document.getElementById('chatModal').classList.add('hidden');
    document.getElementById('chatModal').classList.remove('flex');
    clearInterval(chatInterval);
}

// Load messages
function loadMessages() {
    fetch('fetch_messages_buyer.php?seller_id=' + currentSellerId + '&car_id=' + currentCarId)

    
        .then(res => res.json())
        .then(data => {
            const box = document.getElementById('chatMessages');
            box.innerHTML = '';

            data.forEach(msg => {
    const div = document.createElement('div');
    div.className = msg.sender === 'buyer'
        ? 'text-right mb-2'
        : 'text-left mb-2';

    div.innerHTML = `
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded
            ${msg.sender === 'buyer'
                ? 'bg-blue-200 text-blue-800'
                : 'bg-gray-200 text-gray-800'}">

            ${msg.message}

            ${msg.sender === 'buyer'
                ? `<button onclick="deleteMessage(${msg.id})"
                    class="text-red-600 text-xs hover:text-red-800">🗑️</button>`
                : ''}
        </span>
    `;

    box.appendChild(div);
});


            box.scrollTop = box.scrollHeight;
        });
}

// Send message
document.getElementById('chatForm').addEventListener('submit', e => {
    
    e.preventDefault();

    const input = document.getElementById('chatInput');
    if (!input.value.trim()) return;

    const fd = new FormData(e.target);
    input.value = '';

    fetch('send_message.php', {
        method: 'POST',
        body: fd
    }).then(loadMessages);
});


// DELETE MESSAGE (GLOBAL FUNCTION)
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
        if (resp.trim() === 'deleted') {
            loadMessages();
        } else {
            alert("Delete failed");
        }
    });
}
</script>

<?php include 'footer.php'; ?>
