<?php
// Build category list from catalog for dropdown
$catalog = [];
try { $catalog = require dirname(__DIR__) . '/data/products.php'; } catch(Throwable $e) { $catalog = []; }
$catSet = [];
foreach ($catalog as $p) {
  if (!empty($p['category'])) { $catSet[(string)$p['category']] = true; }
}
$categories = array_keys($catSet); sort($categories);
?>

<ul class="gf-nav hidden md:flex items-center gap-6">
  <li><a href="/" class="hover:underline">Home</a></li>

  <!-- Shop mega dropdown -->
  <li class="relative group">
    <a href="/shop" class="hover:underline">Shop</a>
    <div class="menu-panel absolute left-0 top-full mt-0 w-[760px] p-4 bg-white border rounded-xl shadow-2xl opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition">
      <div class="grid grid-cols-3 gap-6">
        <div>
          <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Categories</div>
          <?php
            // SEO-focused collections requested by user
            $seoCollections = [
              'Shirts' => 'shirts',
              'Hats' => 'hats',
              'Sweatshirts' => 'sweatshirts',
              'Bobbers' => 'fishing-bobbers',
              'Dehookers' => 'dehookers',
              'Measuring Tools' => 'fishing-measuring-tools',
              'Bucket Attachments' => 'bucket-attachments',
              'Fishing Stands' => 'fishing-stands',
            ];
            // Build union of dynamic categories + SEO collections
            $unionLabels = $categories;
            foreach (array_keys($seoCollections) as $label) {
              if (!in_array($label, $unionLabels, true)) { $unionLabels[] = $label; }
            }
            // Helper to slugify labels when not in SEO map
            $slugify = function($s){ return strtolower(trim(preg_replace('/[^a-z0-9]+/i','-', (string)$s), '-')); };
          ?>
          <ul class="space-y-1">
            <?php foreach ($unionLabels as $label):
              $slug = $seoCollections[$label] ?? $slugify($label);
            ?>
              <li><a class="block px-2 py-1 rounded hover:bg-gray-100" href="/category/<?= urlencode($slug) ?>"><?= htmlspecialchars($label) ?></a></li>
            <?php endforeach; ?>
            <?php if (empty($unionLabels)): ?>
              <li class="px-2 py-1 text-gray-500">Coming soon</li>
            <?php endif; ?>
          </ul>
        </div>
        <div>
          <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Featured</div>
          <ul class="space-y-1">
            <li><a class="block px-2 py-1 rounded hover:bg-gray-100" href="/product/the-keeper-gauge">The Keeper Gauge</a></li>
            <li><a class="block px-2 py-1 rounded hover:bg-gray-100" href="/product/the-bucket-station">The Bucket Station</a></li>
            <li><a class="block px-2 py-1 rounded hover:bg-gray-100" href="/product/the-lucky-bobber">The Lucky Bobber</a></li>
            <li><a class="block px-2 py-1 rounded hover:bg-gray-100" href="/product/the-command-station">The Command Station</a></li>
          </ul>
        </div>
        <div>
          <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Coming Soon</div>
          <ul class="space-y-1">
            <li><a class="block px-2 py-1 rounded hover:bg-gray-100" href="/roadmap">Roadmap</a></li>
            <li><a class="block px-2 py-1 rounded hover:bg-gray-100" href="/pro-team">Pro Team</a></li>
            <li><a class="block px-2 py-1 rounded hover:bg-gray-100" href="/guides">Guides & Tips</a></li>
            <li><a class="block px-2 py-1 rounded hover:bg-gray-100" href="/warranty">Warranty</a></li>
            <li><a class="block px-2 py-1 rounded hover:bg-gray-100" href="/contact">Contact</a></li>
          </ul>
          </div>
      </div>
    </div>
  </li>

  <li><a href="/stickers" class="hover:underline">Stickers</a></li>
  <li><a href="/guides" class="hover:underline">Guides</a></li>
  <li><a href="/about" class="hover:underline">About</a></li>
  <li><a href="/support" class="hover:underline">Support</a></li>
</ul>
