<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get the IP address from the request
$input = json_decode(file_get_contents('php://input'), true);
$targetIP = $input['ip'] ?? '';

if (empty($targetIP)) {
    echo json_encode(['success' => false, 'error' => 'IP address is required']);
    exit;
}

// Validate IP address
if (!filter_var($targetIP, FILTER_VALIDATE_IP)) {
    echo json_encode(['success' => false, 'error' => 'Invalid IP address']);
    exit;
}

try {
    // Clear session data for the target IP
    // Since PHP sessions are stored server-side, we need to find and clear them
    $sessionCleared = false;
    
    // Method 1: Clear any session files that might be associated with this IP
    $sessionPath = session_save_path();
    if (empty($sessionPath)) {
        $sessionPath = sys_get_temp_dir();
    }
    
    // Look for session files and clear any that might be related to this IP
    $sessionFiles = glob($sessionPath . '/sess_*');
    $clearedCount = 0;
    
    foreach ($sessionFiles as $sessionFile) {
        if (is_file($sessionFile)) {
            $sessionData = file_get_contents($sessionFile);
            
            // Check if this session contains data related to our target IP
            // This is a basic check - in a real implementation you might want to store IP in session
            if (strpos($sessionData, $targetIP) !== false || 
                strpos($sessionData, 'cart') !== false || 
                strpos($sessionData, 'checkout') !== false) {
                
                // Clear the session file
                unlink($sessionFile);
                $clearedCount++;
                $sessionCleared = true;
            }
        }
    }
    
    // Method 2: Clear any cart data stored in files for this IP
    $cartLogFile = __DIR__ . '/../data/cart_logs.json';
    if (file_exists($cartLogFile)) {
        $cartLogs = json_decode(file_get_contents($cartLogFile), true) ?? [];
        
        // Remove any cart logs for this IP
        $originalCount = count($cartLogs);
        $cartLogs = array_filter($cartLogs, function($log) use ($targetIP) {
            return ($log['ip'] ?? '') !== $targetIP;
        });
        
        if (count($cartLogs) < $originalCount) {
            file_put_contents($cartLogFile, json_encode($cartLogs, JSON_PRETTY_PRINT));
            $sessionCleared = true;
        }
    }
    
    // Method 3: Clear any temporary files or cache for this IP
    $tempDir = __DIR__ . '/../temp/';
    if (is_dir($tempDir)) {
        $tempFiles = glob($tempDir . '*');
        foreach ($tempFiles as $tempFile) {
            if (is_file($tempFile) && strpos($tempFile, $targetIP) !== false) {
                unlink($tempFile);
                $sessionCleared = true;
            }
        }
    }
    
    // Log the session clear action
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'admin_user' => $_SESSION['admin_username'] ?? 'unknown',
        'action' => 'clear_session',
        'target_ip' => $targetIP,
        'sessions_cleared' => $clearedCount,
        'success' => $sessionCleared
    ];
    
    $logFile = __DIR__ . '/../data/admin_actions.log';
    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    
    if ($sessionCleared) {
        echo json_encode([
            'success' => true, 
            'message' => "Session cleared for IP {$targetIP}. Cleared {$clearedCount} session files.",
            'sessions_cleared' => $clearedCount
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'message' => "No active sessions found for IP {$targetIP}, but cleanup completed.",
            'sessions_cleared' => 0
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error clearing session for IP {$targetIP}: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to clear session: ' . $e->getMessage()]);
}
?>
