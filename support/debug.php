<?php
// Enable error reporting
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "Debug: Starting support page test...<br>";

// Test basic includes
echo "Testing includes...<br>";
try {
    include_once __DIR__ . '/../includes/config.php';
    echo "✓ Config included<br>";
} catch (Exception $e) {
    echo "✗ Config failed: " . $e->getMessage() . "<br>";
}

try {
    include_once __DIR__ . '/../check_blocked_ip.php';
    echo "✓ Check blocked IP included<br>";
} catch (Exception $e) {
    echo "✗ Check blocked IP failed: " . $e->getMessage() . "<br>";
}

// Test data files
echo "Testing data files...<br>";
$supportFile = __DIR__ . '/../data/support_tickets.json';
if (file_exists($supportFile)) {
    echo "✓ Support tickets file exists<br>";
    $tickets = json_decode(file_get_contents($supportFile), true) ?: [];
    echo "✓ Support tickets loaded: " . count($tickets) . " tickets<br>";
} else {
    echo "✗ Support tickets file missing<br>";
}

$knowledgeFile = __DIR__ . '/../data/knowledge_base.json';
if (file_exists($knowledgeFile)) {
    echo "✓ Knowledge base file exists<br>";
    $articles = json_decode(file_get_contents($knowledgeFile), true) ?: [];
    echo "✓ Knowledge base loaded: " . count($articles) . " articles<br>";
} else {
    echo "✗ Knowledge base file missing<br>";
}

echo "Debug complete!<br>";
?>
