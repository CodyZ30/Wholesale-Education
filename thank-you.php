<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/includes/config.php';

// --- Full product data ---
$products = [
  "the-keeper-gauge" => [
    "id" => 1,
    "slug" => "the-keeper-gauge",
    "name" => "The Keeper Gauge",
    "price" => 4.99,
    "images" => [
      "/images/gottafish-product-01.png",
      "/images/gottafish-product-01-side.png",
      "/images/gottafish-product-01-detail.png",
    ],
    "description" => "A professional-grade measuring tool designed for serious anglers...",
  ],
  "the-bucket-station" => [
    "id" => 2,
    "slug" => "the-bucket-station",
    "name" => "The Bucket Station",
    "price" => 49.99,
    "images" => [
      "/images/gottafish-product-02.png",
      "/images/gottafish-product-02-side.png",
      "/images/gottafish-product-02-detail.png",
    ],
    "description" => "Crafted from durable stainless steel, this versatile bucket station...",
  ],
  "the-lucky-bobber" => [
    "id" => 3,
    "slug" => "the-lucky-bobber",
    "name" => "The Lucky Bobber",
    "price" => 4.99,
    "images" => ["/images/gottafish-product-03.png"],
    "description" => "A professional-grade fishing float designed for the serious angler...",
  ],
  "the-command-station" => [
    "id" => 4,
    "slug" => "the-command-station",
    "name" => "The Command Station",
    "price" => 199.99,
    "images" => ["/images/gottafish-product-04.png"],
    "description" => "The Command Station is the ultimate tool for boat organization...",
  ],
];

// Flatten for shuffle
$productList = array_values($products);

// Check if order exists
$purchase_complete = isset($_SESSION['order']) && !empty($_SESSION['order']);
$order = $purchase_complete ? $_SESSION['order'] : null;

// Cart items
$cart = $purchase_complete ? ($_SESSION['cart'] ?? []) : [];

// Total calculation
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['qty'];
}

