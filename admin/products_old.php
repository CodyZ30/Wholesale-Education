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
        // Potentially log this for security monitoring
        // Die or redirect to prevent further processing
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
            $new_file_content = "<?php\n\nreturn [\n";
            foreach ($current_products as $slug => $product) {
                $new_file_content .= "  '" . addslashes($slug) . "' => [\n";
                foreach ($product as $key => $value) {
                    $new_file_content .= "    '" . addslashes($key) . "' => ";
                    if (is_array($value)) {
                        $new_file_content .= "[";
                        if (is_int(array_key_first($value))) {
                            foreach ($value as $item) {
                                if (is_array($item)) {
                                    $new_file_content .= "['" . implode("\',\'", array_map('addslashes', $item)) . "'], ";
                                } else {
                                    $new_file_content .= "'" . addslashes((string)$item) . "', ";
                                }
                            }
                        } else {
                            foreach ($value as $k => $v) {
                                $new_file_content .= "'" . addslashes($k) . "' => '". addslashes((string)$v) . "', ";
                            }
                        }
                        $new_file_content = rtrim($new_file_content, ', ') . "],\n";
                    } elseif (is_bool($value)) {
                        $new_file_content .= ($value ? 'true' : 'false') . ',\n';
                    } elseif (is_numeric($value)) {
                        $new_file_content .= $value . ',\n';
                    } else {
                        $new_file_content .= "'" . addslashes((string)$value) . "',\n";
                    }
                }
                $new_file_content .= "  ],\n";
            }
            $new_file_content .= "];\n";

            file_put_contents($products_file_path, $new_file_content);

            $_SESSION['success_message'] = 'Product deleted successfully!';
            header('Location: products.php');
            exit;
        }
    }
}

// Handle adding a new product
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $products_file_path = __DIR__ . '/../data/products.php';
    $current_products = require $products_file_path;

    $new_name = trim($_POST['name'] ?? '');
    $new_slug = trim($_POST['slug'] ?? '');
    $new_price = (float)($_POST['price'] ?? 0.00);
    $new_shipping = (float)($_POST['shipping'] ?? 0.00);
    $new_stock = trim($_POST['stock'] ?? 'In Stock');
    $new_category = trim($_POST['category'] ?? '');
    $new_brand = trim($_POST['brand'] ?? '');
    $new_description = trim($_POST['description'] ?? '');

    // Basic validation
    if (empty($new_name) || empty($new_slug) || $new_price <= 0) {
        $error = 'Name, slug, and price are required and price must be positive.';
    } elseif (isset($current_products[$new_slug])) {
        $error = 'Product slug must be unique.';
    } else {
        $new_product_id = 1;
        if (!empty($current_products)) {
            $ids = array_column($current_products, 'id');
            $new_product_id = max($ids) + 1;
        }

        $image_path = '';
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../images/';
            $file_name = basename($_FILES['image']['name']);
            $target_file = $upload_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Basic image validation
            $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageFileType, $valid_extensions)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = '/images/' . $file_name;
                } else {
                    // Handle upload error
                    error_log("Failed to move uploaded file: " . $_FILES['image']['tmp_name'] . " to " . $target_file);
                    $error = 'Failed to upload image.';
                }
            } else {
                // Handle invalid file type error
                error_log("Invalid file type for image upload: " . $imageFileType);
                $error = 'Invalid image file type. Only JPG, JPEG, PNG, GIF allowed.';
            }
        }

        if (empty($error)) {
            $new_product = [
                'id' => $new_product_id,
                'slug' => $new_slug,
                'name' => $new_name,
                'price' => $new_price,
                'shipping' => $new_shipping,
                'images' => $image_path ? [$image_path] : [], // Store image path if uploaded
                'description' => $new_description,
                'features' => [],
                'specs' => [],
                'category' => $new_category,
                'brand' => $new_brand,
                'stock' => $new_stock,
                'faq' => [],
            ];

            $current_products[$new_slug] = $new_product;

            // Prepare the new content for products.php
            $new_file_content = "<?php\n\nreturn [\n";
            foreach ($current_products as $slug => $product) {
                $new_file_content .= "  '" . addslashes($slug) . "' => [\n";
                foreach ($product as $key => $value) {
                    $new_file_content .= "    '" . addslashes($key) . "' => ";
                    if (is_array($value)) {
                        $new_file_content .= "[";
                        if (is_int(array_key_first($value))) {
                            foreach ($value as $item) {
                                if (is_array($item)) {
                                    $new_file_content .= "['" . implode("\',\'", array_map('addslashes', $item)) . "'], ";
                                } else {
                                    $new_file_content .= "'" . addslashes((string)$item) . "', ";
                                }
                            }
                        } else {
                            foreach ($value as $k => $v) {
                                $new_file_content .= "'" . addslashes($k) . "' => '". addslashes((string)$v) . "', ";
                            }
                        }
                        $new_file_content = rtrim($new_file_content, ', ') . "],\n";
                    } elseif (is_bool($value)) {
                        $new_file_content .= ($value ? 'true' : 'false') . ',\n';
                    } elseif (is_numeric($value)) {
                        $new_file_content .= $value . ',\n';
                    } else {
                        $new_file_content .= "'" . addslashes((string)$value) . "',\n";
                    }
                }
                $new_file_content .= "  ],\n";
            }
            $new_file_content .= "];\n";

            file_put_contents($products_file_path, $new_file_content);

            $_SESSION['success_message'] = 'Product added successfully!';
            header('Location: products.php');
            exit;
        }
    }
}

