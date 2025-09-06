<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access'
    ]);
    exit;
}

$chatFile = __DIR__ . '/../../data/live_chat.json';
$chats = [];
if (file_exists($chatFile)) {
    $chats = json_decode(file_get_contents($chatFile), true) ?: [];
}

$newMessages = [];

// Check for new messages in the last 5 minutes
$fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));

foreach ($chats as $chatId => $chat) {
    if (isset($chat['messages']) && is_array($chat['messages'])) {
        foreach ($chat['messages'] as $message) {
            // Check if message is from visitor and is recent
            if ($message['sender'] === 'visitor' && 
                isset($message['timestamp']) && 
                $message['timestamp'] > $fiveMinutesAgo) {
                
                $newMessages[] = [
                    'chat_id' => $chatId,
                    'visitor_name' => $chat['visitor_name'] ?? 'Anonymous',
                    'message' => $message['message'],
                    'timestamp' => $message['timestamp']
                ];
            }
        }
    }
}

echo json_encode([
    'success' => true,
    'new_messages' => $newMessages,
    'total_chats' => count($chats),
    'active_chats' => count(array_filter($chats, fn($c) => $c['status'] === 'active'))
]);
?>
