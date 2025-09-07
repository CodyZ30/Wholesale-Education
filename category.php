<?php
include_once __DIR__ . '/includes/config.php';
include 'includes/header.php';

$catalog = require __DIR__ . '/data/products.php';

function gf_slugify($s){ return strtolower(trim(preg_replace('/[^a-z0-9]+/i','-', (string)$s), '-')); }

// Resolve category slug from /category/<slug> or ?slug=
$slug = isset($_GET['slug']) ? strtolower(trim((string)$_GET['slug'])) : '';
if ($slug === '') {
  $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
  // Normalize trailing slash
  if (substr($path, -1) === '/') { $path = rtrim($path, '/'); }
  // Expect /category/<slug>
  if (stripos($path, '/category/') === 0) {
    $slug = strtolower(trim(substr($path, strlen('/category/'))));
  }
}

// Map slug -> canonical category name present in products
$categoryName = '';
$catSet = [];
foreach ($catalog as $p) {
  if (!empty($p['category'])) { $catSet[(string)$p['category']] = true; }
}
foreach (array_keys($catSet) as $cand) {
  if (gf_slugify($cand) === $slug) { $categoryName = (string)$cand; break; }
}
if ($categoryName === '' && $slug !== '') { $categoryName = ucwords(str_replace('-', ' ', $slug)); }

// Filter products by category
$items = array_filter($catalog, function($p) use ($categoryName){
  return !empty($p['category']) && strcasecmp((string)$p['category'], (string)$categoryName) === 0;
});

$pageTitle = ($categoryName ? $categoryName . ' — ' : '') . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars((string)$pageTitle); ?></title>
  <meta name="description" content="Explore <?php echo htmlspecialchars((string)$categoryName); ?> at <?php echo SITE_NAME; ?> — specs, buying guides, tips, and our recommended gear." />
  <script type="application/ld+json">
  <?php
    $list = array_values(array_map(function($p){
      return [
        '@type' => 'ListItem',
        'name' => $p['name'] ?? 'Product',
        'url' => '/product/' . ($p['slug'] ?? ''),
      ];
    }, $items));
    echo json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'ItemList',
      'itemListElement' => $list,
    ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
  ?>
  </script>