// Handle updating a product
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $original_slug = trim($_POST['original_slug'] ?? '');
    $new_slug = trim($_POST['slug'] ?? '');
    $updated_name = trim($_POST['name'] ?? '');
    $updated_price = (float)($_POST['price'] ?? 0.00);
    $updated_shipping = (float)($_POST['shipping'] ?? 0.00);
    $updated_stock = trim($_POST['stock'] ?? 'In Stock');
    $updated_category = trim($_POST['category'] ?? '');
    $updated_brand = trim($_POST['brand'] ?? '');
    $updated_description = trim($_POST['description'] ?? '');
    
    if (empty($updated_name) || empty($new_slug) || $updated_price <= 0) {
        $error = 'Name, slug, and price are required and price must be positive.';
    } elseif ($original_slug !== '') {
        $products_file_path = __DIR__ . '/../data/products.php';
        $current_products = require $products_file_path;

        if (isset($current_products[$original_slug])) {
            // Check for unique slug if it changed
            if ($original_slug !== $new_slug && isset($current_products[$new_slug])) {
                $error = 'New product slug must be unique.';
            } else {
                $updated_product = $current_products[$original_slug];

                // Update fields
                $updated_product['name'] = $updated_name;
                $updated_product['price'] = $updated_price;
                $updated_product['description'] = $updated_description;
                $updated_product['category'] = $updated_category;
                $updated_product['brand'] = $updated_brand;
                $updated_product['stock'] = $updated_stock;
                $updated_product['shipping'] = $updated_shipping;

                // Handle image upload for update
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/../images/';
                    $file_name = basename($_FILES['image']['name']);
                    $target_file = $upload_dir . $file_name;
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                    $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    if (in_array($imageFileType, $valid_extensions)) {
                        // Delete old image if it exists and is different from new one
                        if (!empty($updated_product['images'][0]) && file_exists(__DIR__ . '/..' . $updated_product['images'][0]) && $updated_product['images'][0] !== ('/images/' . $file_name)) {
                            unlink(__DIR__ . '/..' . $updated_product['images'][0]);
                            error_log("Deleted old image during update: " . $updated_product['images'][0]);
                        }

                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                            $updated_product['images'] = ['/images/' . $file_name]; // Update with new image path
                        } else {
                            error_log("Failed to move uploaded file during product update: " . $_FILES['image']['tmp_name'] . " to " . $target_file);
                            $error = 'Failed to upload new image.';
                        }
                    } else {
                        error_log("Invalid file type for image upload during product update: " . $imageFileType);
                        $error = 'Invalid image file type. Only JPG, JPEG, PNG, GIF allowed.';
                    }
                }

                if (empty($error)) {
                    // If slug changed, remove old entry and add new one
                    if ($original_slug !== $new_slug) {
                        unset($current_products[$original_slug]);
                        $current_products[$new_slug] = $updated_product;
                        $updated_product['slug'] = $new_slug; // Update slug in the product array
                    } else {
                        $current_products[$original_slug] = $updated_product;
                    }

                    // Prepare the new content for products.php
                    $new_file_content = "<?php\n\nreturn [\n";
                    foreach ($current_products as $slug => $product) {
                        $new_file_content .= "  '" . addslashes($slug) . "' => [\n";
                        foreach ($product as $key => $value) {
                            $new_file_content .= "    '" . addslashes($key) . "' => ";
                            if (is_array($value)) {
                                $new_file_content .= "[";
                                if (is_int(array_key_first($value))) {
                                    foreach ($value as $item) {
                                        if (is_array($item)) {
                                            $new_file_content .= "['" . implode("\',\'", array_map('addslashes', $item)) . "'], ";
                                        } else {
                                            $new_file_content .= "'" . addslashes((string)$item) . "', ";
                                        }
                                    }
                                } else {
                                    foreach ($value as $k => $v) {
                                        $new_file_content .= "'" . addslashes($k) . "' => '". addslashes((string)$v) . "', ";
                                    }
                                }
                                $new_file_content = rtrim($new_file_content, ', ') . "],\n";
                            } elseif (is_bool($value)) {
                                $new_file_content .= ($value ? 'true' : 'false') . ',\n';
                            } elseif (is_numeric($value)) {
                                $new_file_content .= $value . ',\n';
                            } else {
                                $new_file_content .= "'" . addslashes((string)$value) . "',\n";
                            }
                        }
                        $new_file_content .= "  ],\n";
                    }
                    $new_file_content .= "];\n";

                    file_put_contents($products_file_path, $new_file_content);

                    $_SESSION['success_message'] = 'Product updated successfully!';
                    header('Location: products.php');
                    exit;
                }
            }
        }
    }
}

