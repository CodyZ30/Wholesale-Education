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

// Combine all orders for search
$all_orders = array_merge($sales, $deleted_sales);

// Get search parameters
$search_query = $_GET['q'] ?? '';
$status_filter = $_GET['status'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$amount_min = $_GET['amount_min'] ?? '';
$amount_max = $_GET['amount_max'] ?? '';

// Filter orders
$filtered_orders = $all_orders;

// Apply search query
if (!empty($search_query)) {
    $filtered_orders = array_filter($filtered_orders, function($order) use ($search_query) {
        $search_lower = strtolower($search_query);
        return (
            strpos(strtolower($order['order_id']), $search_lower) !== false ||
            strpos(strtolower($order['customer_name']), $search_lower) !== false ||
            strpos(strtolower($order['customer_email']), $search_lower) !== false
        );
    });
}

// Apply status filter
if ($status_filter !== 'all') {
    if ($status_filter === 'active') {
        $filtered_orders = array_filter($filtered_orders, function($order) use ($sales) {
            return in_array($order, $sales);
        });
    } else if ($status_filter === 'deleted') {
        $filtered_orders = array_filter($filtered_orders, function($order) use ($deleted_sales) {
            return in_array($order, $deleted_sales);
        });
    }
}

// Apply date filter
if (!empty($date_from) || !empty($date_to)) {
    $filtered_orders = array_filter($filtered_orders, function($order) use ($date_from, $date_to) {
        $order_date = strtotime($order['order_date']);
        $from_date = !empty($date_from) ? strtotime($date_from) : 0;
        $to_date = !empty($date_to) ? strtotime($date_to . ' 23:59:59') : PHP_INT_MAX;
        
        return $order_date >= $from_date && $order_date <= $to_date;
    });
}

// Apply amount filter
if (!empty($amount_min) || !empty($amount_max)) {
    $filtered_orders = array_filter($filtered_orders, function($order) use ($amount_min, $amount_max) {
        $amount = (float)$order['total_amount'];
        $min = !empty($amount_min) ? (float)$amount_min : 0;
        $max = !empty($amount_max) ? (float)$amount_max : PHP_FLOAT_MAX;
        
        return $amount >= $min && $amount <= $max;
    });
}

// Sort by order date (newest first)
usort($filtered_orders, function($a, $b) {
    return strtotime($b['order_date']) - strtotime($a['order_date']);
});

admin_layout_start('Search Orders', 'sales');
?>

<div class="dashboard-card md:col-span-3">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold">Search Orders</h2>
        <a href="sales.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to Sales
        </a>
    </div>
</div>

<div class="dashboard-card md:col-span-3">
    <h3 class="text-xl font-semibold mb-4">Search & Filter</h3>
    
    <form method="GET" action="search_orders.php" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="q" class="block text-sm font-semibold mb-2">Search:</label>
                <input type="text" name="q" id="q" value="<?php echo htmlspecialchars($search_query); ?>" 
                       placeholder="Order ID, Customer Name, or Email" 
                       class="border rounded w-full px-3 py-2 bg-transparent">
            </div>
            
            <div>
                <label for="status" class="block text-sm font-semibold mb-2">Status:</label>
                <select name="status" id="status" class="border rounded w-full px-3 py-2 bg-transparent">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Orders</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active Only</option>
                    <option value="deleted" <?php echo $status_filter === 'deleted' ? 'selected' : ''; ?>>Deleted Only</option>
                </select>
            </div>
            
            <div>
                <label for="date_from" class="block text-sm font-semibold mb-2">From Date:</label>
                <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($date_from); ?>" 
                       class="border rounded w-full px-3 py-2 bg-transparent">
            </div>
            
            <div>
                <label for="date_to" class="block text-sm font-semibold mb-2">To Date:</label>
                <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($date_to); ?>" 
                       class="border rounded w-full px-3 py-2 bg-transparent">
            </div>
            
            <div>
                <label for="amount_min" class="block text-sm font-semibold mb-2">Min Amount:</label>
                <input type="number" step="0.01" name="amount_min" id="amount_min" value="<?php echo htmlspecialchars($amount_min); ?>" 
                       placeholder="0.00" class="border rounded w-full px-3 py-2 bg-transparent">
            </div>
            
            <div>
                <label for="amount_max" class="block text-sm font-semibold mb-2">Max Amount:</label>
                <input type="number" step="0.01" name="amount_max" id="amount_max" value="<?php echo htmlspecialchars($amount_max); ?>" 
                       placeholder="999.99" class="border rounded w-full px-3 py-2 bg-transparent">
            </div>
        </div>
        
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search mr-2"></i>Search
            </button>
            <a href="search_orders.php" class="btn btn-secondary">
                <i class="fas fa-times mr-2"></i>Clear
            </a>
        </div>
    </form>
</div>

<div class="dashboard-card md:col-span-3">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Search Results (<?php echo count($filtered_orders); ?>)</h3>
        <?php if (count($filtered_orders) > 0): ?>
        <a href="export_orders.php" class="btn btn-secondary">
            <i class="fas fa-download mr-2"></i>Export Results
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($filtered_orders)): ?>
        <div class="text-center py-8 text-gray-400">
            <i class="fas fa-search text-4xl mb-4"></i>
            <p>No orders found matching your criteria.</p>
            <p class="text-sm mt-2">Try adjusting your search parameters.</p>
        </div>
    <?php else: ?>
        <div class="rounded-lg border overflow-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left p-2">Order ID</th>
                        <th class="text-left p-2">Customer</th>
                        <th class="text-left p-2">Total</th>
                        <th class="text-left p-2">Date</th>
                        <th class="text-left p-2">Status</th>
                        <th class="text-left p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filtered_orders as $order): ?>
                        <tr class="border-t">
                            <td class="p-2">
                                <a href="order_detail.php?id=<?php echo htmlspecialchars($order['order_id']); ?>" class="text-blue-400 hover:text-blue-300 font-medium">
                                    <?php echo htmlspecialchars((string)$order['order_id']); ?>
                                </a>
                            </td>
                            <td class="p-2">
                                <div>
                                    <div class="font-medium"><?php echo htmlspecialchars((string)$order['customer_name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars((string)$order['customer_email']); ?></div>
                                </div>
                            </td>
                            <td class="p-2">$<?php echo htmlspecialchars(number_format((float)$order['total_amount'], 2)); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars((string)$order['order_date']); ?></td>
                            <td class="p-2">
                                <?php if (in_array($order, $deleted_sales)): ?>
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Deleted</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-2">
                                <div class="flex gap-2">
                                    <a href="order_detail.php?id=<?php echo htmlspecialchars($order['order_id']); ?>" class="btn btn-primary">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                    <a href="print_order.php?id=<?php echo htmlspecialchars($order['order_id']); ?>" class="btn btn-secondary" target="_blank">
                                        <i class="fas fa-print mr-1"></i>Print
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php admin_layout_end(); ?>
