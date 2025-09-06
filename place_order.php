<?php
session_start();
header('Content-Type: application/json');

function read_json_file(string $path): array {
  if (!file_exists($path)) return [];
  $raw = file_get_contents($path);
  $data = json_decode((string)$raw, true);
  return is_array($data) ? $data : [];
}

$body = file_get_contents('php://input');
$payload = json_decode((string)$body, true);
if (!is_array($payload)) $payload = [];

$customer = [
  'name'   => trim((string)($payload['name'] ?? '')),
  'email'  => trim((string)($payload['email'] ?? '')),
  'shipping' => $payload['shipping'] ?? [],
  'billing'  => $payload['billing'] ?? [],
  'method'   => trim((string)($payload['method'] ?? 'manual')),
];

$cart = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Compute totals (must match update_cart.php)
$subtotal = 0.0; $count = 0;
foreach ($cart as $item) {
  $qty = max(0, (int)($item['qty'] ?? 0));
  $price = (float)($item['price'] ?? 0);
  $subtotal += $price * $qty;
  $count += $qty;
}

$couponCode = isset($_SESSION['coupon_code']) ? strtoupper((string)$_SESSION['coupon_code']) : '';
$coupon = null; $discount = 0.0; $shipping = 0.0; $tax = 0.0;
$SHIPPING_BASE = 12.00; $SHIPPING_THRESHOLD = 250.00; 
$TAX_RATE = 0.00;
$cfg = __DIR__ . '/config/payment_settings.json';
if (file_exists($cfg)) {
  $cfgData = json_decode((string)file_get_contents($cfg), true);
  if (is_array($cfgData)) { $TAX_RATE = (float)($cfgData['tax_rate'] ?? 0); }
}
$shipping = ($subtotal >= $SHIPPING_THRESHOLD) ? 0.0 : $SHIPPING_BASE;

if ($couponCode !== '') {
  $couponsPath = __DIR__ . '/data/coupons.json';
  $list = read_json_file($couponsPath);
  foreach ($list as $c) {
    if (isset($c['code']) && strtoupper((string)$c['code']) === $couponCode && !empty($c['enabled'])) {
      $coupon = $c; break;
    }
  }
  if ($coupon) {
    $min = (float)($coupon['minSubtotal'] ?? 0);
    if ($min <= 0 || $subtotal >= $min) {
      $type = (string)($coupon['type'] ?? '');
      if ($type === 'percent') {
        $val = (float)($coupon['value'] ?? 0);
        $discount = $subtotal * $val;
      } elseif ($type === 'fixed') {
        $val = (float)($coupon['value'] ?? 0);
        $discount = min($val, $subtotal);
      } elseif ($type === 'shipping') {
        $shipping = 0.0;
      }
    }
  }
}
$taxBase = max($subtotal - $discount, 0.0);
if ($TAX_RATE > 0) $tax = $taxBase * $TAX_RATE;
$total = $taxBase + $shipping + $tax;

// Persist order
$salesFile = __DIR__ . '/sales.json';
$sales = read_json_file($salesFile);
$orderId = time();
$order = [
  'order_id' => $orderId,
  'customer_name' => $customer['name'],
  'customer_email' => $customer['email'],
  'items' => $cart,
  'total_amount' => $total,
  'order_date' => date('Y-m-d H:i:s'),
  'summary' => [
    'subtotal' => $subtotal,
    'discount' => $discount,
    'shipping' => $shipping,
    'tax' => $tax,
    'tax_rate' => $TAX_RATE,
  ],
];
$sales[] = $order;
file_put_contents($salesFile, json_encode($sales, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Store brief receipt in session for thank-you
$_SESSION['order'] = [
  'id' => $orderId,
  'email' => $customer['email'],
  'method' => $customer['method'],
];

echo json_encode(['success' => true, 'order_id' => $orderId, 'redirect' => '/thank-you.php']);
exit;

