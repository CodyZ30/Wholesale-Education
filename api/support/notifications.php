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

$supportFile = __DIR__ . '/../../data/support_tickets.json';
$tickets = [];
if (file_exists($supportFile)) {
    $tickets = json_decode(file_get_contents($supportFile), true) ?: [];
}

$newTickets = [];

// Check for new tickets in the last 10 minutes
$tenMinutesAgo = date('Y-m-d H:i:s', strtotime('-10 minutes'));

foreach ($tickets as $ticketId => $ticket) {
    // Check if ticket is new and not yet viewed
    if (isset($ticket['created_at']) && 
        $ticket['created_at'] > $tenMinutesAgo &&
        ($ticket['status'] === 'open' || $ticket['status'] === 'waiting')) {
        
        $newTickets[] = [
            'ticket_id' => $ticketId,
            'subject' => $ticket['subject'],
            'priority' => $ticket['priority'] ?? 'medium',
            'customer' => $ticket['name'],
            'created_at' => $ticket['created_at']
        ];
    }
}

echo json_encode([
    'success' => true,
    'new_tickets' => $newTickets,
    'total_tickets' => count($tickets),
    'open_tickets' => count(array_filter($tickets, fn($t) => $t['status'] === 'open'))
]);
?>
