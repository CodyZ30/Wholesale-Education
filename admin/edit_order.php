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
$order_index = -1;
foreach ($sales as $index => $sale) {
    if ($sale['order_id'] === $order_id) {
        $order = $sale;
        $order_index = $index;
        break;
    }
}

if (!$order) {
    header('Location: sales.php');
    exit;
}

// Handle order update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else {
        $customer_name = trim($_POST['customer_name'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $total_amount = (float)($_POST['total_amount'] ?? 0);
        
        if (empty($customer_name) || empty($customer_email) || $total_amount <= 0) {
            $error = 'All fields are required and total amount must be greater than 0.';
        } else {
            // Update the order
            $sales[$order_index]['customer_name'] = $customer_name;
            $sales[$order_index]['customer_email'] = $customer_email;
            $sales[$order_index]['total_amount'] = $total_amount;
            $sales[$order_index]['updated_at'] = date('Y-m-d H:i:s');
            $sales[$order_index]['updated_by'] = $_SESSION['admin_username'] ?? 'Unknown';
            
            // Save the updated sales
            file_put_contents($salesFile, json_encode($sales, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            
            $_SESSION['success_message'] = 'Order updated successfully!';
            header('Location: order_detail.php?id=' . urlencode($order_id));
            exit;
        }
    }
}

admin_layout_start('Edit Order', 'sales');
?>

<div class="dashboard-card md:col-span-3">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold">Edit Order - <?php echo htmlspecialchars($order['order_id']); ?></h2>
        <div class="flex gap-2">
            <a href="order_detail.php?id=<?php echo htmlspecialchars($order_id); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Back to Order
            </a>
            <a href="sales.php" class="btn btn-secondary">
                <i class="fas fa-list mr-2"></i>All Orders
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit_order.php?id=<?php echo htmlspecialchars($order_id); ?>">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        
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
                        <?php if (isset($order['updated_at'])): ?>
                        <div class="flex justify-between">
                            <span class="font-medium">Last Updated:</span>
                            <span><?php echo htmlspecialchars($order['updated_at']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-3">Customer Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-semibold mb-2">Customer Name:</label>
                            <input type="text" name="customer_name" id="customer_name" 
                                   value="<?php echo htmlspecialchars($order['customer_name']); ?>" 
                                   class="border rounded w-full px-3 py-2 bg-transparent" required>
                        </div>
                        <div>
                            <label for="customer_email" class="block text-sm font-semibold mb-2">Customer Email:</label>
                            <input type="email" name="customer_email" id="customer_email" 
                                   value="<?php echo htmlspecialchars($order['customer_email']); ?>" 
                                   class="border rounded w-full px-3 py-2 bg-transparent" required>
                        </div>
                        <div>
                            <label for="total_amount" class="block text-sm font-semibold mb-2">Total Amount:</label>
                            <input type="number" step="0.01" name="total_amount" id="total_amount" 
                                   value="<?php echo htmlspecialchars($order['total_amount']); ?>" 
                                   class="border rounded w-full px-3 py-2 bg-transparent" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-3">Order Items</h3>
                <div class="space-y-3">
                    <?php foreach ($order['items'] as $index => $item): ?>
                        <div class="flex justify-between items-center p-3 bg-white rounded border">
                            <div>
                                <div class="font-medium"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <?php if (isset($item['product_slug'])): ?>
                                <div class="text-sm text-gray-500">SKU: <?php echo htmlspecialchars($item['product_slug']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">Qty: <?php echo htmlspecialchars($item['qty'] ?? $item['quantity']); ?></div>
                                <div class="text-sm text-gray-500">$<?php echo number_format((float)$item['price'], 2); ?> each</div>
                                <div class="font-bold text-green-600">
                                    $<?php echo number_format((float)$item['price'] * (int)($item['qty'] ?? $item['quantity']), 2); ?>
                                </div>
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

        <div class="mt-6 flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>Update Order
            </button>
            <a href="order_detail.php?id=<?php echo htmlspecialchars($order_id); ?>" class="btn btn-secondary">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>

<?php admin_layout_end(); ?>
