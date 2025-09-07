<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/translation.php';
include_once __DIR__ . '/layout.php';

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$catalog = require __DIR__ . '/../data/guides.php';

$contentPath = __DIR__ . '/../data/guides_content.json';
$content = [];
if (file_exists($contentPath)) {
  $decoded = json_decode((string)file_get_contents($contentPath), true);
  if (is_array($decoded)) $content = $decoded;
}

function slugify($s){ return strtolower(trim(preg_replace('/[^a-z0-9]+/i','-', (string)$s), '-')); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || !hash_equals($csrf, $_POST['csrf_token'])) {
    http_response_code(400);
    exit('Invalid CSRF token');
  }

  $slug = slugify($_POST['slug'] ?? '');
  $title = trim((string)($_POST['title'] ?? ''));
  $excerpt = trim((string)($_POST['excerpt'] ?? ''));
  $body = (string)($_POST['body'] ?? '');
  if ($slug === '' || $title === '') {
    $_SESSION['guides_msg'] = 'Title and slug are required';
    header('Location: guides.php');
    exit;
  }
  $content[$slug] = [
    'title' => $title,
    'excerpt' => $excerpt,
    'body' => $body,
    'updated_at' => date('c'),
  ];
  file_put_contents($contentPath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  $_SESSION['guides_msg'] = 'Guide saved.';
  header('Location: guides.php?edit=' . urlencode($slug));
  exit;
}

$editSlug = isset($_GET['edit']) ? (string)$_GET['edit'] : '';
$edit = $content[$editSlug] ?? null;

admin_layout_start(__('guides'), 'guides');

    <div class="dashboard-grid">
      <?php
        // Build category slug map and resolve selected category
        $catLabels = array_keys($catalog);
        $catSlugMap = [];
        foreach ($catLabels as $label) { $catSlugMap[slugify($label)] = $label; }
        $selectedSlug = isset($_GET['cat']) ? slugify((string)$_GET['cat']) : '';
        if ($selectedSlug === '' || !isset($catSlugMap[$selectedSlug])) {
          $first = null;
          foreach ($catLabels as $l) { if ($l !== 'Quick Tips') { $first = $l; break; } }
          if ($first === null && !empty($catLabels)) { $first = $catLabels[0]; }
          $selectedSlug = slugify((string)$first);
        }
        $selectedLabel = $catSlugMap[$selectedSlug] ?? '';
        $topicList = $selectedLabel !== '' ? ($catalog[$selectedLabel] ?? []) : [];
      ?>

      <!-- Category filter (dropdown + menu list) -->
      <div class="dashboard-card">
        <h3 class="text-lg font-medium mb-3">Categories</h3>
        <ul class="space-y-1 max-h-[60vh] overflow-auto rounded border">
          <?php foreach ($catSlugMap as $slug => $label): $count = isset($catalog[$label]) && is_array($catalog[$label]) ? count($catalog[$label]) : 0; ?>
            <li>
              <a class="block px-2 py-1 rounded <?php echo $slug===$selectedSlug?'btn btn-primary':'hover:bg-white/5'; ?>" href="guides.php?cat=<?php echo htmlspecialchars((string)$slug); ?>"><?php echo htmlspecialchars((string)$label); ?> <span class="text-xs opacity-70">(<?php echo $count; ?>)</span></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Topics for selected category -->
      <div class="dashboard-card md:col-span-2">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-medium">Topics — <?php echo htmlspecialchars((string)$selectedLabel); ?> <span class="text-sm text-gray-400">(<?php echo count($topicList); ?>)</span></h3>
          <a class="btn" href="/guides/<?php echo htmlspecialchars((string)$selectedSlug); ?>" target="_blank">View Category</a>
        </div>
        <div class="rounded-lg border overflow-auto" style="max-height:70vh;">
          <ul class="divide-y">
            <?php if (empty($topicList)): ?>
              <li class="p-3 text-gray-400">No topics found.</li>
            <?php else: foreach ($topicList as $t): $slug = slugify($t); ?>
              <li class="flex items-center justify-between gap-2 p-2">
                <a class="hover:underline truncate" href="/guides/<?php echo $slug; ?>" target="_blank"><?php echo htmlspecialchars((string)$t); ?></a>
                <a class="btn btn-primary" href="guides.php?edit=<?php echo $slug; ?>">Edit</a>
              </li>
            <?php endforeach; endif; ?>
          </ul>
        </div>
      </div>

      <!-- Editor -->
      <div class="dashboard-card">
        <h3 class="text-lg font-medium mb-3">Editor</h3>
        <?php if (!empty($_SESSION['guides_msg'])): ?>
          <div class="mb-3 text-sm" style="color:#1dd171;">
            <?php echo htmlspecialchars((string)$_SESSION['guides_msg']); unset($_SESSION['guides_msg']); ?>
          </div>
        <?php endif; ?>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$csrf); ?>">
          <div class="mb-3">
            <label class="text-sm text-gray-400">Slug</label>
            <input class="w-full border rounded px-3 py-2 bg-transparent" name="slug" value="<?php echo htmlspecialchars((string)$editSlug); ?>" placeholder="auto-from-title or custom" />
          </div>
          <div class="mb-3">
            <label class="text-sm text-gray-400">Title</label>
            <input class="w-full border rounded px-3 py-2 bg-transparent" name="title" value="<?php echo htmlspecialchars((string)($edit['title'] ?? '')); ?>" />
          </div>
          <div class="mb-3">
            <label class="text-sm text-gray-400">Excerpt</label>
            <textarea class="w-full border rounded px-3 py-2 bg-transparent" name="excerpt" rows="3"><?php echo htmlspecialchars((string)($edit['excerpt'] ?? '')); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="text-sm text-gray-400">Body (HTML allowed)</label>
            <textarea class="w-full border rounded px-3 py-2 bg-transparent" name="body" rows="12"><?php echo htmlspecialchars((string)($edit['body'] ?? '')); ?></textarea>
          </div>
          <button class="btn btn-primary" type="submit">Save</button>
        </form>
      </div>
    </div>
<?php admin_layout_end();


