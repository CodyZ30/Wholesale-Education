<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/../check_blocked_ip.php';

function slugify($s){ $s = strtolower((string)$s); $s = preg_replace('/[^a-z0-9]+/i','-', $s); return trim($s,'-'); }

$kbFile = __DIR__ . '/../data/knowledge_base.json';
$articles = [];
if (file_exists($kbFile)) {
  $decoded = json_decode((string)file_get_contents($kbFile), true);
  if (is_array($decoded)) $articles = $decoded;
}

$slug = isset($_GET['slug']) ? (string)$_GET['slug'] : '';
// Allow pretty URL: /support/knowledge-base/{slug}
if ($slug === '' && isset($_SERVER['REQUEST_URI'])) {
  $req = strtok((string)$_SERVER['REQUEST_URI'], '?');
  // Expecting /support/knowledge-base/{slug}
  $parts = explode('/', trim($req, '/'));
  if (count($parts) >= 3 && $parts[0] === 'support' && $parts[1] === 'knowledge-base') {
    $maybe = $parts[2] ?? '';
    if ($maybe !== '' && $maybe !== 'all') { $slug = $maybe; }
  }
}
$article = null;
if ($slug !== '') {
  foreach ($articles as $a) {
    $id = (string)($a['id'] ?? '');
    $built = slugify((string)($a['title'] ?? 'article')) . '-' . strtolower($id);
    if ($built === $slug) { $article = $a; break; }
  }
}

// Sort for index
$sorted = $articles;
usort($sorted, function($a,$b){
  $ua = strtotime((string)($a['updated_at'] ?? '')) ?: 0;
  $ub = strtotime((string)($b['updated_at'] ?? '')) ?: 0;
  return $ub <=> $ua;
});

$pageTitle = $article ? ($article['title'] ?? 'Article') : 'Knowledge Base';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle) . ' - ' . SITE_NAME; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-white">
  <?php include __DIR__ . '/../includes/header.php'; ?>
  <main class="container mx-auto px-4 py-8">
    <?php if ($article): ?>
      <nav class="text-sm text-gray-500 mb-4"><a href="/support" class="hover:underline">Support</a> / <a href="/support/knowledge-base/all" class="hover:underline">Knowledge Base</a> / <?php echo htmlspecialchars($article['category'] ?? ''); ?></nav>
      <div class="mb-4">
        <a href="/support/knowledge-base/all" class="inline-flex items-center text-blue-600 hover:text-blue-800 transition">
          <i class="fas fa-arrow-left mr-2"></i>
          Back to All Articles
        </a>
      </div>
      <h1 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900"><?php echo htmlspecialchars($article['title'] ?? ''); ?></h1>
      <div class="text-sm text-gray-500 mb-6">Last updated: <?php echo htmlspecialchars($article['updated_at'] ?? ''); ?></div>
      <article class="prose max-w-none">
        <p class="text-gray-800 leading-relaxed"><?php echo nl2br(htmlspecialchars((string)($article['content'] ?? ''))); ?></p>
      </article>
    <?php else: ?>
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900">All Articles</h1>
        <div class="text-sm text-gray-500"><?php echo count($sorted); ?> total</div>
      </div>
      
      <div class="divide-y border border-gray-200 rounded-md bg-white">
        <?php foreach ($sorted as $a):
          $id = (string)($a['id'] ?? '');
          $s = slugify((string)($a['title'] ?? 'article')) . '-' . strtolower($id);
          $excerpt = trim(mb_substr((string)($a['content'] ?? ''), 0, 200));
          if (mb_strlen((string)($a['content'] ?? '')) > 200) $excerpt .= '...';
        ?>
        <a href="/support/knowledge-base/<?php echo urlencode($s); ?>" class="block p-4 hover:bg-gray-50 transition">
          <div class="font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars((string)($a['title'] ?? '')); ?></div>
          <div class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($excerpt); ?></div>
          <div class="text-xs text-gray-400"><?php echo htmlspecialchars((string)($a['category'] ?? '')); ?> • <?php echo htmlspecialchars((string)($a['updated_at'] ?? '')); ?></div>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>


