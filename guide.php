<?php
require_once __DIR__ . '/includes/config.php';
include 'includes/header.php';

$slug = isset($_GET['slug']) ? strtolower(trim((string)$_GET['slug'])) : '';
$catalog = require __DIR__ . '/data/guides.php';
$allProducts = require __DIR__ . '/data/products.php';

// Flatten categories -> list of [slug => [title, category]]
$map = [];
foreach ($catalog as $cat => $topics) {
  foreach ($topics as $t) {
    $s = strtolower(trim(preg_replace('/[^a-z0-9]+/i','-', $t), '-'));
    $map[$s] = ['title' => $t, 'category' => $cat];
  }
}

$exists = array_key_exists($slug, $map);
$title = $exists ? $map[$slug]['title'] : 'Guide';
$category = $exists ? $map[$slug]['category'] : 'Guides';

// Editable content store
$contentPath = __DIR__ . '/data/guides_content.json';
$content = [];
if (file_exists($contentPath)) {
  $json = file_get_contents($contentPath);
  $decoded = json_decode($json, true);
  if (is_array($decoded)) $content = $decoded;
}
$entry = $content[$slug] ?? null;

// Default 5-section generator with seed-based variations for unique pages
function gf_generate_default_sections(string $slug, string $title, string $category): string {
  $seed = abs(crc32($slug));
  $pick = function(array $arr) use (&$seed) {
    $idx = $seed % max(count($arr),1);
    $seed = ($seed * 1103515245 + 12345) & 0x7fffffff; // LCG step
    return $arr[$idx];
  };
  $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
  $safeCategory = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
  $brand = htmlspecialchars((string)SITE_NAME, ENT_QUOTES, 'UTF-8');

  // Phrase banks
  $hOverview = [
    'Overview', 'Why It Works', 'Before You Launch', 'Pattern Snapshot', 'Core Ideas'
  ];
  $hWhen = [
    'When It Fires', 'Season & Conditions', 'Timing The Bite', 'Windows of Opportunity'
  ];
  $hGear = [
    'Dialed Gear & Setup', 'Tackle & Rigging', 'Tools For The Job', 'Rod • Line • Terminal'
  ];
  $hExec = [
    'Execution & Boat Angles', 'Presentation & Adjustments', 'Boat Control & Cadence', 'How To Work It'
  ];
  $hTips = [
    'Pro Notes & Fixes', 'Troubleshooting', 'Small Tweaks, Big Bites', 'Advanced Tips'
  ];

  $openers = [
    "$brand presents ‘$safeTitle’ — a lake‑tested walkthrough tailored to real conditions.",
    "This is your fast track to mastering $safeTitle with practical, repeatable steps.",
    "Skip the fluff. Here’s how to execute $safeTitle when it matters most.",
    "An actionable field guide to $safeTitle, built from hours on the water.",
  ];
  $whenBullets = [
    ['Clear mornings with a light ripple', 'Cold fronts that stall mid‑day feeding', 'Shad pushed by a crosswind'],
    ['Mud lines around wind‑blown points', 'New water after a small rise', 'Shade seams near channel swings'],
    ['Pre‑spawn warming trends', 'Post‑front bluebird skies', 'Major/minor moon windows'],
    ['Low light with bait flipping', 'Two‑foot visibility and dying grass', 'Rock transitions with current'],
  ];
  $gearBullets = [
    ['Medium‑fast rod to load smaller hooks', 'Braid to fluoro leader for crisp control', 'Compact bait with subtle action'],
    ['Moderate glass for treble baits', '12–15 lb fluoro for abrasion', 'Natural tones on sunny days'],
    ['High‑gear reel for fast line pick‑up', 'Straight fluoro for suspending baits', 'Add a feathered treble for hang‑time'],
    ['Medium‑heavy for power fishing', '30 lb braid around cover', 'Bright accent color in stained water'],
  ];
  $execSteps = [
    ['Post up 45° to the structure', 'Cast past the target to stage the bait', 'Work from high percentage outer edges inward', 'Alter retrieve speed every third cast'],
    ['Map a clean casting lane', 'Start broad, then tighten angles', 'Pause longer on shade, speed up on hard bottom', 'Rotate profiles before leaving'],
    ['Quarter the wind to maintain contact', 'Lead with the quietest approach', 'Note every bump and repeat it', 'Finish with a downsized follow‑up'],
  ];
  $tipsBullets = [
    ['Short strikes? add a trailer hook or shorten the skirt', 'Followers only? speed‑burst then kill it', 'Boat too close? Spot‑lock 10 yards back'],
    ['High pressure? swap to a silent bait', 'Clouds rolled in? widen your search depth', 'Dirty water? upsize blade or add flash'],
    ['Small fish only? big profile at the best hour', 'Wind died? switch to finessey plastics', 'Missed bites? sharpen hooks and retie'],
  ];

  $intro = $pick($openers);
  $when = $pick($whenBullets);
  $gear = $pick($gearBullets);
  $exec = $pick($execSteps);
  $tips = $pick($tipsBullets);

  $hh1 = $pick($hOverview); $hh2 = $pick($hWhen); $hh3 = $pick($hGear); $hh4 = $pick($hExec); $hh5 = $pick($hTips);
 
  ob_start();
  ?>
  <article class="prose max-w-none">
    <section>
      <h2><?php echo htmlspecialchars($hh1); ?></h2>
      <p><?php echo htmlspecialchars($intro); ?> It explains when it shines, the right rig, and the cadence that turns follows into bites.</p>
    </section>
    <section>
      <h2><?php echo htmlspecialchars($hh2); ?></h2>
      <ul>
        <?php foreach ($when as $b): ?><li><?php echo htmlspecialchars($b); ?></li><?php endforeach; ?>
      </ul>
    </section>
    <section>
      <h2><?php echo htmlspecialchars($hh3); ?></h2>
      <ul>
        <?php foreach ($gear as $g): ?><li><?php echo htmlspecialchars($g); ?></li><?php endforeach; ?>
      </ul>
      <figure>
        <img src="/images/fish.png" alt="<?php echo $brand; ?> gear illustration" style="max-width:320px;height:auto">
        <figcaption>Dial your setup once — then focus on boat lanes and angles.</figcaption>
      </figure>
    </section>
    <section>
      <h2><?php echo htmlspecialchars($hh4); ?></h2>
      <ol>
        <?php foreach ($exec as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
      </ol>
    </section>
    <section>
      <h2><?php echo htmlspecialchars($hh5); ?></h2>
      <ul>
        <?php foreach ($tips as $t): ?><li><?php echo htmlspecialchars($t); ?></li><?php endforeach; ?>
      </ul>
      <p class="text-sm text-gray-600">Category: <?php echo $safeCategory; ?> · Brought to you by <?php echo $brand; ?>.</p>
    </section>
  </article>
  <?php
  return (string)ob_get_clean();
}

// Meta
$pageTitle = $title . ' - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars((string)$pageTitle); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars((string)($entry['excerpt'] ?? ($title . ' — a comprehensive guide by ' . SITE_NAME))); ?>" />
</head>
<body>
<main class="container mx-auto px-4 md:px-6 py-10">
  <nav class="text-sm text-gray-500 mb-6"><a class="hover:underline" href="/guides">Guides</a> / <span><?php echo htmlspecialchars((string)$category); ?></span></nav>
  <header class="mb-6">
    <h1 class="text-3xl md:text-4xl font-extrabold mb-2"><?php echo htmlspecialchars((string)$title); ?></h1>
    <div class="flex items-center gap-3 text-gray-600">
      <img src="/images/logo.png" alt="<?php echo SITE_NAME; ?> logo" style="height:28px;width:auto">
      <span>By <?php echo SITE_NAME; ?></span>
    </div>
  </header>

  <div class="grid md:grid-cols-3 gap-8">
    <div class="md:col-span-2">
      <?php if (!empty($entry) && !empty($entry['body'])): ?>
        <article class="prose max-w-none">
          <?php echo $entry['body']; ?>
        </article>
      <?php else: ?>
        <?php echo gf_generate_default_sections($slug, $title, $category); ?>
      <?php endif; ?>
    </div>

    <aside>
      <div class="bg-white rounded-2xl border shadow p-4">
        <h2 class="text-lg font-bold mb-3">Recommended Gear</h2>
        <?php
          $recoSlugs = [];
          $catLower = strtolower($category);
          $titleLower = strtolower($title);
          if (strpos($catLower, 'bass') !== false) { $recoSlugs[] = 'the-lucky-bobber'; $recoSlugs[] = 'the-keeper-gauge'; }
          if (strpos($catLower, 'kayak') !== false || strpos($catLower,'boat') !== false || strpos($titleLower,'boat') !== false) {
            $recoSlugs[] = 'the-command-station'; $recoSlugs[] = 'the-bucket-station';
          }
          // Fallback to include all if empty
          if (empty($recoSlugs)) { $recoSlugs = array_keys($allProducts); }
          // De-duplicate and cap to 4
          $recoSlugs = array_values(array_unique($recoSlugs));
          $recoSlugs = array_slice($recoSlugs, 0, 4);
          $reco = array_values(array_filter(array_map(function($s) use ($allProducts){ return $allProducts[$s] ?? null; }, $recoSlugs)));
        ?>

        <?php if (!empty($reco)): ?>
        <div class="relative">
          <button id="gear-prev" type="button" aria-label="Previous" class="absolute -left-4 top-1/2 -translate-y-1/2 bg-black text-white w-9 h-9 rounded-full z-10 shadow flex items-center justify-center">‹</button>
          <div class="overflow-hidden rounded-xl border relative">
            <div id="gear-track" class="flex transition-transform duration-300">
              <?php foreach ($reco as $p): $pslug = $p['slug']; $img = $p['images'][0] ?? '/images/placeholder.png'; $price = number_format((float)$p['price'],2); $url = '/product/' . urlencode($pslug); ?>
                <div class="min-w-full p-4 bg-white">
                  <a href="<?php echo $url; ?>" class="block mb-3">
                    <div class="w-full h-40 bg-white border rounded-xl flex items-center justify-center">
                      <img src="<?php echo htmlspecialchars((string)$img); ?>" alt="<?php echo htmlspecialchars((string)$p['name']); ?>" class="max-h-36 object-contain" />
                    </div>
                    <div class="mt-2">
                      <div class="font-semibold text-gray-900 truncate"><?php echo htmlspecialchars((string)$p['name']); ?></div>
                      <div class="text-gray-600 text-sm">$<?php echo $price; ?></div>
                    </div>
                  </a>
                  <div class="flex gap-2 mt-3">
                    <a href="<?php echo $url; ?>" class="flex-1 border border-black text-black rounded-full py-2 text-center">View</a>
                    <button
                      type="button"
                      class="addToCartBtn flex-1 bg-black text-white rounded-full py-2"
                      data-id="<?php echo htmlspecialchars((string)$p['id']); ?>"
                      data-slug="<?php echo htmlspecialchars((string)$pslug); ?>"
                      data-name="<?php echo htmlspecialchars((string)$p['name']); ?>"
                      data-price="<?php echo htmlspecialchars((string)$p['price']); ?>"
                      data-image="<?php echo htmlspecialchars((string)$img); ?>"
                      data-url="<?php echo htmlspecialchars((string)$url); ?>"
                      data-qty="1"
                    >Add</button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <button id="gear-next" type="button" aria-label="Next" class="absolute -right-4 top-1/2 -translate-y-1/2 bg-black text-white w-9 h-9 rounded-full z-10 shadow flex items-center justify-center">›</button>
        </div>
        <script>
          (function(){
            const track = document.getElementById('gear-track');
            if (!track) return;
            const slides = track.children;
            let idx = 0;
            function update(){ track.style.transform = 'translateX(' + (-idx*100) + '%)'; }
            const prevBtn = document.getElementById('gear-prev');
            const nextBtn = document.getElementById('gear-next');
            prevBtn?.addEventListener('click', (e)=>{ e.preventDefault(); e.stopPropagation(); idx = (idx - 1 + slides.length) % slides.length; update(); });
            nextBtn?.addEventListener('click', (e)=>{ e.preventDefault(); e.stopPropagation(); idx = (idx + 1) % slides.length; update(); });
            // Hide arrows if only one slide
            const many = slides.length > 1;
            if (!many) { prevBtn?.classList.add('hidden'); nextBtn?.classList.add('hidden'); }
            // Keyboard support
            track.closest('div.relative')?.addEventListener('keydown', (ev)=>{
              if (ev.key === 'ArrowLeft') { idx = (idx - 1 + slides.length) % slides.length; update(); }
              if (ev.key === 'ArrowRight') { idx = (idx + 1) % slides.length; update(); }
            });
            // Ensure container can receive focus
            const container = track.closest('div.relative');
            if (container) container.setAttribute('tabindex','0');
          })();
        </script>
        <?php else: ?>
          <p class="text-sm text-gray-600">Gear recommendations coming soon.</p>
        <?php endif; ?>
      </div>
    </aside>
  </div>

  <section class="mt-10">
    <h2 class="text-xl font-bold mb-3">More from <?php echo htmlspecialchars((string)$category); ?></h2>
    <div class="grid md:grid-cols-3 gap-4">
      <?php $i=0; foreach (($catalog[$category] ?? []) as $t) { if ($i>=6) break; $s = strtolower(trim(preg_replace('/[^a-z0-9]+/i','-', $t), '-')); ?>
        <a class="block border rounded-xl p-4 bg-white hover:shadow" href="/guides/<?php echo $s; ?>"><?php echo htmlspecialchars((string)$t); ?></a>
      <?php $i++; } ?>
    </div>
  </section>
</main>
<?php include 'footer.php'; ?>
</body>
</html>


