<?php
session_start();
include_once __DIR__ . '/../includes/config.php';

// Check if the admin is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Path to the sales JSON files
$salesFile = __DIR__ . '/../sales.json';
$deletedSalesFile = __DIR__ . '/../deleted_sales.json';

// Handle delete order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        header('Location: sales.php');
        exit;
    }
    
    $order_id = $_POST['order_id'] ?? '';
    
    if (empty($order_id)) {
        $_SESSION['error_message'] = 'Order ID is required.';
        header('Location: sales.php');
        exit;
    }
    
    // Load sales data
    $sales = [];
    if (file_exists($salesFile)) {
        $json_content = file_get_contents($salesFile);
        $sales = json_decode($json_content, true);
        if (!is_array($sales)) {
            $sales = [];
        }
    }
    
    // Find and remove the order
    $order_to_delete = null;
    $updated_sales = [];
    
    foreach ($sales as $sale) {
        if ($sale['order_id'] === $order_id) {
            $order_to_delete = $sale;
        } else {
            $updated_sales[] = $sale;
        }
    }
    
    if ($order_to_delete) {
        // Load deleted sales
        $deleted_sales = [];
        if (file_exists($deletedSalesFile)) {
            $json_content = file_get_contents($deletedSalesFile);
            $deleted_sales = json_decode($json_content, true);
            if (!is_array($deleted_sales)) {
                $deleted_sales = [];
            }
        }
        
        // Add deletion metadata
        $order_to_delete['deleted_at'] = date('Y-m-d H:i:s');
        $order_to_delete['deleted_by'] = $_SESSION['admin_username'] ?? 'Unknown';
        
        // Move to deleted sales
        $deleted_sales[] = $order_to_delete;
        
        // Save both files
        file_put_contents($deletedSalesFile, json_encode($deleted_sales, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($salesFile, json_encode($updated_sales, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        $_SESSION['success_message'] = 'Order moved to deleted orders successfully!';
    } else {
        $_SESSION['error_message'] = 'Order not found.';
    }
    
    header('Location: sales.php');
    exit;
}

// If not POST, redirect to sales
header('Location: sales.php');
exit;
?>
