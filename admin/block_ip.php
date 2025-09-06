<?php
// Block IP address endpoint
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
$reason = trim($data['reason'] ?? '');

// Validate IP address
if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    echo json_encode(['success' => false, 'error' => 'Invalid IP address format']);
    exit;
}

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

// Check if IP is already blocked
foreach ($blockedIPs as $blocked) {
    if ($blocked['ip'] === $ip) {
        echo json_encode(['success' => false, 'error' => 'IP address is already blocked']);
        exit;
    }
}

// Add new blocked IP
$blockedIPs[] = [
    'ip' => $ip,
    'reason' => $reason,
    'blocked_date' => date('Y-m-d H:i:s'),
    'blocked_by' => $_SESSION['admin_username'] ?? 'Admin'
];

// Save to file
if (file_put_contents($blockedFile, json_encode($blockedIPs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
    echo json_encode(['success' => true, 'message' => 'IP address blocked successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save blocked IP']);
}
?>
