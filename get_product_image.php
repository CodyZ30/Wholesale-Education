<?php
header('Content-Type: application/json');

// Get product slug from query parameter
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    echo json_encode(['error' => 'Product slug required']);
    exit;
}

// Load products data
$products_file = __DIR__ . '/data/products.php';
if (!file_exists($products_file)) {
    echo json_encode(['error' => 'Products file not found']);
    exit;
}

$products = require $products_file;

// Find the product by slug
$product = null;
foreach ($products as $product_data) {
    if ($product_data['slug'] === $slug) {
        $product = $product_data;
        break;
    }
}

if (!$product) {
    echo json_encode(['error' => 'Product not found']);
    exit;
}

// Return the first image or placeholder
$image = '/images/placeholder.png';
if (!empty($product['images']) && is_array($product['images'])) {
    $image = $product['images'][0];
}

echo json_encode([
    'slug' => $product['slug'],
    'name' => $product['name'],
    'image' => $image,
    'price' => $product['price']
]);
?>