// Load product data from the existing products.php file
$products_data = require __DIR__ . '/../data/products.php';

// Convert the associative array to a simple indexed array for easier display
$products = [];
foreach ($products_data as $slug => $product) {
    // Add the slug to the product data
    $product['slug'] = $slug;
    $products[] = $product;
}

// --- Search/Filter Logic for Products ---
$search_query = trim($_GET['search'] ?? '');
$filtered_products = $products;

if (!empty($search_query)) {
    $filtered_products = array_filter($products, function($product) use ($search_query) {
        return stripos($product['name'], $search_query) !== false ||
               stripos($product['slug'], $search_query) !== false ||
               stripos($product['category'] ?? '', $search_query) !== false ||
               stripos($product['brand'] ?? '', $search_query) !== false;
    });
}

// Re-index after filtering to ensure array_slice works correctly
$filtered_products = array_values($filtered_products);

// --- Pagination Logic for Products (updated to use filtered products) ---
$items_per_page = 12;
$total_products = count($filtered_products);
$total_pages = ceil($total_products / $items_per_page);
$current_page = (int)($_GET['p'] ?? 1);
$current_page = max(1, min($current_page, (int)$total_pages));
$offset = ($current_page - 1) * $items_per_page;
$paginated_products = array_slice($filtered_products, $offset, $items_per_page);

