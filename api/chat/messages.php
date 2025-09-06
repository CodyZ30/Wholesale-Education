<?php
session_start();
header('Content-Type: application/json');

$chatFile = __DIR__ . '/../../data/live_chat.json';
$chats = [];
if (file_exists($chatFile)) {
    $chats = json_decode(file_get_contents($chatFile), true) ?: [];
}

$chatId = $_GET['chat_id'] ?? '';
$since = $_GET['since'] ?? '';

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

$chat = $chats[$chatId];
$messages = $chat['messages'] ?? [];

// Filter messages if 'since' parameter is provided
if (!empty($since)) {
    $filteredMessages = [];
    $foundSince = false;
    
    foreach ($messages as $message) {
        if ($foundSince) {
            $filteredMessages[] = $message;
        } elseif ($message['id'] === $since) {
            $foundSince = true;
        }
    }
    $messages = $filteredMessages;
}

echo json_encode([
    'success' => true,
    'messages' => $messages,
    'chat_status' => $chat['status'] ?? 'waiting'
]);
?>
