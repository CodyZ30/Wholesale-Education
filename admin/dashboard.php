<?php
session_start();
include_once __DIR__ . '/../includes/config.php'; // Include global site configuration (admin -> includes)
include_once __DIR__ . '/layout.php';

// Check if the admin is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Path to data files
$salesFile = __DIR__ . '/../sales.json';
$productsFile = __DIR__ . '/../data/products.php';
$reviewsFile = __DIR__ . '/../reviews.json';
$customersFile = __DIR__ . '/../data/customers.json';
$trafficFile = __DIR__ . '/../data/traffic.json';

// --- Load Sales Data ---
$total_sales_amount = 0.0;
$total_orders = 0;
$sales = [];
if (file_exists($salesFile)) {
    $json_content = file_get_contents($salesFile);
    $sales = json_decode($json_content, true);
    if (is_array($sales)) {
        $total_orders = count($sales);
        foreach ($sales as $order) {
            $total_sales_amount += (float)($order['total_amount'] ?? 0);
        }
    }
}

// --- Load Products Data ---
$products_in_stock = 0;
$products = [];
if (file_exists($productsFile)) {
    $products = include $productsFile;
    if (!is_array($products)) {
        $products = [];
    }
    foreach ($products as $product) {
        if (($product['stock'] ?? 0) > 0) {
            $products_in_stock++;
        }
    }
}

// --- Load Reviews Data ---
$total_reviews = 0;
$allReviews = [];
if (file_exists($reviewsFile)) {
    $json_content = file_get_contents($reviewsFile);
    $allReviews = json_decode($json_content, true);
    if (is_array($allReviews)) {
        foreach ($allReviews as $product_slug => $product_reviews) {
            $total_reviews += count($product_reviews); // Sum reviews for all products
        }
    }
}

// --- Load Customers Data ---
$total_customers = 0;
if (file_exists($customersFile)) {
    $json_content = file_get_contents($customersFile);
    $customers = json_decode($json_content, true);
    if (is_array($customers)) {
        $total_customers = count($customers);
    }
}

// --- Load Traffic Data ---
$total_visits = 0;
$unique_sources = [];
$traffic_by_source = [];
$traffic_by_medium = [];
$recent_visits = [];

$traffic = []; // Initialize traffic array
if (file_exists($trafficFile)) {
    $json_content = file_get_contents($trafficFile);
    $traffic = json_decode($json_content, true);
    if (!is_array($traffic)) {
        $traffic = [];
    }
}

if (is_array($traffic) && !empty($traffic)) {
    $total_visits = count($traffic);
    
    // Get last 7 days of visits
    $seven_days_ago = date('Y-m-d', strtotime('-7 days'));
    $recent_visits = array_filter($traffic, function($visit) use ($seven_days_ago) {
        $visit_date = date('Y-m-d', strtotime($visit['ts'] ?? ''));
        return $visit_date >= $seven_days_ago;
    });
    
    // Group by source and medium
    foreach ($traffic as $visit) {
        $source = $visit['utm']['source'] ?? 'direct';
        $medium = $visit['utm']['medium'] ?? 'direct';
        
        if (!in_array($source, $unique_sources)) {
            $unique_sources[] = $source;
        }
        
        $traffic_by_source[$source] = ($traffic_by_source[$source] ?? 0) + 1;
        $traffic_by_medium[$medium] = ($traffic_by_medium[$medium] ?? 0) + 1;
    }
}

admin_layout_start('Dashboard', 'dashboard');
?>

<!-- Welcome Section with User Profile -->
<div class="dashboard-card md:col-span-3 mb-6">
    <div class="flex items-center gap-4">
        <?php
        // Get current user's profile picture and info
        $current_username = $_SESSION['admin_username'] ?? '';
        $current_user_picture = '';
        $current_user_role = '';
        
        if (!empty($current_username)) {
            $users_file = __DIR__ . '/../users.json';
            if (file_exists($users_file)) {
                $users_data = json_decode(file_get_contents($users_file), true);
                if (is_array($users_data)) {
                    foreach ($users_data as $user) {
                        if (($user['username'] ?? '') === $current_username) {
                            $current_user_picture = $user['profile_picture'] ?? '';
                            $current_user_role = $user['role'] ?? '';
                            break;
                        }
                    }
                }
            }
        }
        ?>
        <div class="flex-shrink-0">
            <?php if (!empty($current_user_picture)): ?>
                <img src="../uploads/profiles/<?php echo htmlspecialchars($current_user_picture); ?>" alt="Profile Picture" class="w-16 h-16 rounded-full object-cover border-2 border-gray-300">
            <?php else: ?>
                <div class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-2xl font-semibold">
                    <?php echo strtoupper(substr($current_username ?: 'A', 0, 1)); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="flex-grow">
            <h2 class="text-2xl font-bold text-white"><?php echo __('welcome_back'); ?>, <?php echo htmlspecialchars($current_username ?: 'Admin'); ?>!</h2>
            <p class="text-gray-400 capitalize"><?php echo htmlspecialchars($current_user_role ?: __('administrator')); ?></p>
            <p class="text-sm text-gray-500 mt-1"><?php echo date('l, F j, Y'); ?></p>
        </div>
        <div class="flex-shrink-0">
            <a href="users.php?action=edit&id=<?php echo htmlspecialchars((string)($_SESSION['admin_user_id'] ?? '1')); ?>" class="btn btn-secondary">
                <i class="fas fa-user-edit mr-2"></i><?php echo __('edit_profile'); ?>
            </a>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <h3 class="text-lg font-medium"><?php echo __('total_sales'); ?></h3>
    <p class="value">$<?php echo number_format($total_sales_amount, 2); ?></p>