?>
admin_layout_start(__('products'), 'products');
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #0b0d10;
            margin: 0;
        }
        .container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        .table-auto { width: 100%; border-collapse: collapse; }
        .table-auto th, .table-auto td { border: 1px solid #e2e8f0; padding: 0.75rem; text-align: left; }
        .table-auto th { background-color: #f8fafc; font-weight: 600; }
        .action-button { background-color: #3b82f6; color: #fff; padding: 0.4rem 0.8rem; border-radius: 0.25rem; text-decoration: none; font-size: 0.875rem; }
        .action-button.edit { background-color: #22c55e; }
        .action-button.delete { background-color: #ef4444; }
    </style>
</head>
<body>
    <div class="sidebar">
      <div class="sidebar-header">
        <img src="../images/white-logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="sidebar-logo mx-auto">
        <div class="text-sm text-gray-400 mt-2">Employee Portal</div>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li><a class="active" href="products.php"><i class="fas fa-box"></i> Products</a></li>
          <li><a href="guides.php"><i class="fas fa-book"></i> Guides</a></li>
          <li><a href="reviews.php"><i class="fas fa-comments"></i> Reviews</a></li>
          <li><a href="sales.php"><i class="fas fa-chart-line"></i> Sales</a></li>
          <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
          <li><a href="cart_log.php"><i class="fas fa-list"></i> Cart Log</a></li>
          <li><a href="messages.php"><i class="fas fa-inbox"></i> Messages</a></li>
          <li><a href="settings.php"><i class="fas fa-gear"></i> Settings</a></li>
          <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
          <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
      </nav>
    </div>

    <div class="main-content">
      <div class="top-navbar">
        <div class="flex items-center gap-2">
          <img src="../images/white-logo.png" alt="<?php echo SITE_NAME; ?>" style="height:28px;width:auto;border-radius:6px;">
          <span class="text-xl font-semibold">Products</span>
        </div>
      </div>

    <div class="container">
            <h2 class="text-xl font-semibold mb-4">Manage Products</h2>
            <a href="products.php?action=add" class="btn btn-primary mb-4 inline-block">Add New Product</a>
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>

            <!-- Search Form -->
            <form method="GET" action="products.php" class="mb-4 flex gap-2 items-center">
                <input type="text" name="search" placeholder="Search products..." class="input-field flex-grow" value="<?php echo htmlspecialchars((string)$search_query); ?>">
                <button type="submit" class="action-button">Search</button>
                <?php if (!empty($search_query)): ?>
                    <a href="products.php" class="action-button delete">Clear</a>
                <?php endif; ?>
            </form>

            <table class="w-full text-sm mt-4">
                <thead>
                    <tr>
                        <th class="text-left p-2">ID</th>
                        <th class="text-left p-2">Name</th>
                        <th class="text-left p-2">Price</th>
                        <th class="text-left p-2">Stock</th>
                        <th class="text-left p-2">Category</th>
                        <th class="text-left p-2">Brand</th>
                        <th class="text-left p-2">Image</th>
                        <th class="text-left p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginated_products as $product): ?>
                        <tr>
                            <td class="p-2"><?php echo htmlspecialchars((string)$product['id']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars((string)$product['name']); ?></td>
                            <td class="p-2">$<?php echo htmlspecialchars(number_format((float)$product['price'], 2)); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars((string)($product['stock'] ?? 'N/A')); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars((string)($product['category'] ?? 'N/A')); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars((string)($product['brand'] ?? 'N/A')); ?></td>
                            <td class="p-2">
                                <?php if (!empty($product['images'][0])): ?>
                                    <img src="<?php echo htmlspecialchars((string)$product['images'][0]); ?>" alt="Product Image" style="width: 50px; height: auto;">
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td class="p-2">
                                <a href="products.php?action=edit&slug=<?php echo htmlspecialchars((string)$product['slug']); ?>" class="btn btn-primary">Edit</a>
                                <form method="POST" action="products.php" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="slug" value="<?php echo htmlspecialchars((string)$product['slug']); ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$csrf_token); ?>">
                                    <button type="submit" class="btn ml-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($paginated_products)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-gray-400">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
                <div class="flex justify-center items-center space-x-2 mt-4">
                    <?php if ($current_page > 1): ?>
                        <a href="products.php?p=<?php echo htmlspecialchars((string)($current_page - 1)); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="px-3 py-1 border rounded-md hover:bg-gray-200">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="products.php?p=<?php echo htmlspecialchars((string)$i); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="px-3 py-1 border rounded-md <?php echo ($i === $current_page) ? 'bg-blue-500 text-white' : 'hover:bg-gray-200'; ?>">
                            <?php echo htmlspecialchars((string)$i); ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="products.php?p=<?php echo htmlspecialchars((string)($current_page + 1)); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="px-3 py-1 border rounded-md hover:bg-gray-200">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
        <div class="dashboard-card md:col-span-3 mt-6">
            <h2 class="text-xl font-semibold mb-4">Add New Product</h2>
            <form method="POST" action="products.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$csrf_token); ?>">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-semibold mb-2">Product Name:</label>
                    <input type="text" name="name" id="name" class="border rounded w-full px-3 py-2 bg-transparent" required>
                </div>
                <div class="mb-4">
                    <label for="slug" class="block text-sm font-semibold mb-2">Product Slug:</label>
                    <input type="text" name="slug" id="slug" class="border rounded w-full px-3 py-2 bg-transparent" required>
                </div>
                <div class="mb-4">
                    <label for="price" class="block text-sm font-semibold mb-2">Price:</label>
                    <input type="number" name="price" id="price" step="0.01" class="border rounded w-full px-3 py-2 bg-transparent" required>
                </div>
                <div class="mb-4">
                    <label for="shipping" class="block text-sm font-semibold mb-2">Shipping ($):</label>
                    <input type="number" name="shipping" id="shipping" step="0.01" class="border rounded w-full px-3 py-2 bg-transparent" value="0.00">
                </div>
                <div class="mb-4">
                    <label for="stock" class="block text-sm font-semibold mb-2">Stock:</label>
                    <input type="text" name="stock" id="stock" class="border rounded w-full px-3 py-2 bg-transparent" value="In Stock">
                </div>
                <div class="mb-4">
                    <label for="category" class="block text-sm font-semibold mb-2">Category:</label>
                    <input type="text" name="category" id="category" class="border rounded w-full px-3 py-2 bg-transparent">
                </div>
                <div class="mb-4">
                    <label for="brand" class="block text-sm font-semibold mb-2">Brand:</label>
                    <input type="text" name="brand" id="brand" class="border rounded w-full px-3 py-2 bg-transparent">
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-semibold mb-2">Description:</label>
                    <textarea name="description" id="description" class="border rounded w-full px-3 py-2 bg-transparent"></textarea>
                </div>
                <div class="mb-4">
                    <label for="image" class="block text-sm font-semibold mb-2">Product Image:</label>
                    <input type="file" name="image" id="image" class="border rounded w-full px-3 py-2 bg-transparent">
                </div>
                <button type="submit" class="btn btn-primary">Add Product</button>
                <a href="products.php" class="btn ml-2">Cancel</a>
            </form>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['slug'])): ?>
        <?php
            $edit_slug = $_GET['slug'];
            $product_to_edit = $products_data[$edit_slug] ?? null;
            if (!$product_to_edit) {
                // Redirect or show error if product not found
                header('Location: products.php');
                exit;
            }
        ?>
        <div class="dashboard-card md:col-span-3 mt-6">
            <h2 class="text-xl font-semibold mb-4">Edit Product: <?php echo htmlspecialchars((string)$product_to_edit['name']); ?></h2>
            <form method="POST" action="products.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="original_slug" value="<?php echo htmlspecialchars((string)$edit_slug); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$csrf_token); ?>">
                <div class="mb-4">
                    <label for="edit_name" class="block text-sm font-semibold mb-2">Product Name:</label>
                    <input type="text" name="name" id="edit_name" class="border rounded w-full px-3 py-2 bg-transparent" value="<?php echo htmlspecialchars((string)$product_to_edit['name']); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="edit_slug" class="block text-sm font-semibold mb-2">Product Slug:</label>
                    <input type="text" name="slug" id="edit_slug" class="border rounded w-full px-3 py-2 bg-transparent" value="<?php echo htmlspecialchars((string)$product_to_edit['slug']); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="edit_price" class="block text-sm font-semibold mb-2">Price:</label>
                    <input type="number" name="price" id="edit_price" step="0.01" class="border rounded w-full px-3 py-2 bg-transparent" value="<?php echo htmlspecialchars(number_format((float)$product_to_edit['price'], 2, '.', '')); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="edit_shipping" class="block text-sm font-semibold mb-2">Shipping ($):</label>
                    <input type="number" name="shipping" id="edit_shipping" step="0.01" class="border rounded w-full px-3 py-2 bg-transparent" value="<?php echo htmlspecialchars(number_format((float)($product_to_edit['shipping'] ?? 0), 2, '.', '')); ?>">
                </div>
                <div class="mb-4">
                    <label for="edit_stock" class="block text-sm font-semibold mb-2">Stock:</label>
                    <input type="text" name="stock" id="edit_stock" class="border rounded w-full px-3 py-2 bg-transparent" value="<?php echo htmlspecialchars((string)($product_to_edit['stock'] ?? 'In Stock')); ?>">
                </div>
                <div class="mb-4">
                    <label for="edit_category" class="block text-sm font-semibold mb-2">Category:</label>
                    <input type="text" name="category" id="edit_category" class="border rounded w-full px-3 py-2 bg-transparent" value="<?php echo htmlspecialchars((string)($product_to_edit['category'] ?? '')); ?>">
                </div>
                <div class="mb-4">
                    <label for="edit_brand" class="block text-sm font-semibold mb-2">Brand:</label>
                    <input type="text" name="brand" id="edit_brand" class="border rounded w-full px-3 py-2 bg-transparent" value="<?php echo htmlspecialchars((string)($product_to_edit['brand'] ?? '')); ?>">
                </div>
                <div class="mb-4">
                    <label for="edit_description" class="block text-sm font-semibold mb-2">Description:</label>
                    <textarea name="description" id="edit_description" class="border rounded w-full px-3 py-2 bg-transparent"><?php echo htmlspecialchars((string)($product_to_edit['description'] ?? '')); ?></textarea>
                </div>
                <div class="mb-4">
                    <label for="edit_image" class="block text-sm font-semibold mb-2">Product Image (leave blank to keep current):</label>
                    <?php if (!empty($product_to_edit['images'][0])): ?>
                        <img src="<?php echo htmlspecialchars((string)$product_to_edit['images'][0]); ?>" alt="Product Image" class="mb-2" style="max-width: 100px;">
                    <?php endif; ?>
                    <input type="file" name="image" id="edit_image" class="border rounded w-full px-3 py-2 bg-transparent">
                </div>
                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="products.php" class="btn ml-2">Cancel</a>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
