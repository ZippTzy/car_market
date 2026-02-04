<?php
include 'db.php';
include 'header.php';

// Buyer only
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'buyer') {
    header("Location: login.php");
    exit;
}

$buyerId = $_SESSION['user_id'];
$sql = "
SELECT 
    u.id AS seller_id,
    u.name AS seller_name,
    MAX(m.created_at) AS last_message
FROM messages m
JOIN users u ON u.id = 
    CASE 
        WHEN m.sender_id = ? THEN m.receiver_id
        ELSE m.sender_id
    END
WHERE m.sender_id = ? OR m.receiver_id = ?
GROUP BY u.id, u.name
ORDER BY last_message DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$buyerId, $buyerId, $buyerId]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">Messages</h2>

        <?php if (count($conversations) == 0): ?>
            <p class="text-gray-500">No conversations yet.</p>
        <?php endif; ?>

        <ul class="divide-y">
            <?php foreach ($conversations as $c): ?>
                <li>
                    <button
                        onclick="openChat(<?= $c['seller_id'] ?>)"
                        class="w-full text-left p-4 hover:bg-gray-50 flex justify-between"
                    >
                        <span class="font-medium"><?= htmlspecialchars($c['seller_name']) ?></span>
                        <span class="text-xs text-gray-400">
                            <?= date('M d, h:i A', strtotime($c['last_message'])) ?>
                        </span>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
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

function openChat(seller_id) {
    currentSellerId = seller_id;
    currentCarId = 0;

    document.getElementById('chatModal').classList.remove('hidden');
    document.getElementById('chatModal').classList.add('flex');

    document.getElementById('chatModalTitle').innerText = "Conversation";
    document.getElementById('chatReceiver').value = seller_id;
    document.getElementById('chatCar').value = 0;

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
    fetch('fetch_messages_buyer.php?seller_id=' + currentSellerId)


    
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