</div>
<div class="dashboard-card">
    <h3 class="text-lg font-medium"><?php echo __('products_in_stock'); ?></h3>
    <p class="value"><?php echo $products_in_stock; ?></p>
</div>
<div class="dashboard-card">
    <h3 class="text-lg font-medium"><?php echo __('total_reviews'); ?></h3>
    <p class="value"><?php echo $total_reviews; ?></p>
</div>
<div class="dashboard-card">
    <h3 class="text-lg font-medium"><?php echo __('customers'); ?></h3>
    <p class="value"><?php echo $total_customers; ?></p>
</div>
<div class="dashboard-card">
    <h3 class="text-lg font-medium"><?php echo __('total_visits'); ?></h3>
    <p class="value"><?php echo $total_visits; ?></p>
</div>
<div class="dashboard-card">
    <h3 class="text-lg font-medium"><?php echo __('last_7_days'); ?></h3>
    <p class="value"><?php echo count($recent_visits); ?></p>
</div>
</div>

<!-- Traffic Analytics Section -->
<div class="dashboard-grid">
    <div class="dashboard-card md:col-span-2">
        <h3 class="text-lg font-medium"><?php echo __('top_traffic_sources'); ?></h3>
        <div class="space-y-2 mt-4">
            <?php 
            arsort($traffic_by_source);
            $top_sources = array_slice($traffic_by_source, 0, 5, true);
            foreach ($top_sources as $source => $count): 
                $percentage = $total_visits > 0 ? round(($count / $total_visits) * 100, 1) : 0;
            ?>
            <div class="flex justify-between items-center">
                <span class="text-sm"><?php echo htmlspecialchars($source ?: 'Direct'); ?></span>
                <div class="flex items-center gap-2">
                    <div class="w-20 bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                    <span class="text-sm font-medium"><?php echo $count; ?> (<?php echo $percentage; ?>%)</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="dashboard-card">
        <h3 class="text-lg font-medium"><?php echo __('traffic_by_medium'); ?></h3>
        <div class="space-y-2 mt-4">
            <?php 
            arsort($traffic_by_medium);
            $top_mediums = array_slice($traffic_by_medium, 0, 4, true);
            foreach ($top_mediums as $medium => $count): 
                $percentage = $total_visits > 0 ? round(($count / $total_visits) * 100, 1) : 0;
            ?>
            <div class="flex justify-between items-center">
                <span class="text-sm"><?php echo htmlspecialchars($medium ?: 'Direct'); ?></span>
                <span class="text-sm font-medium"><?php echo $count; ?> (<?php echo $percentage; ?>%)</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="dashboard-grid">
    <div class="dashboard-card md:col-span-3">
        <h3 class="text-lg font-medium"><?php echo __('recent_traffic_activity'); ?></h3>
        <div class="mt-4 space-y-2 max-h-64 overflow-y-auto">
            <?php 
            if (!empty($traffic)) {
                $recent_traffic = array_slice(array_reverse($traffic), 0, 10);
                foreach ($recent_traffic as $visit): 
                    $source = $visit['utm']['source'] ?? 'Direct';
                    $medium = $visit['utm']['medium'] ?? 'direct';
                    $campaign = $visit['utm']['campaign'] ?? '';
                    $time = date('M j, Y g:i A', strtotime($visit['ts'] ?? ''));
                ?>
                <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                    <div>
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($source); ?></span>
                        <?php if ($campaign): ?>
                        <span class="text-xs text-gray-500">(<?php echo htmlspecialchars($campaign); ?>)</span>
                        <?php endif; ?>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($visit['path'] ?? '/'); ?></div>
                    </div>
                    <div class="text-xs text-gray-500"><?php echo $time; ?></div>
                </div>
                <?php endforeach; 
            } else { ?>
                <div class="text-center py-8 text-gray-400">
                    <p><?php echo __('no_traffic_data'); ?></p>
                    <p class="text-sm mt-2">Traffic will appear here as visitors arrive.</p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php admin_layout_end(); ?>
