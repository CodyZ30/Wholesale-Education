<?php
// Test the image fetching endpoints
echo "<h1>Testing Product Image Endpoints</h1>";

echo "<h2>1. Testing single product image:</h2>";
echo "<p>URL: /get_product_image.php?slug=the-keeper-gauge</p>";
$url = 'http://' . $_SERVER['HTTP_HOST'] . '/get_product_image.php?slug=the-keeper-gauge';
$response = file_get_contents($url);
echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<h2>2. Testing batch product images:</h2>";
echo "<p>URL: /get_product_images.php (POST with slugs)</p>";

// Test POST request
$data = json_encode(['slugs' => ['the-keeper-gauge', 'the-bucket-station']]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data
    ]
]);
$response = file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/get_product_images.php', false, $context);
echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<h2>3. Testing direct product data:</h2>";
$products = require __DIR__ . '/data/products.php';
$keeper = $products['the-keeper-gauge'] ?? null;
if ($keeper) {
    echo "<pre>" . htmlspecialchars(json_encode($keeper, JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "<p>Product not found in data/products.php</p>";
}

echo "<h2>4. Testing image file exists:</h2>";
$image_path = __DIR__ . '/images/gottafish-product-01.png';
if (file_exists($image_path)) {
    echo "<p>✅ Image file exists: $image_path</p>";
    echo "<p>File size: " . filesize($image_path) . " bytes</p>";
    echo "<img src='/images/gottafish-product-01.png' alt='Keeper Gauge' style='max-width: 200px;'>";
} else {
    echo "<p>❌ Image file NOT found: $image_path</p>";
}
?>
