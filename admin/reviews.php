<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/layout.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$reviewsFile = __DIR__ . '/../reviews.json';

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

    // Handle review creation
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $product_slug = trim($_POST['product_slug'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $rating = (int)($_POST['rating'] ?? 0);
        $text = trim($_POST['text'] ?? '');

        // Basic validation
        if (empty($product_slug) || empty($author) || $rating < 1 || $rating > 5 || empty($text)) {
            $error = 'Please fill in all fields (Product Slug, Author, Rating 1-5, and Comment).';
        } else {
            // Load existing reviews
            $allReviews = [];
            if (file_exists($reviewsFile)) {
                $json_content = file_get_contents($reviewsFile);
                $allReviews = json_decode($json_content, true);
                if (!is_array($allReviews)) { $allReviews = []; }
            }

            // Generate a unique ID for the new review
            // Find the highest existing ID across all products and increment
            $max_id = 0;
            foreach ($allReviews as $prod_revs) {
                foreach ($prod_revs as $rev) {
                    if (($rev['id'] ?? 0) > $max_id) {
                        $max_id = $rev['id'];
                    }
                }
            }
            $new_review_id = $max_id + 1;

            $new_review = [
                'id'     => $new_review_id,
                'author' => $author,
                'rating' => $rating,
                'text'   => $text,
                'date'   => date('Y-m-d H:i:s'),
            ];

            // Add the new review under the correct product slug
            if (!isset($allReviews[$product_slug])) {
                $allReviews[$product_slug] = [];
            }
            $allReviews[$product_slug][] = $new_review;

            // Save the updated reviews back to the JSON file
            file_put_contents($reviewsFile, json_encode($allReviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $_SESSION['success_message'] = 'Review added successfully!';
            header('Location: reviews.php');
            exit;
        }
    }

    // Handle review deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
        $product_slug_to_delete_from = trim($_POST['product_slug'] ?? '');
        $review_id_to_delete = (int)($_POST['review_id'] ?? 0);

        if (empty($product_slug_to_delete_from) || $review_id_to_delete <= 0) {
            $error = 'Invalid review or product slug provided for deletion.';
        } else {
            $allReviews = [];
            if (file_exists($reviewsFile)) {
                $json_content = file_get_contents($reviewsFile);
                $allReviews = json_decode($json_content, true);
                if (!is_array($allReviews)) { $allReviews = []; }
            }

            if (isset($allReviews[$product_slug_to_delete_from])) {
                $reviews_for_product = $allReviews[$product_slug_to_delete_from];
                $updated_reviews_for_product = [];
                $found_and_deleted = false;
                foreach ($reviews_for_product as $review) {
                    if (($review['id'] ?? 0) !== $review_id_to_delete) {
                        $updated_reviews_for_product[] = $review;
                    } else {
                        $found_and_deleted = true;
                    }
                }
                $allReviews[$product_slug_to_delete_from] = $updated_reviews_for_product;

                if ($found_and_deleted) {
                    file_put_contents($reviewsFile, json_encode($allReviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    $_SESSION['success_message'] = 'Review deleted successfully!';
                } else {
                    $error = 'Review not found.';
                }

                header('Location: reviews.php');
                exit;
            } else {
                $error = 'Product slug not found in reviews.';
            }
        }
    }
}

// Load reviews
$allReviews = [];
if (file_exists($reviewsFile)) {
    $json_content = file_get_contents($reviewsFile);
    $allReviews = json_decode($json_content, true);
    if (!is_array($allReviews)) {
        $allReviews = [];
    }
}

// Flatten the reviews for display in a single table
$display_reviews = [];
foreach ($allReviews as $product_slug => $product_reviews) {
    foreach ($product_reviews as $review) {
        $review['product_slug'] = $product_slug; // Add product slug for reference
        $display_reviews[] = $review;
    }
}

// --- Search/Filter Logic for Reviews ---
$search_query = trim($_GET['search'] ?? '');
$filtered_reviews = $display_reviews;

if (!empty($search_query)) {
    $filtered_reviews = array_filter($display_reviews, function($review) use ($search_query) {
        return stripos($review['author'], $search_query) !== false ||
               stripos($review['product_slug'], $search_query) !== false ||
               stripos($review['text'], $search_query) !== false;
    });
}

// Re-index after filtering to ensure array_slice works correctly
$filtered_reviews = array_values($filtered_reviews);

// --- Pagination Logic for Reviews ---
$items_per_page = 5;
$total_reviews = count($filtered_reviews);
$total_pages = ceil($total_reviews / $items_per_page);
$current_page = (int)($_GET['p'] ?? 1);
$current_page = max(1, min($current_page, (int)$total_pages));
$offset = ($current_page - 1) * $items_per_page;
$paginated_reviews = array_slice($filtered_reviews, $offset, $items_per_page);

// Handle messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="admin_styles.css">
    <script src="/admin/ui.js" defer></script>
    <style>
        .container { padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .card { background-color: #ffffff; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
        .table-auto { width: 100%; border-collapse: collapse; }
        .table-auto th, .table-auto td { border: 1px solid #e2e8f0; padding: 0.75rem; text-align: left; }
        .table-auto th { background-color: #f8fafc; font-weight: 600; }
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
        <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
        <li><a class="active" href="reviews.php"><i class="fas fa-comments"></i> Reviews</a></li>
        <li><a href="guides.php"><i class="fas fa-book"></i> Guides</a></li>
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
        <span class="text-xl font-semibold">Reviews</span>
      </div>
    </div>

    <div class="container">
            <h2 class="text-xl font-semibold mb-4">Manage Reviews</h2>
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
            <form method="GET" action="reviews.php" class="mb-4 flex gap-2 items-center">
                <input type="text" name="search" placeholder="Search reviews..." class="input-field flex-grow" value="<?php echo htmlspecialchars((string)$search_query); ?>">
                <button type="submit" class="action-button">Search</button>
                <?php if (!empty($search_query)): ?>
                    <a href="reviews.php" class="action-button delete">Clear</a>
                <?php endif; ?>
            </form>

            <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
                <h3 class="text-xl font-semibold mb-4">Add New Review</h3>
                <form method="POST" action="reviews.php" class="bg-white p-6 rounded-md shadow-md mb-6">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$csrf_token); ?>">

                    <div class="mb-4">
                        <label for="product_slug" class="block text-gray-700 text-sm font-bold mb-2">Product Slug:</label>
                        <input type="text" id="product_slug" name="product_slug" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-4">
                        <label for="author" class="block text-gray-700 text-sm font-bold mb-2">Author:</label>
                        <input type="text" id="author" name="author" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-4">
                        <label for="rating" class="block text-gray-700 text-sm font-bold mb-2">Rating (1-5):</label>
                        <input type="number" id="rating" name="rating" min="1" max="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-6">
                        <label for="text" class="block text-gray-700 text-sm font-bold mb-2">Comment:</label>
                        <textarea id="text" name="text" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add Review</button>
                    <a href="reviews.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline ml-2">Cancel</a>
                </form>
                <hr class="my-6">
            <?php endif; ?>

            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">All Reviews</h2>
                <a href="reviews.php?action=add" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Add New Review</a>
            </div>

            <table class="table-auto mt-4">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Slug</th>
                        <th>Author</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginated_reviews as $review): ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string)($review['id'] ?? 'N/A')); ?></td>
                            <td><?php echo htmlspecialchars((string)($review['product_slug'] ?? 'N/A')); ?></td>
                            <td><?php echo htmlspecialchars((string)$review['author']); ?></td>
                            <td><?php echo htmlspecialchars((string)$review['rating']); ?></td>
                            <td><?php echo htmlspecialchars((string)$review['text']); ?></td>
                            <td><?php echo htmlspecialchars((string)$review['date']); ?></td>
                            <td>
                                <form method="POST" action="reviews.php" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="product_slug" value="<?php echo htmlspecialchars((string)$review['product_slug']); ?>">
                                    <input type="hidden" name="review_id" value="<?php echo htmlspecialchars((string)$review['id']); ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$csrf_token); ?>">
                                    <button type="submit" class="action-button delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($paginated_reviews)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-gray-500">No reviews yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
                <div class="flex justify-center items-center space-x-2 mt-4">
                    <?php if ($current_page > 1): ?>
                        <a href="reviews.php?p=<?php echo htmlspecialchars((string)($current_page - 1)); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="px-3 py-1 border rounded-md hover:bg-gray-200">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="reviews.php?p=<?php echo htmlspecialchars((string)$i); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="px-3 py-1 border rounded-md <?php echo ($i === $current_page) ? 'bg-blue-500 text-white' : 'hover:bg-gray-200'; ?>">
                            <?php echo htmlspecialchars((string)$i); ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="reviews.php?p=<?php echo htmlspecialchars((string)($current_page + 1)); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="px-3 py-1 border rounded-md hover:bg-gray-200">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
