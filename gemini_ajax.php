<?php
header('Content-Type: application/json');

// --- CONFIGURATION ---
$apiKey = "AIzaSyAln4X20lINTVQ-9DwFuIetFk3r7Os6mmY"; 
// ---------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid Request']);
    exit;
}

$type = $_POST['type'] ?? '';
$make = $_POST['make'] ?? '';
$model = $_POST['model'] ?? '';
$year = $_POST['year'] ?? '';
$mileage = $_POST['mileage'] ?? '';

if (!$apiKey || $apiKey === "AIzaSyAln4X20lINTVQ-9DwFuIetFk3r7Os6mmY") {
    echo json_encode(['success' => false, 'error' => 'API Key missing in gemini_ajax.php']);
    exit;
}

// Construct Prompt
if ($type == 'price') {
    $prompt = "Give me a single estimated price number (just the number, no text) for a used $year $make $model with $mileage miles.";
} elseif ($type == 'desc') {
    $prompt = "Write a short, exciting sales description (max 40 words) for a used $year $make $model. Mention it is in great condition.";
} else {
    echo json_encode(['success' => false, 'error' => 'Unknown type']);
    exit;
}

// Call Gemini API via cURL
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=" . $apiKey;

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $decoded = json_decode($response, true);
    $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? 'AI could not generate response.';
    echo json_encode(['success' => true, 'result' => $text]);
} else {
    echo json_encode(['success' => false, 'error' => 'Gemini API Error: ' . $response]);
}
?>