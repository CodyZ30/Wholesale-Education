<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/layout.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Check if the admin is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$error = '';
$success_message = '';

// Validate CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
        exit('Invalid CSRF token.');
    }
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $slug_to_delete = $_POST['slug'] ?? '';
    if ($slug_to_delete !== '') {
        $products_file_path = __DIR__ . '/../data/products.php';
        $current_products = require $products_file_path;

        if (isset($current_products[$slug_to_delete])) {
            // Get image path before unsetting the product
            $image_to_delete = $current_products[$slug_to_delete]['images'][0] ?? null;

            unset($current_products[$slug_to_delete]);

            // Delete the image file if it exists
            if ($image_to_delete && file_exists(__DIR__ . '/..' . $image_to_delete)) {
                unlink(__DIR__ . '/..' . $image_to_delete);
                error_log("Deleted image: " . $image_to_delete);
            }

            // Prepare the new content for products.php
            $new_content = "<?php\n\nreturn [\n";
            foreach ($current_products as $slug => $product) {
                $new_content .= "  '" . addslashes($slug) . "' => [\n";
                $new_content .= "    'id' => " . (int)$product['id'] . ",\n";
                $new_content .= "    'slug' => '" . addslashes($product['slug']) . "',\n";
                $new_content .= "    'name' => '" . addslashes($product['name']) . "',\n";
                $new_content .= "    'price' => " . (float)$product['price'] . ",\n";
                $new_content .= "    'images' => [\n";
                foreach ($product['images'] as $image) {
                    $new_content .= "      '" . addslashes($image) . "',\n";
                }
                $new_content .= "    ],\n";
                $new_content .= "    'description' => '" . addslashes($product['description']) . "',\n";
                $new_content .= "    'category' => '" . addslashes($product['category'] ?? '') . "',\n";
                $new_content .= "    'brand' => '" . addslashes($product['brand'] ?? '') . "',\n";
                $new_content .= "    'stock' => " . (int)($product['stock'] ?? 0) . ",\n";
                $new_content .= "  ],\n";
            }
            $new_content .= "];\n";

            // Write the new content to the file
            if (file_put_contents($products_file_path, $new_content)) {
                $success_message = 'Product deleted successfully.';
            } else {
                $error = 'Failed to delete product.';
            }
        } else {
            $error = 'Product not found.';
        }
    }
}

// Load products
$products_file_path = __DIR__ . '/../data/products.php';
$products = [];
if (file_exists($products_file_path)) {
    $products = require $products_file_path;
}

// Get unique categories
$categories = [];
foreach ($products as $product) {
    if (!empty($product['category'])) {
        $categories[] = $product['category'];
    }
}
$categories = array_unique($categories);
sort($categories);

// Handle search and filtering
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

$filtered_products = $products;
if (!empty($search)) {
    $filtered_products = array_filter($filtered_products, function($product) use ($search) {
        return stripos($product['name'], $search) !== false || 
               stripos($product['description'], $search) !== false;
    });
}

if (!empty($category_filter)) {
    $filtered_products = array_filter($filtered_products, function($product) use ($category_filter) {
        return $product['category'] === $category_filter;
    });
}

// Pagination
$items_per_page = 10;
$total_products = count($filtered_products);
$total_pages = ceil($total_products / $items_per_page);
$current_page = (int)($_GET['p'] ?? 1);
$current_page = max(1, min($current_page, (int)$total_pages));
$offset = ($current_page - 1) * $items_per_page;
$paginated_products = array_slice($filtered_products, $offset, $items_per_page);

admin_layout_start(__('products'), 'products');
?>

<div class="col-span-12">
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <span><?php echo __('products'); ?></span>
            <a href="products.php?action=add" class="btn btn-primary"><?php echo __('add_new'); ?></a>
        </div>
        <div class="space-y-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="form-label"><?php echo __('search'); ?>:</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-input" placeholder="<?php echo __('search_products'); ?>">
                </div>
                <div class="w-48">
                    <label class="form-label"><?php echo __('category'); ?>:</label>
                    <select id="category" name="category" class="form-input">
                        <option value=""><?php echo __('all_categories'); ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category_filter === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" class="btn" onclick="applyFilters()"><?php echo __('apply_filters'); ?></button>
                <a href="products.php" class="btn btn-secondary"><?php echo __('clear'); ?></a>
            </div>
            
            <div class="text-sm text-gray-400">
                <?php echo count($filtered_products); ?> <?php echo __('products_found'); ?>
            </div>
            
            <?php if (!empty($paginated_products)): ?>
                <div class="overflow-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo __('image'); ?></th>
                                <th><?php echo __('name'); ?></th>
                                <th><?php echo __('price'); ?></th>
                                <th><?php echo __('category'); ?></th>
                                <th><?php echo __('brand'); ?></th>
                                <th><?php echo __('stock'); ?></th>
                                <th><?php echo __('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paginated_products as $slug => $product): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['images'][0])): ?>
                                            <img src="<?php echo htmlspecialchars($product['images'][0]); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-12 h-12 object-cover rounded">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-600 rounded flex items-center justify-center text-gray-400 text-xs">No Image</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($product['brand'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($product['stock'] ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="products.php?action=edit&slug=<?php echo urlencode($slug); ?>" class="btn btn-primary text-sm"><?php echo __('edit'); ?></a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('<?php echo __('confirm_delete_product'); ?>');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="slug" value="<?php echo htmlspecialchars($slug); ?>">
                                                <button type="submit" class="btn btn-danger text-sm"><?php echo __('delete'); ?></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                    <div class="flex justify-center gap-2">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>" class="btn btn-secondary"><?php echo __('previous'); ?></a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $current_page): ?>
                                <span class="btn bg-blue-600 text-white"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>" class="btn btn-secondary"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>" class="btn btn-secondary"><?php echo __('next'); ?></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-gray-400"><?php echo __('no_products_found'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('search').value;
    const category = document.getElementById('category').value;
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (category) params.append('category', category);
    window.location.href = 'products.php?' + params.toString();
}
</script>

<?php admin_layout_end();
