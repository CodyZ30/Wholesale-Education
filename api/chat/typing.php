<?php
session_start();
header('Content-Type: application/json');

$chatFile = __DIR__ . '/../../data/live_chat.json';
$chats = [];
if (file_exists($chatFile)) {
    $chats = json_decode(file_get_contents($chatFile), true) ?: [];
}

$input = json_decode(file_get_contents('php://input'), true);
$chatId = $input['chat_id'] ?? '';
$isTyping = $input['is_typing'] ?? false;

if (empty($chatId)) {
    echo json_encode([
        'success' => false,
        'error' => 'Chat ID is required'
    ]);
    exit;
}

if (!isset($chats[$chatId])) {
    echo json_encode([
        'success' => false,
        'error' => 'Chat session not found'
    ]);
    exit;
}

// Update typing status
$chats[$chatId]['visitor_typing'] = $isTyping;
$chats[$chatId]['last_activity'] = date('Y-m-d H:i:s');

file_put_contents($chatFile, json_encode($chats, JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'message' => 'Typing status updated'
]);
?>
