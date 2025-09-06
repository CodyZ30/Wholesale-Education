<?php
session_start();
include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/translation.php';
include_once __DIR__ . '/layout.php';

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

$error = '';
$success_message = '';

// Path to the sales JSON files
$salesFile = __DIR__ . '/../sales.json';
$deletedSalesFile = __DIR__ . '/../deleted_sales.json';

// Load sales data
$sales = [];
if (file_exists($salesFile)) {
    $json_content = file_get_contents($salesFile);
    $sales = json_decode($json_content, true);
    if (!is_array($sales)) {
        $sales = [];
    }
}

// Load deleted sales data
$deleted_sales = [];
if (file_exists($deletedSalesFile)) {
    $json_content = file_get_contents($deletedSalesFile);
    $deleted_sales = json_decode($json_content, true);
    if (!is_array($deleted_sales)) {
        $deleted_sales = [];
    }
}

// Handle export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'export') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else {
        $export_type = $_POST['export_type'] ?? 'active';
        $export_format = $_POST['export_format'] ?? 'csv';
        $date_from = $_POST['date_from'] ?? '';
        $date_to = $_POST['date_to'] ?? '';
        
        // Filter orders by date range if provided
        $orders_to_export = [];
        if ($export_type === 'active') {
            $orders_to_export = $sales;
        } else if ($export_type === 'deleted') {
            $orders_to_export = $deleted_sales;
        } else if ($export_type === 'all') {
            $orders_to_export = array_merge($sales, $deleted_sales);
        }
        
        // Apply date filter
        if (!empty($date_from) || !empty($date_to)) {
            $filtered_orders = [];
            foreach ($orders_to_export as $order) {
                $order_date = strtotime($order['order_date']);
                $from_date = !empty($date_from) ? strtotime($date_from) : 0;
                $to_date = !empty($date_to) ? strtotime($date_to . ' 23:59:59') : PHP_INT_MAX;
                
                if ($order_date >= $from_date && $order_date <= $to_date) {
                    $filtered_orders[] = $order;
                }
            }
            $orders_to_export = $filtered_orders;
        }
        
        if ($export_format === 'csv') {
            // Export as CSV
            $filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, [
                'Order ID',
                'Customer Name',
                'Customer Email',
                'Order Date',
                'Total Amount',
                'Items Count',
                'Status',
                'Deleted Date',
                'Deleted By'
            ]);
            
            // CSV data
            foreach ($orders_to_export as $order) {
                $items_count = count($order['items'] ?? []);
                $status = in_array($order, $deleted_sales) ? 'Deleted' : 'Active';
                $deleted_date = $order['deleted_at'] ?? '';
                $deleted_by = $order['deleted_by'] ?? '';
                
                fputcsv($output, [
                    $order['order_id'],
                    $order['customer_name'],
                    $order['customer_email'],
                    $order['order_date'],
                    $order['total_amount'],
                    $items_count,
                    $status,
                    $deleted_date,
                    $deleted_by
                ]);
            }
            
            fclose($output);
            exit;
            
        } else if ($export_format === 'json') {
            // Export as JSON
            $filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.json';
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            echo json_encode($orders_to_export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }
}

admin_layout_start('Export Orders', 'sales');
?>

<div class="dashboard-card md:col-span-3">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold">Export Orders</h2>
        <a href="sales.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to Sales
        </a>
    </div>
    
    <?php if ($error): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <h3 class="text-lg font-medium">Active Orders</h3>
        <p class="value"><?php echo count($sales); ?></p>
    </div>
    <div class="dashboard-card">
        <h3 class="text-lg font-medium">Deleted Orders</h3>
        <p class="value"><?php echo count($deleted_sales); ?></p>
    </div>
    <div class="dashboard-card">
        <h3 class="text-lg font-medium">Total Orders</h3>
        <p class="value"><?php echo count($sales) + count($deleted_sales); ?></p>
    </div>
</div>

<div class="dashboard-card md:col-span-3">
    <h3 class="text-xl font-semibold mb-4">Export Options</h3>
    
    <form method="POST" action="export_orders.php">
        <input type="hidden" name="action" value="export">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label for="export_type" class="block text-sm font-semibold mb-2">Export Type:</label>
                    <select name="export_type" id="export_type" class="border rounded w-full px-3 py-2 bg-transparent" required>
                        <option value="active">Active Orders Only</option>
                        <option value="deleted">Deleted Orders Only</option>
                        <option value="all">All Orders</option>
                    </select>
                </div>
                
                <div>
                    <label for="export_format" class="block text-sm font-semibold mb-2">Export Format:</label>
                    <select name="export_format" id="export_format" class="border rounded w-full px-3 py-2 bg-transparent" required>
                        <option value="csv">CSV (Excel Compatible)</option>
                        <option value="json">JSON (Raw Data)</option>
                    </select>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label for="date_from" class="block text-sm font-semibold mb-2">From Date (Optional):</label>
                    <input type="date" name="date_from" id="date_from" class="border rounded w-full px-3 py-2 bg-transparent">
                </div>
                
                <div>
                    <label for="date_to" class="block text-sm font-semibold mb-2">To Date (Optional):</label>
                    <input type="date" name="date_to" id="date_to" class="border rounded w-full px-3 py-2 bg-transparent">
                </div>
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-download mr-2"></i>Export Orders
            </button>
            <a href="sales.php" class="btn btn-secondary ml-2">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>

<div class="dashboard-card md:col-span-3">
    <h3 class="text-xl font-semibold mb-4">Export Information</h3>
    <div class="space-y-2 text-sm text-gray-600">
        <p><strong>CSV Format:</strong> Includes basic order information in a spreadsheet-compatible format.</p>
        <p><strong>JSON Format:</strong> Includes complete order data with all items and details.</p>
        <p><strong>Date Range:</strong> Leave blank to export all orders, or specify a range to filter.</p>
        <p><strong>Export Types:</strong></p>
        <ul class="list-disc list-inside ml-4">
            <li><strong>Active Orders:</strong> Currently active orders only</li>
            <li><strong>Deleted Orders:</strong> Orders that have been moved to deleted status</li>
            <li><strong>All Orders:</strong> Both active and deleted orders</li>
        </ul>
    </div>
</div>

<?php admin_layout_end(); ?>
