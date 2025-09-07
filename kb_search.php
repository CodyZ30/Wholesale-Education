<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

$kbFile = __DIR__ . '/data/knowledge_base.json';
if (!file_exists($kbFile)) {
  echo json_encode(['success' => false, 'error' => 'KB file not found']);
  exit;
}

function slugify($s){
  $s = strtolower((string)$s);
  $s = preg_replace('/[^a-z0-9]+/i', '-', $s);
  $s = trim($s, '-');
  return $s ?: 'kb';
}

function build_slug(array $a): string {
  $id = (string)($a['id'] ?? 'kb0');
  $title = (string)($a['title'] ?? 'article');
  return slugify($title) . '-' . strtolower($id);
}

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$cat = isset($_GET['category']) ? trim((string)$_GET['category']) : '';
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 30;
$offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

$articles = json_decode((string)file_get_contents($kbFile), true);
if (!is_array($articles)) $articles = [];

// Basic scoring + filtering
$filtered = [];
foreach ($articles as $a) {
  if (!is_array($a)) continue;
  if ($cat !== '' && strcasecmp((string)($a['category'] ?? ''), $cat) !== 0) continue;
  if ($q !== '') {
    $hay = strtolower(((string)($a['title'] ?? '')) . ' ' . ((string)($a['content'] ?? '')) . ' ' . ((string)($a['category'] ?? '')));
    if (strpos($hay, strtolower($q)) === false) continue;
  }
  $filtered[] = $a;
}

// Sort by updated_at desc if present
usort($filtered, function($a, $b){
  $ua = strtotime((string)($a['updated_at'] ?? '')) ?: 0;
  $ub = strtotime((string)($b['updated_at'] ?? '')) ?: 0;
  return $ub <=> $ua;
});

$total = count($filtered);
$slice = array_slice($filtered, $offset, $limit);

$results = [];
foreach ($slice as $a) {
  $content = (string)($a['content'] ?? '');
  $excerpt = trim(mb_substr(strip_tags($content), 0, 180));
  if (mb_strlen($content) > 180) $excerpt .= '...';
  $results[] = [
    'id' => (string)($a['id'] ?? ''),
    'title' => (string)($a['title'] ?? ''),
    'category' => (string)($a['category'] ?? ''),
    'slug' => build_slug($a),
    'excerpt' => $excerpt,
    'updated_at' => (string)($a['updated_at'] ?? ''),
  ];
}

echo json_encode([
  'success' => true,
  'total' => $total,
  'offset' => $offset,
  'limit' => $limit,
  'results' => $results,
]);
?>


