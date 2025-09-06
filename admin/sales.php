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

// Load sales data (needed for both display and creation)
$sales = [];
if (file_exists($salesFile)) {
    $json_content = file_get_contents($salesFile);
    $sales = json_decode($json_content, true);
    if (!is_array($sales)) {
        $sales = [];
    }
}

// Preload items if provided (from Cart Log)
$import_items = [];
if (isset($_GET['items'])) {
    $json = base64_decode((string)$_GET['items'], true);
    if (is_string($json)) {
        $arr = json_decode($json, true);
        if (is_array($arr)) $import_items = $arr;
    }
}

// Handle new sale creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else {
        $customer_name = trim($_POST['customer_name'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $product_name = trim($_POST['product_name'] ?? '');
        $product_price = (float)($_POST['product_price'] ?? 0);
        $product_qty = (int)($_POST['product_qty'] ?? 1);
        $total_amount = $product_price * $product_qty;

        if (empty($customer_name) || empty($customer_email) || empty($product_name) || $product_price <= 0) {
            $error = 'All fields are required and price must be greater than 0.';
        } else {
            // Generate new order ID
            $new_order_id = 'ORD' . str_pad((string)(count($sales) + 1), 3, '0', STR_PAD_LEFT);
            
            $new_sale = [
                'order_id' => $new_order_id,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'items' => [
                    [
                        'product_name' => $product_name,
                        'price' => $product_price,
                        'qty' => $product_qty
                    ]
                ],
                'total_amount' => $total_amount,
                'order_date' => date('Y-m-d H:i:s'),
            ];

            $sales[] = $new_sale;

            file_put_contents($salesFile, json_encode($sales, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $_SESSION['success_message'] = 'Sale added successfully!';
            header('Location: sales.php');
            exit;
        }
    }
}

$total_sales_amount = array_sum(array_column($sales, 'total_amount'));
$total_orders = count($sales);

// Handle messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

admin_layout_start('Sales', 'sales');
?>

<div class="dashboard-card md:col-span-3">
    <h2 class="text-lg font-medium mb-4"><?php echo __('sales'); ?> Overview</h2>
    <div class="flex gap-2 mb-4 flex-wrap">
        <a href="sales.php?action=add" class="btn btn-primary">Add New Sale</a>
        <a href="deleted_orders.php" class="btn btn-secondary">View Deleted Orders</a>
        <a href="search_orders.php" class="btn btn-secondary">Search Orders</a>
        <a href="export_orders.php" class="btn btn-secondary">Export Orders</a>
    </div>
    <?php if ($error): ?>
        <div class="mb-4" style="color:#ef4444; font-weight:600;"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="mb-4" style="color:#1dd171; font-weight:600;">&nbsp;<?php echo $success_message; ?></div>
    <?php endif; ?>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <h3 class="text-lg font-medium"><?php echo __('total_sales'); ?></h3>
        <p class="value">$<?php echo htmlspecialchars(number_format($total_sales_amount, 2)); ?></p>
    </div>
    <div class="dashboard-card">
        <h3 class="text-lg font-medium">Total Orders</h3>
        <p class="value"><?php echo htmlspecialchars((string)$total_orders); ?></p>
    </div>
</div>

<div class="dashboard-card md:col-span-3">
    <h3 class="text-xl font-semibold mb-4">Recent Orders</h3>
    <div class="rounded-lg border overflow-auto">
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="text-left p-2">Order ID</th>
                    <th class="text-left p-2">Customer</th>
                    <th class="text-left p-2">Total</th>
                    <th class="text-left p-2">Date</th>
                    <th class="text-left p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr class="border-t">
                        <td class="p-2">
                            <a href="order_detail.php?id=<?php echo htmlspecialchars($sale['order_id']); ?>" class="text-blue-400 hover:text-blue-300 font-medium">
                                <?php echo htmlspecialchars((string)$sale['order_id']); ?>
                            </a>
                        </td>
                        <td class="p-2"><?php echo htmlspecialchars((string)$sale['customer_name']); ?></td>
                        <td class="p-2">$<?php echo htmlspecialchars(number_format((float)$sale['total_amount'], 2)); ?></td>
                        <td class="p-2"><?php echo htmlspecialchars((string)$sale['order_date']); ?></td>
                        <td class="p-2">
                            <div class="flex gap-2">
                                <a href="order_detail.php?id=<?php echo htmlspecialchars($sale['order_id']); ?>" class="btn btn-primary">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                                <form method="POST" action="delete_order.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this order? It will be moved to deleted orders.');">
                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($sale['order_id']); ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                    <button type="submit" class="btn" style="background-color: #ef4444; color: white;">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($sales)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-400">No sales data available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
<div class="dashboard-card md:col-span-3">
    <h2 class="text-xl font-semibold mb-4">Add New Sale</h2>
    <form method="POST" action="sales.php">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
                <label for="customer_name" class="block text-sm font-semibold mb-2">Customer Name:</label>
                <input type="text" name="customer_name" id="customer_name" class="border rounded w-full px-3 py-2 bg-transparent" required>
            </div>
            <div class="form-group">
                <label for="customer_email" class="block text-sm font-semibold mb-2">Customer Email:</label>
                <input type="email" name="customer_email" id="customer_email" class="border rounded w-full px-3 py-2 bg-transparent" required>
            </div>
            <div class="form-group">
                <label for="product_name" class="block text-sm font-semibold mb-2">Product Name:</label>
                <input type="text" name="product_name" id="product_name" class="border rounded w-full px-3 py-2 bg-transparent" required>
            </div>
            <div class="form-group">
                <label for="product_price" class="block text-sm font-semibold mb-2">Product Price:</label>
                <input type="number" step="0.01" name="product_price" id="product_price" class="border rounded w-full px-3 py-2 bg-transparent" required>
            </div>
            <div class="form-group">
                <label for="product_qty" class="block text-sm font-semibold mb-2">Quantity:</label>
                <input type="number" name="product_qty" id="product_qty" value="1" min="1" class="border rounded w-full px-3 py-2 bg-transparent" required>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Add Sale</button>
            <a href="sales.php" class="btn ml-2">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php admin_layout_end(); ?>
