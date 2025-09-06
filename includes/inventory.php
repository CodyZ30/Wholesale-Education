<?php
// Inventory helpers: load/save products and log stock history

function inv_products_path(): string {
  return __DIR__ . '/../data/products.php';
}

function inv_history_path(): string {
  return __DIR__ . '/../inventory_history.json';
}

function inv_load_products(): array {
  $file = inv_products_path();
  if (!file_exists($file)) return [];
  $arr = require $file;
  return is_array($arr) ? $arr : [];
}

function inv_write_products(array $products): void {
  $file = inv_products_path();
  // Emit simple PHP array exporter (flat, supports nested arrays)
  $export = "<?php\n\nreturn [\n";
  foreach ($products as $slug => $p) {
    $export .= "  '" . addslashes((string)$slug) . "' => [\n";
    foreach ($p as $k => $v) {
      $export .= "    '" . addslashes((string)$k) . "' => ";
      if (is_array($v)) {
        $export .= inv_export_array($v) . ",\n";
      } elseif (is_bool($v)) {
        $export .= ($v ? 'true' : 'false') . ",\n";
      } elseif (is_numeric($v)) {
        $export .= $v . ",\n";
      } else {
        $export .= "'" . addslashes((string)$v) . "',\n";
      }
    }
    $export .= "  ],\n";
  }
  $export .= "];\n";
  file_put_contents($file, $export);
}

function inv_export_array($arr): string {
  // Recursively export arrays compactly
  if (array_values($arr) === $arr) { // indexed
    $parts = [];
    foreach ($arr as $v) {
      if (is_array($v)) $parts[] = inv_export_array($v);
      elseif (is_bool($v)) $parts[] = $v ? 'true' : 'false';
      elseif (is_numeric($v)) $parts[] = (string)$v;
      else $parts[] = "'" . addslashes((string)$v) . "'";
    }
    return '[' . implode(', ', $parts) . ']';
  }
  // associative
  $parts = [];
  foreach ($arr as $k => $v) {
    if (is_array($v)) $parts[] = "'" . addslashes((string)$k) . "' => " . inv_export_array($v);
    elseif (is_bool($v)) $parts[] = "'" . addslashes((string)$k) . "' => " . ($v ? 'true' : 'false');
    elseif (is_numeric($v)) $parts[] = "'" . addslashes((string)$k) . "' => " . $v;
    else $parts[] = "'" . addslashes((string)$k) . "' => '" . addslashes((string)$v) . "'";
  }
  return '[' . implode(', ', $parts) . ']';
}

function inv_log_history(array $entry): void {
  $path = inv_history_path();
  $hist = [];
  if (file_exists($path)) {
    $json = file_get_contents($path);
    $hist = json_decode($json, true);
    if (!is_array($hist)) $hist = [];
  }
  $entry['ts'] = $entry['ts'] ?? date('c');
  $hist[] = $entry;
  file_put_contents($path, json_encode($hist, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function inv_adjust_stock(string $slug, int $delta, string $reason = 'manual', string $note = '', array $meta = []): void {
  $products = inv_load_products();
  if (!isset($products[$slug])) return;
  $cur = $products[$slug]['stock'] ?? 0;
  if (!is_numeric($cur)) $cur = 0;
  $new = (int)$cur + (int)$delta;
  if ($new < 0) $new = 0;
  $products[$slug]['stock'] = $new;
  inv_write_products($products);
  inv_log_history([
    'slug' => $slug,
    'delta' => $delta,
    'new' => $new,
    'reason' => $reason,
    'note' => $note,
  ] + $meta);
}

?>

