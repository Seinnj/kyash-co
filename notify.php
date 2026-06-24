<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$TELEGRAM_TOKEN = '8706478281:AAFQKdItwTQ2X9H85rwUBB6rUHFMWHIfGzU';
$TELEGRAM_CHAT_ID = '8613664459';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'message required']);
    exit;
}

$message = $input['message'];
$telegramUrl = "https://api.telegram.org/bot{$TELEGRAM_TOKEN}/sendMessage";

$postData = [
    'chat_id' => $TELEGRAM_CHAT_ID,
    'text' => $message,
    'parse_mode' => 'HTML'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $telegramUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($httpCode === 200) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'sent']);
} else {
    http_response_code(500);
    error_log("Telegram error: HTTP $httpCode - $curlError - $response");
    echo json_encode(['success' => false, 'error' => 'telegram failed']);
}
?>
