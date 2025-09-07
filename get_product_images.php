<?php
header('Content-Type: application/json');

// Get product slugs from POST body or query parameter
$slugs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $slugs = $input['slugs'] ?? [];
} else {
    $slugs_param = $_GET['slugs'] ?? '';
    if (!empty($slugs_param)) {
        $slugs = explode(',', $slugs_param);
    }
}

if (empty($slugs)) {
    echo json_encode(['error' => 'Product slugs required']);
    exit;
}

// Load products data
$products_file = __DIR__ . '/data/products.php';
if (!file_exists($products_file)) {
    echo json_encode(['error' => 'Products file not found']);
    exit;
}

$products = require $products_file;
$result = [];

foreach ($slugs as $slug) {
    $slug = trim($slug);
    if (empty($slug)) continue;
    
    // Find the product by slug
    $product = null;
    foreach ($products as $product_data) {
        if ($product_data['slug'] === $slug) {
            $product = $product_data;
            break;
        }
    }
    
    if ($product) {
        // Get the first image or placeholder
        $image = '/images/placeholder.png';
        if (!empty($product['images']) && is_array($product['images'])) {
            $image = $product['images'][0];
        }
        
        $result[$slug] = [
            'slug' => $product['slug'],
            'name' => $product['name'],
            'image' => $image,
            'price' => $product['price']
        ];
    } else {
        $result[$slug] = [
            'slug' => $slug,
            'name' => 'Unknown Product',
            'image' => '/images/placeholder.png',
            'price' => 0
        ];
    }
}

echo json_encode($result);
?>
