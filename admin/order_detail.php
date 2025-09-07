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

// Path to the sales JSON file
$salesFile = __DIR__ . '/../sales.json';
$deletedSalesFile = __DIR__ . '/../deleted_sales.json';

// Get order ID from URL
$order_id = $_GET['id'] ?? '';

if (empty($order_id)) {
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

// Find the specific order
$order = null;
foreach ($sales as $sale) {
    if ($sale['order_id'] === $order_id) {
        $order = $sale;
        break;
    }
}

if (!$order) {
    header('Location: sales.php');
    exit;
}

// Handle delete order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else {
        // Load deleted sales
        $deleted_sales = [];
        if (file_exists($deletedSalesFile)) {
            $json_content = file_get_contents($deletedSalesFile);
            $deleted_sales = json_decode($json_content, true);
            if (!is_array($deleted_sales)) {
                $deleted_sales = [];
            }
        }
        
        // Add deletion timestamp
        $order['deleted_at'] = date('Y-m-d H:i:s');
        $order['deleted_by'] = $_SESSION['admin_username'] ?? 'Unknown';
        
        // Move to deleted sales
        $deleted_sales[] = $order;
        
        // Remove from active sales
        $updated_sales = [];
        foreach ($sales as $sale) {
            if ($sale['order_id'] !== $order_id) {
                $updated_sales[] = $sale;
            }
        }
        
        // Save both files
        file_put_contents($deletedSalesFile, json_encode($deleted_sales, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($salesFile, json_encode($updated_sales, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        $_SESSION['success_message'] = 'Order moved to deleted orders successfully!';
        header('Location: sales.php');
        exit;
    }
}

admin_layout_start('Order Details', 'sales');
?>

<div class="dashboard-card md:col-span-3">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold">Order Details - <?php echo htmlspecialchars($order['order_id']); ?></h2>
        <div class="flex gap-2">
            <a href="sales.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Back to Sales
            </a>
            <form method="POST" action="order_detail.php?id=<?php echo htmlspecialchars($order_id); ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this order? It will be moved to deleted orders.');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <button type="submit" class="btn" style="background-color: #ef4444; color: white;">
                    <i class="fas fa-trash mr-2"></i>Delete Order
                </button>
            </form>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Order Information -->
        <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-3">Order Information</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium">Order ID:</span>
                        <span><?php echo htmlspecialchars($order['order_id']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Order Date:</span>
                        <span><?php echo htmlspecialchars($order['order_date']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Total Amount:</span>
                        <span class="font-bold text-green-600">$<?php echo number_format((float)$order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-3">Customer Information</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium">Name:</span>
                        <span><?php echo htmlspecialchars($order['customer_name']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Email:</span>
                        <span><?php echo htmlspecialchars($order['customer_email']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-3">Order Items</h3>
            <div class="space-y-3">
                <?php foreach ($order['items'] as $item): ?>
                    <div class="flex justify-between items-center p-3 bg-white rounded border">
                        <div>
                            <div class="font-medium"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            <div class="text-sm text-gray-500">SKU: <?php echo htmlspecialchars($item['product_slug']); ?></div>
                        </div>
                        <div class="text-right">
                            <div class="font-medium">Qty: <?php echo htmlspecialchars($item['quantity']); ?></div>
                            <div class="text-sm text-gray-500">$<?php echo number_format((float)$item['price'], 2); ?> each</div>
                            <div class="font-bold text-green-600">$<?php echo number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-4 pt-4 border-t">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold">Total:</span>
                    <span class="text-xl font-bold text-green-600">$<?php echo number_format((float)$order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Order Details -->
    <?php if (isset($order['billing_address']) || isset($order['shipping_address']) || isset($order['payment_method'])): ?>
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <?php if (isset($order['billing_address'])): ?>
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-3">Billing Address</h3>
            <div class="text-sm">
                <?php echo nl2br(htmlspecialchars($order['billing_address'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($order['shipping_address'])): ?>
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-3">Shipping Address</h3>
            <div class="text-sm">
                <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($order['payment_method'])): ?>
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-3">Payment Method</h3>
            <div class="text-sm">
                <?php echo htmlspecialchars($order['payment_method']); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div class="dashboard-card md:col-span-3">
    <div class="flex justify-end gap-2">
        <a href="edit_order.php?id=<?php echo htmlspecialchars($order['order_id']); ?>" class="btn btn-primary">
            <i class="fas fa-edit mr-2"></i>Edit Order
        </a>
        <a href="print_order.php?id=<?php echo htmlspecialchars($order['order_id']); ?>" class="btn btn-secondary" target="_blank">
            <i class="fas fa-print mr-2"></i>Print Invoice
        </a>
        <form method="POST" action="delete_order.php" onsubmit="return confirm('Are you sure you want to delete this order? It will be moved to deleted orders.');" style="display: inline;">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt mr-2"></i>Delete Order</button>
        </form>
        <a href="sales.php" class="btn btn-secondary">Back to Sales</a>
    </div>
</div>

<?php admin_layout_end(); ?>