</head>
<body>
<main class="container mx-auto px-4 md:px-6 py-10">
  <nav class="text-sm text-gray-500 mb-6"><a class="hover:underline" href="/shop">Shop</a> / <span><?php echo htmlspecialchars((string)$categoryName); ?></span></nav>

  <header class="mb-8">
    <h1 class="text-3xl md:text-4xl font-extrabold mb-3"><?php echo htmlspecialchars((string)$categoryName); ?></h1>
    <p class="text-gray-700 max-w-3xl">Your definitive resource for <?php echo htmlspecialchars((string)$categoryName); ?>: what to look for, how to choose, and which products perform best for different anglers and conditions. Curated by <?php echo SITE_NAME; ?>.</p>
  </header>

  <section class="grid md:grid-cols-3 gap-8 mb-10">
    <div class="md:col-span-2 space-y-6">
      <article class="bg-white rounded-2xl border shadow p-6">
        <h2 class="text-xl font-bold mb-2">Buying Guide</h2>
        <p class="text-gray-700">When selecting <?php echo htmlspecialchars((string)$categoryName); ?>, focus on materials, durability, and how the gear pairs with your typical water and target species. Weight and ergonomics matter over long days on the water.</p>
        <ul class="list-disc list-inside text-gray-700 mt-2">
          <li>Materials and corrosion resistance</li>
          <li>Fit with your boat/kayak/shore setup</li>
          <li>Storage footprint and portability</li>
          <li>Value over time versus one‑season gear</li>
        </ul>
      </article>

      <article class="bg-white rounded-2xl border shadow p-6">
        <h2 class="text-xl font-bold mb-2">Use Cases</h2>
        <p class="text-gray-700">Match the product to the job: organization on the boat, quick measurements at the pier, or durable accessories that live in your tackle bag.</p>
        <div class="grid md:grid-cols-2 gap-3 mt-3 text-gray-700">
          <div class="p-3 rounded-xl border">Boat days: maximize deck time and reduce clutter.</div>
          <div class="p-3 rounded-xl border">Shore missions: compact tools that pack fast.</div>
          <div class="p-3 rounded-xl border">Cold weather: gloves‑friendly operation.</div>
          <div class="p-3 rounded-xl border">Travel: multi‑use gear with small footprint.</div>
        </div>
      </article>

      <article class="bg-white rounded-2xl border shadow p-6">
        <h2 class="text-xl font-bold mb-2">FAQs</h2>
        <details class="mb-2"><summary class="font-semibold">What makes great <?php echo htmlspecialchars((string)$categoryName); ?>?</summary><p class="text-gray-700 mt-1">Look for proven materials, reliable hardware, and a design that simplifies your day on the water instead of adding fuss.</p></details>
        <details class="mb-2"><summary class="font-semibold">How do I maintain it?</summary><p class="text-gray-700 mt-1">Rinse salt, dry thoroughly, and store out of direct sun. Tighten fasteners periodically.</p></details>
        <details><summary class="font-semibold">Will this fit my current setup?</summary><p class="text-gray-700 mt-1">Check measurements against your deck, rail, or bucket size. Our product pages include key dimensions.</p></details>
      </article>
    </div>

    <aside>
      <div class="bg-white rounded-2xl border shadow p-6">
        <h2 class="text-lg font-bold mb-3">Editor’s Picks</h2>
        <?php $top = array_slice(array_values($items), 0, 3); if (!empty($top)): ?>
          <div class="space-y-4">
            <?php foreach ($top as $p): $img = $p['images'][0] ?? '/images/placeholder.png'; $url = '/product/' . urlencode((string)$p['slug']); ?>
              <div class="flex gap-3 items-center">
                <div class="w-16 h-16 border rounded-xl bg-white flex items-center justify-center"><img src="<?php echo htmlspecialchars((string)$img); ?>" alt="" class="max-h-14 object-contain"></div>
                <div class="min-w-0 flex-1">
                  <a href="<?php echo $url; ?>" class="font-semibold truncate"><?php echo htmlspecialchars((string)$p['name']); ?></a>
                  <div class="text-sm text-gray-600">$<?php echo number_format((float)$p['price'],2); ?></div>
                </div>
                <button
                  type="button"
                  class="addToCartBtn bg-black text-white rounded-full px-3 py-1 text-sm"
                  data-id="<?php echo htmlspecialchars((string)$p['id']); ?>"
                  data-slug="<?php echo htmlspecialchars((string)$p['slug']); ?>"
                  data-name="<?php echo htmlspecialchars((string)$p['name']); ?>"
                  data-price="<?php echo htmlspecialchars((string)$p['price']); ?>"
                  data-image="<?php echo htmlspecialchars((string)$img); ?>"
                  data-url="<?php echo htmlspecialchars((string)$url); ?>"
                  data-qty="1"
                >Add</button>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-sm text-gray-600">Picks will appear when products are available.</p>
        <?php endif; ?>
      </div>
    </aside>
  </section>

  <section>
    <h2 class="text-2xl font-bold mb-4">All <?php echo htmlspecialchars((string)$categoryName); ?></h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($items as $p): $slugP = $p['slug']; $img = $p['images'][0] ?? '/images/placeholder.png'; $url = '/product/' . urlencode((string)$slugP); ?>
        <div class="border rounded-2xl bg-white shadow-sm p-4">
          <a href="<?php echo $url; ?>" class="block">
            <div class="w-full h-44 bg-white border rounded-xl flex items-center justify-center">
              <img src="<?php echo htmlspecialchars((string)$img); ?>" alt="<?php echo htmlspecialchars((string)$p['name']); ?>" class="max-h-40 object-contain" />
            </div>
            <div class="mt-3">
              <div class="font-semibold text-lg truncate"><?php echo htmlspecialchars((string)$p['name']); ?></div>
              <div class="text-gray-600 text-sm line-clamp-2"><?php echo htmlspecialchars((string)($p['description'] ?? '')); ?></div>
              <div class="mt-1 font-bold">$<?php echo number_format((float)$p['price'],2); ?></div>
            </div>
          </a>
          <div class="mt-3 flex gap-2">
            <a href="<?php echo $url; ?>" class="flex-1 border border-black text-black rounded-full py-2 text-center">View</a>
            <button
              type="button"
              class="addToCartBtn flex-1 bg-black text-white rounded-full py-2"
              data-id="<?php echo htmlspecialchars((string)$p['id']); ?>"
              data-slug="<?php echo htmlspecialchars((string)$slugP); ?>"
              data-name="<?php echo htmlspecialchars((string)$p['name']); ?>"
              data-price="<?php echo htmlspecialchars((string)$p['price']); ?>"
              data-image="<?php echo htmlspecialchars((string)$img); ?>"
              data-url="<?php echo htmlspecialchars((string)$url); ?>"
              data-qty="1"
            >Add to Cart</button>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($items)): ?>
        <div class="text-gray-600">No products found for this category yet.</div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php include 'footer.php'; ?>
</body>
</html>


