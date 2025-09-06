<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

$itemsB64 = isset($_GET['items']) ? (string)$_GET['items'] : '';
$items = [];
if ($itemsB64 !== '') {
  $json = base64_decode($itemsB64, true);
  if (is_string($json)) {
    $decoded = json_decode($json, true);
    if (is_array($decoded)) { $items = $decoded; }
  }
}

if (!is_array($items)) { $items = []; }

// Minimal sanitize
$clean = [];
foreach ($items as $it) {
  if (!is_array($it)) continue;
  $clean[] = [
    'id' => (string)($it['id'] ?? ''),
    'slug' => (string)($it['slug'] ?? ''),
    'name' => (string)($it['name'] ?? ''),
    'image' => (string)($it['image'] ?? ''),
    'url' => (string)($it['url'] ?? ''),
    'price' => (float)($it['price'] ?? 0),
    'qty' => max(0, (int)($it['qty'] ?? 0)),
  ];
}

$_SESSION['cart'] = array_values(array_filter($clean, fn($i)=> ($i['qty'] ?? 0) > 0));

header('Location: /cart');
exit;
