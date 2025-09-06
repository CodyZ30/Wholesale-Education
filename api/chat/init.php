<?php
session_start();
header('Content-Type: application/json');

// Initialize a new chat session
$chatFile = __DIR__ . '/../../data/live_chat.json';
$chats = [];
if (file_exists($chatFile)) {
    $chats = json_decode(file_get_contents($chatFile), true) ?: [];
}

$input = json_decode(file_get_contents('php://input'), true);
$visitorInfo = $input['visitor_info'] ?? [];

// Generate unique chat ID
$chatId = 'CHAT-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 8);

// Create new chat session
$chat = [
    'id' => $chatId,
    'visitor_name' => $visitorInfo['name'] ?? 'Anonymous',
    'visitor_email' => $visitorInfo['email'] ?? '',
    'visitor_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    'user_agent' => $visitorInfo['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
    'page_url' => $visitorInfo['page'] ?? $_SERVER['HTTP_REFERER'] ?? 'Unknown',
    'status' => 'waiting',
    'started_at' => date('Y-m-d H:i:s'),
    'last_activity' => date('Y-m-d H:i:s'),
    'messages' => [],
    'admin_notes' => []
];

$chats[$chatId] = $chat;
file_put_contents($chatFile, json_encode($chats, JSON_PRETTY_PRINT));

// Store chat ID in session
$_SESSION['chat_id'] = $chatId;

echo json_encode([
    'success' => true,
    'chat_id' => $chatId,
    'message' => 'Chat session initialized successfully'
]);
?>