// Recommended products (exclude purchased)
$excludeSlugs = [];
if (!empty($cart) && is_array($cart)) {
    foreach ($cart as $c) {
        if (is_array($c) && isset($c['slug'])) {
            $excludeSlugs[] = $c['slug'];
        }
    }
}
$availableProducts = array_filter($productList, fn($p) => !in_array($p['slug'], $excludeSlugs));
shuffle($availableProducts);
$recommended = array_slice($availableProducts, 0, 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thank You - <?php echo SITE_NAME; ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<style>
body { font-family: Inter, sans-serif; background:#f9fafb; margin:0; }
.container { max-width:900px; margin:2rem auto; padding:1rem; }
h1 { font-size:2rem; font-weight:700; margin-bottom:1rem; text-align:center; color:#00a651; }
.message { text-align:center; margin-bottom:2rem; font-size:1.1rem; color:#333; }
.order-summary { background:#fff; padding:1.5rem; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); margin-bottom:2rem; }
.cart-item { display:flex; justify-content:space-between; padding:0.5rem 0; border-bottom:1px solid #eee; }
.cart-total { font-weight:700; margin-top:0.5rem; }
.button { display:inline-block; margin:0.5rem; padding:0.5rem 1rem; background:#00a651; color:#fff; border-radius:8px; font-weight:600; cursor:pointer; text-decoration:none; transition:background 0.15s ease; }
.button:hover { background:#008f46; }
.products-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
.product-card { background:#fff; border:1px solid #e5e7eb; border-radius:0.75rem; padding:0.5rem; text-align:center; transition:transform 0.2s; display:flex; flex-direction:column; justify-content:space-between; }
.product-card:hover { transform:scale(1.03); }
.product-card img { width:100%; max-height:220px; object-fit:contain; border-radius:0.5rem; margin-bottom:0.5rem; }
.product-card h3 { font-size:1.1rem; margin-top:0.5rem; font-weight:600; }
.product-card p { font-size:0.875rem; color:#4b5563; margin-top:0.25rem; flex-grow:1; }
.product-card a { display:inline-block; margin-top:0.5rem; padding:0.5rem 0.75rem; background:#00a651; color:white; font-weight:600; border-radius:9999px; text-decoration:none; transition:background 0.15s ease; }
.product-card a:hover { background:#008f46; }
.social-share { display:flex; gap:0.5rem; justify-content:center; margin-top:1rem; flex-wrap:wrap; }
.invoice-buttons { display:flex; justify-content:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:2rem; }
</style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">

<?php
// --- Display logic ---
if ($purchase_complete && !empty($cart)):
?>
    <h1>🎉 Thank You for Your Purchase!</h1>
    <p class="message">Confirmation sent to <strong><?= htmlspecialchars($order['email'] ?? '') ?></strong></p>
    <p class="message">Order Number: <strong><?= htmlspecialchars($order['id'] ?? '') ?></strong></p>

    <div class="order-summary">
        <h2 class="font-semibold text-lg mb-2">Order Summary</h2>
        <?php foreach($cart as $item): ?>
            <div class="cart-item">
                <span><?= htmlspecialchars($item['name']) ?> x <?= $item['qty'] ?></span>
                <span>$<?= number_format($item['price'] * $item['qty'], 2) ?></span>
            </div>
        <?php endforeach; ?>
        <div class="cart-item cart-total">
            <span>Total</span>
            <span>$<?= number_format($total, 2) ?></span>
        </div>
        <div class="text-sm text-gray-500 mt-1">PayPal Transaction ID: <?= htmlspecialchars($order['paypal_id'] ?? 'N/A') ?></div>
    </div>

    <div class="invoice-buttons">
        <button onclick="downloadPDF()" class="button">Download Invoice (PDF)</button>
        <button onclick="window.print()" class="button">Print Invoice</button>
    </div>

<?php elseif ($purchase_complete && empty($cart)): ?>
    <h1>Order Processed</h1>
    <p class="message">It looks like we couldn’t find any products in your order. Please contact support if you think this is a mistake.</p>

<?php else: ?>
    <h1>Welcome to <?php echo SITE_NAME; ?>!</h1>
    <p class="message">Browse our popular products below:</p>
<?php endif; ?>

<h2 class="text-xl font-semibold mb-4"><?= ($purchase_complete && !empty($cart)) || empty($cart) ? "Recommended Products" : "Popular Products" ?></h2>
<div class="products-grid">
    <?php foreach($recommended as $prod): ?>
        <div class="product-card">
            <img src="<?= $prod['images'][0] ?>" alt="<?= htmlspecialchars($prod['name']) ?>">
            <h3><?= htmlspecialchars($prod['name']) ?></h3>
            <p><?= htmlspecialchars($prod['description']) ?></p>
            <div class="mt-2 font-bold">$<?= number_format($prod['price'], 2) ?></div>
            <a href="/product.php?slug=<?= urlencode($prod['slug']) ?>">View Product</a>
        </div>
    <?php endforeach; ?>
</div>

<div class="social-share">
    <button onclick="share('facebook')" class="button">Share on Facebook</button>
    <button onclick="share('twitter')" class="button">Share on Twitter</button>
    <button onclick="share('linkedin')" class="button">Share on LinkedIn</button>
</div>

</div>
<?php include 'footer.php'; ?>

<script>
<?php if ($purchase_complete && !empty($cart)): ?>
confetti({
    particleCount: 150,
    spread: 70,
    origin: { y: 0.6 },
    colors: ['#00a651', '#f5a623', '#ff3f34', '#007aff']
});
<?php endif; ?>

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.setFontSize(16);
    doc.text("<?php echo SITE_NAME; ?> Order Invoice", 20, 20);
    doc.setFontSize(12);
    let y = 40;
    <?php foreach($cart as $item): ?>
        doc.text("<?= addslashes($item['name']) ?> x <?= $item['qty'] ?> - $<?= number_format($item['price'] * $item['qty'],2) ?>", 20, y);
        y += 10;
    <?php endforeach; ?>
    doc.text("Total: $<?= number_format($total,2) ?>", 20, y+10);
    doc.text("PayPal Transaction ID: <?= htmlspecialchars($order['paypal_id'] ?? 'N/A') ?>", 20, y+20);
    doc.text("Customer Email: <?= htmlspecialchars($order['email'] ?? '') ?>", 20, y+30);
    doc.save('invoice.pdf');
}

function share(platform){
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent("I just shopped at <?php echo SITE_NAME; ?>! Check them out!");
    let shareURL = "";
    if(platform==='facebook') shareURL = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
    if(platform==='twitter') shareURL = `https://twitter.com/intent/tweet?text=${text}&url=${url}`;
    if(platform==='linkedin') shareURL = `https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${text}`;
    window.open(shareURL,'_blank');
}
</script>

<?php
// Clear cart after rendering
if ($purchase_complete) unset($_SESSION['cart']);
?>

</body>
</html>
