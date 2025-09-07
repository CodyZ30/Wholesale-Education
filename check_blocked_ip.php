<?php
// Global IP blocking check - include this at the top of pages to block users
// Note: session_start() should be called by the main page, not here

$blockedFile = __DIR__ . '/data/blocked_ips.json';

// Get visitor's IP address
function getVisitorIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            // Handle comma-separated IPs (from proxies)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

$visitorIP = getVisitorIP();

// Check if IP is blocked
if (file_exists($blockedFile)) {
    $json = file_get_contents($blockedFile);
    $blockedIPs = json_decode($json, true);
    
    if (is_array($blockedIPs)) {
        foreach ($blockedIPs as $blocked) {
            if ($blocked['ip'] === $visitorIP) {
                // IP is blocked - show blocked page
                http_response_code(403);
                ?>
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Access Denied - <?php echo defined('SITE_NAME') ? SITE_NAME : 'Gotta.Fish'; ?></title>
                    <script src="https://cdn.tailwindcss.com"></script>
                </head>
                <body class="bg-gray-900 text-white min-h-screen flex items-center justify-center">
                    <div class="text-center max-w-md mx-auto p-8">
                        <div class="mb-8">
                            <h1 class="text-6xl mb-4">🚫</h1>
                            <h2 class="text-3xl font-bold mb-4">Access Denied</h2>
                            <p class="text-gray-300 mb-6">
                                Your IP address has been blocked from accessing this website.
                            </p>
                            <?php if (!empty($blocked['reason'])): ?>
                            <div class="bg-red-900 border border-red-700 rounded-lg p-4 mb-6">
                                <p class="text-sm text-red-200">
                                    <strong>Reason:</strong> <?php echo htmlspecialchars($blocked['reason']); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            <p class="text-sm text-gray-400">
                                If you believe this is an error, please contact the website administrator.
                            </p>
                        </div>
                        <div class="text-xs text-gray-500">
                            Blocked on: <?php echo htmlspecialchars($blocked['blocked_date'] ?? 'Unknown'); ?>
                        </div>
                    </div>
                </body>
                </html>
                <?php
                exit;
            }
        }
    }
}
?>
