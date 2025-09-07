<?php
// Get blocked IPs endpoint
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$blockedFile = __DIR__ . '/../data/blocked_ips.json';

$blockedIPs = [];
if (file_exists($blockedFile)) {
    $json = file_get_contents($blockedFile);
    $blockedIPs = json_decode($json, true);
    if (!is_array($blockedIPs)) {
        $blockedIPs = [];
    }
}

echo json_encode($blockedIPs);
?>
