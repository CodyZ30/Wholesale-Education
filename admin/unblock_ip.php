<?php
// Unblock IP address endpoint
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['ip']) || empty($data['ip'])) {
    echo json_encode(['success' => false, 'error' => 'IP address is required']);
    exit;
}

$ip = trim($data['ip']);

$blockedFile = __DIR__ . '/../data/blocked_ips.json';

// Load existing blocked IPs
$blockedIPs = [];
if (file_exists($blockedFile)) {
    $json = file_get_contents($blockedFile);
    $blockedIPs = json_decode($json, true);
    if (!is_array($blockedIPs)) {
        $blockedIPs = [];
    }
}

// Find and remove the IP
$found = false;
$blockedIPs = array_filter($blockedIPs, function($blocked) use ($ip, &$found) {
    if ($blocked['ip'] === $ip) {
        $found = true;
        return false; // Remove this entry
    }
    return true; // Keep this entry
});

if (!$found) {
    echo json_encode(['success' => false, 'error' => 'IP address is not blocked']);
    exit;
}

// Re-index array
$blockedIPs = array_values($blockedIPs);

// Save to file
if (file_put_contents($blockedFile, json_encode($blockedIPs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
    echo json_encode(['success' => true, 'message' => 'IP address unblocked successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save changes']);
}
?>
