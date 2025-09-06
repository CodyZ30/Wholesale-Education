<?php
session_start();
// Identify session key for logging (shortened)
$__sid = session_id();

/**
 * Helper: build a stable key for an item (id + slug + options)
 */
function item_key(array $item): string {
    $id     = isset($item['id'])   ? (string)$item['id']   : '';
    $slug   = isset($item['slug']) ? (string)$item['slug'] : '';

    // Options could be size/color/variant, etc. Sort keys for stability.
    $opts = '';
    if (!empty($item['options']) && is_array($item['options'])) {
        $sorted = $item['options'];
        ksort($sorted);
        $opts = json_encode($sorted);
    }
    return sha1($id . '|' . $slug . '|' . $opts);
}

/**
 * Read raw body & basic idempotency guard
 */
$raw = file_get_contents('php://input');
$hash = sha1((string)$raw);
$now  = microtime(true);

// If the exact same payload arrives again within 1.5s, treat as duplicate and just echo current summary
if (isset($_SESSION['last_cart_hash'], $_SESSION['last_cart_ts'])) {
    if ($_SESSION['last_cart_hash'] === $hash && ($now - (float)$_SESSION['last_cart_ts']) < 1.5) {
        $current = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
        $total = 0.0; $count = 0;
        foreach ($current as $ci) {
            $qty = max(0, (int)($ci['qty'] ?? 0));
            $price = (float)($ci['price'] ?? 0);
            $total += $price * $qty;
            $count += $qty;
        }
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'deduped' => true,
            'count'   => $count,
            'total'   => number_format($total, 2),
        ]);
        exit;
    }
}

/**
 * Parse incoming JSON; if invalid, do not overwrite—just return current state.
 */
$incoming = json_decode($raw, true);
if (!is_array($incoming)) {
    $incoming = null;
}

if ($incoming === null) {
    // No valid cart provided; report current
    $current = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $total = 0.0; $count = 0;
    foreach ($current as $ci) {
        $qty = max(0, (int)($ci['qty'] ?? 0));
        $price = (float)($ci['price'] ?? 0);
        $total += $price * $qty;
        $count += $qty;
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'count'   => $count,
        'total'   => number_format($total, 2),
    ]);
    exit;
}

/**
 * De-duplicate incoming items by key and sanitize fields.
 * Keep the LAST occurrence of a duplicate key (prevents qty doubling from duplicates in payload).
 */
$map = [];
foreach ($incoming as $item) {
    if (!is_array($item)) continue;

    // sanitize
    $clean = [
        'id'     => isset($item['id']) ? (string)$item['id'] : '',
        'slug'   => isset($item['slug']) ? (string)$item['slug'] : '',
        'name'   => isset($item['name']) ? (string)$item['name'] : '',
        'image'  => isset($item['image']) ? (string)$item['image'] : '',
        'url'    => isset($item['url']) ? (string)$item['url'] : '',
        'price'  => (float)($item['price'] ?? 0),
        'qty'    => max(0, (int)($item['qty'] ?? 0)), // allow 0 to mean "remove"
    ];

    // preserve options if present
    if (isset($item['options']) && is_array($item['options'])) {
        $clean['options'] = $item['options'];
    }

    $key = item_key($clean);
    $map[$key] = $clean; // last one wins
}

/**
 * Remove any items with qty <= 0
 */
$deduped = array_values(array_filter($map, function ($it) {
    return isset($it['qty']) && $it['qty'] > 0;
}));

/**
 * Overwrite the session cart with the deduped incoming payload.
 * (Treat incoming as authoritative to avoid "merge then double" issues across pages.)
 */
$_SESSION['cart'] = $deduped;

/**
 * Save idempotency fingerprint
 */
$_SESSION['last_cart_hash'] = $hash;
$_SESSION['last_cart_ts']   = $now;

/**
 * Build summary
 */
// ---- Summary (server-authoritative) ----
$subtotal = 0.0; $count = 0;
foreach ($deduped as $item) {
    $qty = max(0, (int)($item['qty'] ?? 0));
    $price = (float)($item['price'] ?? 0);
    $subtotal += $price * $qty;
    $count += $qty;
}

// Load coupon catalog (optional)
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

header('Content-Type: application/json');
// Prepare summary
$summary = [
    'success'  => true,
    'count'    => $count,
    'subtotal' => number_format($subtotal, 2),
    'discount' => number_format($discount, 2),
    'shipping' => number_format($shipping, 2),
    'tax'      => number_format($tax, 2),
    'total'    => number_format($grand, 2),
    'coupon'   => $coupon ? ['code' => $coupon['code'], 'name' => $coupon['name'] ?? ''] : null,
];

// ---- Append cart log entry ----
try {
    $logDir  = __DIR__ . '/data';
    $logFile = $logDir . '/cart_logs.json';
    if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }

    $existing = [];
    if (file_exists($logFile)) {
        $raw = file_get_contents($logFile);
        $decoded = json_decode((string)$raw, true);
        if (is_array($decoded)) $existing = $decoded;
    }

    $existing[] = [
        'ts'      => date('c'),
        'session' => substr($__sid, 0, 20),
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
        'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'items'   => $deduped,
        'summary' => $summary,
    ];

    // Bound file size: cap to last 2000 entries
    if (count($existing) > 2000) {
        $existing = array_slice($existing, -2000);
    }
    file_put_contents($logFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
} catch (Throwable $e) {
    // best-effort logging only
}

echo json_encode($summary);
