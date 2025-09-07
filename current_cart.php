<?php
session_start();
header('Content-Type: application/json');

// Get cart from session (synced from client-side)
$cart = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];

// If no cart in session, try to get from client-side data (for debugging)
if (empty($cart) && isset($_POST['cart_data'])) {
    $clientCart = json_decode($_POST['cart_data'], true);
    if (is_array($clientCart)) {
        $cart = $clientCart;
        $_SESSION['cart'] = $cart;
    }
}

$subtotal = 0.0; $count = 0;
foreach ($cart as $item) {
  $qty = max(0, (int)($item['qty'] ?? 0));
  $price = (float)($item['price'] ?? 0);
  $subtotal += $price * $qty;
  $count += $qty;
}

// Coupon/shipping/tax consistent with update_cart.php
$couponCode = isset($_SESSION['coupon_code']) ? strtoupper((string)$_SESSION['coupon_code']) : '';
$coupon = null; $discount = 0.0; $shipping = 0.0; $tax = 0.0;
$SHIPPING_BASE = 12.00; $SHIPPING_THRESHOLD = 250.00; 
// Load tax rate from settings
$TAX_RATE = 0.00;
$cfg = __DIR__ . '/config/payment_settings.json';
if (file_exists($cfg)) {
  $cfgData = json_decode((string)file_get_contents($cfg), true);
  if (is_array($cfgData)) { $TAX_RATE = (float)($cfgData['tax_rate'] ?? 0); }
}
if ($subtotal >= $SHIPPING_THRESHOLD) $shipping = 0.0; else $shipping = $SHIPPING_BASE;

if ($couponCode !== '') {
  $couponsPath = __DIR__ . '/data/coupons.json';
  if (file_exists($couponsPath)) {
    $list = json_decode((string)file_get_contents($couponsPath), true);
    if (is_array($list)) {
      foreach ($list as $c) {
        if (isset($c['code']) && strtoupper((string)$c['code']) === $couponCode && !empty($c['enabled'])) {
          $coupon = $c; break;
        }
      }
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
$grand = $taxBase + $shipping + $tax;

echo json_encode([
  'cart' => $cart,
  'summary' => [
    'count'    => $count,
    'subtotal' => number_format($subtotal, 2),
    'discount' => number_format($discount, 2),
    'shipping' => number_format($shipping, 2),
    'tax'      => number_format($tax, 2),
    'total'    => number_format($grand, 2),
    'coupon'   => $coupon ? ['code' => $coupon['code'], 'name' => $coupon['name'] ?? ''] : null,
    'tax_rate' => $TAX_RATE,
  ]
]);
