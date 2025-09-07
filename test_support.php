<?php
echo "Testing support page...\n";
echo "Current directory: " . __DIR__ . "\n";
echo "Config file exists: " . (file_exists(__DIR__ . '/includes/config.php') ? 'YES' : 'NO') . "\n";
echo "Check blocked IP exists: " . (file_exists(__DIR__ . '/check_blocked_ip.php') ? 'YES' : 'NO') . "\n";
echo "Support tickets file exists: " . (file_exists(__DIR__ . '/data/support_tickets.json') ? 'YES' : 'NO') . "\n";
echo "Knowledge base file exists: " . (file_exists(__DIR__ . '/data/knowledge_base.json') ? 'YES' : 'NO') . "\n";

// Test includes
try {
    include_once __DIR__ . '/includes/config.php';
    echo "Config included successfully\n";
} catch (Exception $e) {
    echo "Config include failed: " . $e->getMessage() . "\n";
}

try {
    include_once __DIR__ . '/check_blocked_ip.php';
    echo "Check blocked IP included successfully\n";
} catch (Exception $e) {
    echo "Check blocked IP include failed: " . $e->getMessage() . "\n";
}
?>
