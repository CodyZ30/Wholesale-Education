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
$message = trim($input['message'] ?? '');
$sender = $input['sender'] ?? 'visitor';

if (empty($chatId) || empty($message)) {
    echo json_encode([
        'success' => false,
        'error' => 'Chat ID and message are required'
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

// Add message to chat
$messageData = [
    'id' => uniqid(),
    'sender' => $sender,
    'message' => $message,
    'timestamp' => date('Y-m-d H:i:s'),
    'type' => 'text'
];

if ($sender === 'admin') {
    $messageData['admin_name'] = $input['admin_name'] ?? 'Admin';
}

$chats[$chatId]['messages'][] = $messageData;
$chats[$chatId]['last_activity'] = date('Y-m-d H:i:s');
$chats[$chatId]['status'] = 'active';

file_put_contents($chatFile, json_encode($chats, JSON_PRETTY_PRINT));

// Check if there are any pending admin responses
$adminResponse = null;
if ($sender === 'visitor') {
    // In a real implementation, you might check for auto-responses or queue the message for admin review
    // For now, we'll just acknowledge the message was received
}

echo json_encode([
    'success' => true,
    'message' => 'Message sent successfully',
    'message_id' => $messageData['id'],
    'admin_response' => $adminResponse
]);
?>
