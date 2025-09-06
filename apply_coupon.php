<?php
session_start();

header('Content-Type: application/json');

function load_json_file(string $path): array {
  if (!file_exists($path)) return [];
  $raw = file_get_contents($path);
  $data = json_decode((string)$raw, true);
  return is_array($data) ? $data : [];
}

$body = file_get_contents('php://input');
$payload = json_decode((string)$body, true);
$code = isset($payload['code']) ? strtoupper(trim((string)$payload['code'])) : '';

if ($code === '') {
  echo json_encode(['success' => false, 'error' => 'No code provided']);
  exit;
}

// Special: REMOVE clears any applied coupon
if ($code === 'REMOVE') {
  unset($_SESSION['coupon_code']);
  echo json_encode(['success' => true, 'removed' => true]);
  exit;
}

$couponsPath = __DIR__ . '/data/coupons.json';
$coupons = load_json_file($couponsPath);
$coupon = null;
foreach ($coupons as $c) {
  if (isset($c['code']) && strtoupper((string)$c['code']) === $code && !empty($c['enabled'])) {
    $coupon = $c; break;
  }
}

if (!$coupon) {
  echo json_encode(['success' => false, 'error' => 'Invalid or disabled coupon']);
  exit;
}

// Compute current subtotal from session cart
$subtotal = 0.0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $it) {
    $price = (float)($it['price'] ?? 0);
    $qty   = (int)($it['qty'] ?? 0);
    $subtotal += $price * $qty;
  }
}

$min = (float)($coupon['minSubtotal'] ?? 0);
if ($min > 0 && $subtotal < $min) {
  echo json_encode(['success' => false, 'error' => 'Subtotal requirement not met', 'required' => $min, 'subtotal' => $subtotal]);
  exit;
}

$_SESSION['coupon_code'] = $coupon['code'];

echo json_encode(['success' => true, 'coupon' => $coupon]);
exit;
?>


