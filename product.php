<?php
// product.php

declare(strict_types=1);

// ---------- Bootstrap & Helpers ----------
session_start();
// Ensure globals (SITE_NAME) and error display are available early
require_once __DIR__ . '/includes/config.php';

// Small helpers
function safe($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function currency($n): string { return '$' . number_format((float)$n, 2); }
function boolval_like($v): bool { return filter_var($v, FILTER_VALIDATE_BOOLEAN) ?? false; }
function request_path(): string {
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $uri    = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
  return "{$scheme}://{$host}{$uri}";
}
function canonical_url(string $slug): string {
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
  return "{$scheme}://{$host}/product.php?slug=" . urlencode($slug);
}
function get_currency_code(): string { return 'USD'; }
function fish_badge_url(): string { return '/images/fish.png'; }

// CSRF token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION['csrf_token'];

// ---------- Load Catalog & Resolve Product ----------
$products = require __DIR__ . '/data/products.php';

// Find slug safely
$slug = $_GET['slug'] ?? array_key_first($products);
$slug = is_string($slug) ? $slug : array_key_first($products);

// Resolve product or graceful fallback
$product = $products[$slug] ?? null;
if (!$product) {
  // 404-like graceful fallback: show first product and message
  $fallbackSlug = array_key_first($products);
  $product = $products[$fallbackSlug];
  $slug = $fallbackSlug;
  $missing_notice = 'Product not found. Showing a featured item instead.';
}

// Normalize: ensure slug present
if (!isset($product['slug'])) { $product['slug'] = $slug; }

// ---------- Images (filter placeholder/black) ----------
$rawImages = $product['images'] ?? [];
$images = array_values(array_filter($rawImages, function ($src) {
  return stripos($src, 'placeholder') === false && stripos($src, 'black') === false;
}));
if (empty($images) && !empty($rawImages)) { $images = $rawImages; }
$mainImg = $images[0] ?? '/images/placeholder.png';

// ---------- Recommended (simple shuffle, exclude current) ----------
$recommended = $products;
unset($recommended[$product['slug']]);
$recommended = array_values($recommended);
shuffle($recommended);
$recommended = array_slice($recommended, 0, 3);

// ---------- Reviews (local JSON store) ----------
$reviewsFile = __DIR__ . '/reviews.json';
$allReviews = file_exists($reviewsFile) ? json_decode((string)file_get_contents($reviewsFile), true) : [];
if (!is_array($allReviews)) $allReviews = [];
$productReviews = $allReviews[$product['slug']] ?? [];

// Compute rating summary
$total = 0; $count = 0;
foreach ($productReviews as $r) {
  $rt = (int)($r['rating'] ?? 0);
  if ($rt >= 1 && $rt <= 5) { $total += $rt; $count++; }
}
$avgRating = $count ? round($total / $count, 1) : null;

// ---------- Handle New Review POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Basic spam honeypot & CSRF
  $hp = $_POST['website'] ?? ''; // honeypot
  $csrf = $_POST['csrf_token'] ?? '';
  if (hash_equals($csrf_token, $csrf) && $hp === '' && isset($_POST['author'], $_POST['rating'], $_POST['text'])) {
    $author = trim((string)$_POST['author']);
    $text = trim((string)$_POST['text']);
    $rating = (int)$_POST['rating'];

    // Validate
    if ($author !== '' && $text !== '' && $rating >= 1 && $rating <= 5) {
      $newReview = [
        'author' => safe($author),
        'rating' => max(1, min(5, $rating)),
        'text'   => safe($text),
        'date'   => date('F j, Y')
      ];
      $productReviews[] = $newReview;
      $allReviews[$product['slug']] = $productReviews;
      file_put_contents($reviewsFile, json_encode($allReviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
      header('Location: ' . canonical_url($product['slug']));
      exit;
    }
  }
}

// ---------- Availability (supports: stock_status, in_stock, stock [number or text]) ----------
$inStock = true;
$numericStock = null; // keep numeric count if provided

if (isset($product['stock_status'])) {
  // Accept text like "in", "in stock", "available", "out", "out of stock"
  $status = strtolower(trim((string)$product['stock_status']));
  $inStock = in_array($status, ['in', 'in stock', 'available', 'yes', 'true', '1'], true);
} elseif (array_key_exists('in_stock', $product)) {
  $inStock = filter_var($product['in_stock'], FILTER_VALIDATE_BOOLEAN);
} elseif (array_key_exists('stock', $product)) {
  $sv = $product['stock'];
  if (is_numeric($sv)) {
    $numericStock = (int)$sv;
    $inStock = ($numericStock > 0);
  } else {
    $status = strtolower(trim((string)$sv));
    $inStock = in_array($status, ['in', 'in stock', 'available', 'yes', 'true', '1'], true);
  }
}

// Low-stock note only if numeric quantity was provided
$lowStockNote = ($numericStock !== null && $numericStock > 0 && $numericStock <= 5)
  ? ('Only ' . $numericStock . ' left — order soon!')
  : '';

$stockLabel = $inStock ? 'In stock' : 'Out of stock';
$stockColorClass = $inStock ? 'bg-green-500' : 'bg-red-500';


// ---------- SEO & Meta ----------
$canonical = canonical_url($product['slug']);
$metaTitle = ($product['name'] ?? 'Product') . ' - ' . SITE_NAME;
$metaDesc = trim((string)($product['description'] ?? ('Premium fishing gear by ' . SITE_NAME . '.')));
$metaDesc = mb_substr($metaDesc, 0, 160);

// ---------- JSON-LD (Product + Breadcrumbs) ----------
$brand = $product['brand'] ?? SITE_NAME;
$sku   = $product['id'] ?? ($product['slug'] ?? '');
$offers = [
  '@type' => 'Offer',
  'priceCurrency' => get_currency_code(),
  'price' => (string)($product['price'] ?? 0),
  'availability' => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
  'url' => $canonical,
];
$jsonLd = [
  '@context' => 'https://schema.org',
  '@type' => 'Product',
  'name' => $product['name'] ?? '',
  'image' => $images ?: [$mainImg],
  'description' => $metaDesc,
  'sku' => $sku,
  'brand' => ['@type' => 'Brand', 'name' => $brand],
  'offers' => $offers,
];
if ($count > 0 && $avgRating !== null) {
  $jsonLd['aggregateRating'] = [
    '@type' => 'AggregateRating',
    'ratingValue' => (string)$avgRating,
    'reviewCount' => (string)$count
  ];
}
if (!empty($productReviews)) {
  $jsonLd['review'] = array_map(function ($r) {
    return [
      '@type' => 'Review',
      'author' => ['@type' => 'Person', 'name' => $r['author'] ?? 'Customer'],
      'datePublished' => date('Y-m-d', strtotime($r['date'] ?? 'now')),
      'reviewBody' => $r['text'] ?? '',
      'reviewRating' => ['@type' => 'Rating', 'ratingValue' => (string)($r['rating'] ?? 0), 'bestRating' => '5', 'worstRating' => '1'],
    ];
  }, $productReviews);
}
$breadcrumbsLd = [
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
    ['@type' => 'ListItem','position' => 1,'name' => 'Home','item' => (function(){ $s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'; $h = $_SERVER['HTTP_HOST'] ?? 'localhost'; return "{$s}://{$h}/"; })()],
    ['@type' => 'ListItem','position' => 2,'name' => 'Shop','item' => (function(){ $s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'; $h = $_SERVER['HTTP_HOST'] ?? 'localhost'; return "{$s}://{$h}/shop"; })()],
    ['@type' => 'ListItem','position' => 3,'name' => $product['name'] ?? 'Product','item' => $canonical],
  ],
];

// ---------- Recently Viewed (session-based) ----------
$_SESSION['recently_viewed'] = $_SESSION['recently_viewed'] ?? [];
// Remove if already there, then unshift current, cap to 12
$_SESSION['recently_viewed'] = array_values(array_filter($_SESSION['recently_viewed'], fn($s) => $s !== $slug));
array_unshift($_SESSION['recently_viewed'], $slug);
$_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 12);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= safe($metaTitle); ?></title>
<link rel="canonical" href="<?= safe($canonical); ?>" />
<meta name="description" content="<?= safe($metaDesc); ?>" />

<!-- Performance hints -->
<link rel="preload" as="image" href="<?= safe($mainImg); ?>">
<link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>

<!-- Open Graph -->
<meta property="og:type" content="product" />
<meta property="og:title" content="<?= safe($product['name'] ?? 'Product'); ?>" />
<meta property="og:description" content="<?= safe($metaDesc); ?>" />
<meta property="og:image" content="<?= safe($mainImg); ?>" />
<meta property="og:url" content="<?= safe($canonical); ?>" />
<meta property="product:price:amount" content="<?= safe($product['price'] ?? 0); ?>" />
<meta property="product:price:currency" content="<?= safe(get_currency_code()); ?>" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="<?= safe($product['name'] ?? 'Product'); ?>" />
<meta name="twitter:description" content="<?= safe($metaDesc); ?>" />
<meta name="twitter:image" content="<?= safe($mainImg); ?>" />

<!-- JSON-LD -->
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?></script>
<script type="application/ld+json"><?= json_encode($breadcrumbsLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?></script>

<!-- Tailwind + Cart API -->
<script src="https://cdn.tailwindcss.com"></script>
<script src="/assets/cart.js" defer></script>

<!-- One-time cart bootstrap (de-dupe + mini-cart + ATC + toast) -->
<script defer>
(function () {
  if (window.__GF_BOOTSTRAP) return; window.__GF_BOOTSTRAP = true;

  // Block UI double click/touch
  const LAST_TS = new WeakMap(); const UI_MS = 700;
  function maybeBlock(e){ const btn=e.target.closest('.addToCartBtn'); if(!btn) return; const n=Date.now(), l=LAST_TS.get(btn)||0; if(n-l<UI_MS){ e.stopImmediatePropagation(); e.stopPropagation(); e.preventDefault(); return; } LAST_TS.set(btn,n); }
  ['pointerup','touchend','click'].forEach(t=>document.addEventListener(t, maybeBlock, true));

  // Drop identical Cart.add calls within 600ms
  function patchCartAdd(){ const C=window.Cart; if(!C||C.__dedupPatched) return; C.__dedupPatched=true; const orig=C.add?.bind(C); if(!orig) return;
    let lastKey='', lastTs=0; const MS=600;
    C.add=function(item){
      try{ const k=[item?.id??'',item?.slug??'',item?.price??'',item?.qty??1, JSON.stringify(item?.options??{})].join('|'); const n=Date.now(); if(k===lastKey && (n-lastTs)<MS) return; lastKey=k; lastTs=n; }catch(e){}
      return orig(item);
    };
  }

  // Mini-cart
  function wireMiniCart(){
    if (window.__GF_MINICART) return; window.__GF_MINICART = true;
    const $=s=>document.querySelector(s);
    const btn=$('#cart-button'), panel=$('#mini-cart-panel'), overlay=$('#mini-cart-overlay'), closeBtn=$('#mini-cart-close'), itemsEl=$('#mini-cart-items'), subtotalEl=$('#mini-cart-subtotal'), countEl=$('#cart-count');
    const currency=n=>`$${Number(n||0).toFixed(2)}`; const isMobile=()=>matchMedia('(max-width: 768px)').matches;
    function lockScroll(l){ document.documentElement.style.overflow=l?'hidden':''; document.body.style.overflow=l?'hidden':''; }
    function open(){ panel?.classList.remove('hidden'); btn?.setAttribute('aria-expanded','true'); if(isMobile()){ overlay?.classList.add('show'); lockScroll(true);} render(); }
    function close(){ panel?.classList.add('hidden'); btn?.setAttribute('aria-expanded','false'); overlay?.classList.remove('show'); lockScroll(false); }
    function render(){
      const cart=(window.Cart?.get()||[]);
      if(!itemsEl||!subtotalEl) return;
      if(!cart.length){ itemsEl.innerHTML=`<div class="p-6 text-center text-gray-500">Your cart is empty.</div>`; subtotalEl.textContent=currency(0); return; }
      itemsEl.innerHTML=cart.map(it=>`
        <div class="p-4 flex items-center gap-3">
          <div class="w-16 h-16 rounded-xl border flex items-center justify-center bg-white overflow-hidden">
            <img src="${it.image||'/images/placeholder.png'}" alt="" class="max-w-[90%] max-h-[90%] object-contain">
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-medium truncate">${it.name||'Item'}</div>
            <div class="text-sm text-gray-500">
              ${currency(it.price)} • Qty
              <input type="number" min="1" value="${it.qty}" data-mini-qty="${it.id}" class="w-14 ml-1 border rounded text-center">
            </div>
          </div>
          <div class="text-right">
            <div class="font-semibold">${currency(Number(it.price)*Number(it.qty))}</div>
            <button class="text-xs text-red-500 hover:underline mt-1" data-mini-remove="${it.id}">Remove</button>
          </div>
        </div>`).join('');
      subtotalEl.textContent=currency(window.Cart?.subtotal()||0);
    }
    panel?.addEventListener('click', e=>{ const a=e.target.closest('a.view-bag, a.checkout'); if(a) close(); });
    function badge(){ if(countEl) countEl.textContent = window.Cart?.count?.()||0; }
    window.addEventListener('cart:updated', ()=>{ badge(); if(panel && !panel.classList.contains('hidden')) render(); });
    document.addEventListener('DOMContentLoaded', badge);
    btn?.addEventListener('click', ()=> panel?.classList.contains('hidden') ? open() : close());
    closeBtn?.addEventListener('click', close);
    overlay?.addEventListener('click', close);
    document.addEventListener('keydown', e=>{ if(e.key==='Escape') close(); });
    document.addEventListener('click', e=>{ if(panel && !panel.contains(e.target) && btn && !btn.contains(e.target) && !panel.classList.contains('hidden')){ if(!isMobile()) close(); } });
    itemsEl?.addEventListener('input', e=>{ const id=e.target.getAttribute('data-mini-qty'); if(!id) return; const v=parseInt(e.target.value,10); if(isNaN(v)||v<1) window.Cart.remove(id); else window.Cart.updateQty(id,v); });
    itemsEl?.addEventListener('click', e=>{ const r=e.target.closest('[data-mini-remove]']); if(r) window.Cart.remove(r.getAttribute('data-mini-remove')); });
    window.updateHeaderCart = function(){ window.dispatchEvent(new CustomEvent('cart:updated')); };
  }

  // Universal Add-to-Cart
  function wireATC(){
    if (window.__GF_ATC) return; window.__GF_ATC = true;
    document.querySelectorAll('button.addToCartBtn').forEach(b=>b.setAttribute('type','button'));
    document.addEventListener('click', (e)=>{
      const btn=e.target.closest('.addToCartBtn'); if(!btn) return; e.preventDefault();
      let qty = Number(btn.dataset.qty||1);
      const qs=btn.dataset.qtySelector; if(qs){ const el=document.querySelector(qs); const v=parseInt(el?.value,10); if(!isNaN(v)&&v>0) qty=v; }
      const item={ id:btn.dataset.id, slug:btn.dataset.slug, name:btn.dataset.name, price:Number(btn.dataset.price||0), image:btn.dataset.image||'', url:btn.dataset.url||'', qty };
      if (window.Cart?.add) window.Cart.add(item);
    });
  }

  // Toast
  function wireToast(){
    const root=document.getElementById('cart-toast'); const toast=root?.querySelector('.toast');
    const img=document.getElementById('cart-toast-img'); const desc=document.getElementById('cart-toast-desc'); if(!root||!toast) return;
    let timer=null;
    function show(item){
      img.src=item.image||'/images/placeholder.png'; img.alt=item.name||'Item';
      const price=Number(item.price||0), qty=Number(item.qty||1);
      desc.textContent=`${qty} × ${item.name||'Item'} • $${(price*qty).toFixed(2)}`;
      root.style.display='block'; requestAnimationFrame(()=>toast.classList.add('show'));
      clearTimeout(timer); timer=setTimeout(()=>{ toast.classList.remove('show'); setTimeout(()=> root.style.display='none', 280); }, 2300);
    }
    window.addEventListener('cart:added', e=>show(e.detail?.item||{}));
  }

  function init(){ patchCartAdd(); wireMiniCart(); wireATC(); wireToast(); }
  if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', init, {once:true}); } else { init(); }
})();
</script>

<style>
/* Header bits */
.top-bar{background:#00a651;color:#fff;font-size:.8rem;padding:.25rem 1rem;text-align:center;}
header.gf-header{background:#fff;color:#333;border-bottom:4px solid #00a651;z-index:50;}
.gf-logo img{height:60px;width:auto;}
.gf-nav a{color:#333;font-weight:600;transition:transform .15s;}
.gf-nav a:hover{transform:scale(1.05);}
.cart-button{background:#000;color:#fff;border-radius:9999px;padding:.45rem .9rem;font-weight:700;display:inline-flex;align-items:center;gap:.4rem;position:relative;}
.cart-button:hover{background:#008f46;}
.cart-count{background:#fff;color:#000;font-size:.75rem;font-weight:800;padding:.12rem .38rem;border-radius:9999px;line-height:1;position:absolute;top:-.25rem;right:-.25rem;}
#mini-cart-panel{display:none;position:absolute;right:0;top:100%;margin-top:.5rem;width:24rem;background:#fff;border-radius:1rem;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,.1);transform:translateY(-10px);opacity:0;transition:transform .25s,opacity .25s;z-index:60;}
#mini-cart-panel:not(.hidden){display:block;transform:translateY(0);opacity:1;}
#mini-cart-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:55;opacity:0;transition:opacity .2s;}
#mini-cart-overlay.show{display:block;opacity:1;}
@media (max-width:768px){
  #mini-cart-panel{position:fixed;inset:0;margin:0;width:100vw;height:100vh;max-height:100vh;border-radius:0;border:none;box-shadow:none;transform:none;opacity:1;display:none;overflow:auto;}
  #mini-cart-panel:not(.hidden){display:block;}
}

/* Page visuals */
body{font-family:Inter,system-ui,Arial;background:#f8fafc;}
.thumbnail-image.selected{border:2px solid #333;opacity:1;}

/* Toast */
#cart-toast{position:fixed;top:1rem;right:1rem;z-index:70;display:none;}
#cart-toast .toast{min-width:320px;max-width:90vw;background:#111;color:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.25);padding:14px 16px;display:flex;gap:12px;align-items:center;transform:translateY(-12px);opacity:0;transition:transform .28s,opacity .28s;}
#cart-toast .toast.show{transform:translateY(0);opacity:1;}
#cart-toast .thumb{width:48px;height:48px;border-radius:10px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;}
#cart-toast .thumb img{width:100%;height:100%;object-fit:contain;}

/* Badges */
.badge{font-size:.75rem;border-radius:9999px;padding:.25rem .5rem;border:1px solid #e5e7eb;display:inline-flex;align-items:center;gap:.35rem;}
.badge-dot{width:.5rem;height:.5rem;border-radius:9999px;display:inline-block;}
.fish-badge{display:inline-flex;align-items:center;gap:.35rem;border:1px dashed #d1fae5;background:#ecfdf5;color:#065f46;padding:.25rem .6rem;border-radius:9999px;font-weight:700}
.fish-badge img{width:18px;height:18px;object-fit:contain}

/* Hero fish ribbon */
.fish-ribbon{position:absolute;top:.75rem;left:.75rem;background:rgba(255,255,255,.9);border:1px solid #e5e7eb;border-radius:9999px;padding:.35rem .6rem;display:flex;align-items:center;gap:.4rem;box-shadow:0 6px 18px rgba(0,0,0,.08);}
.fish-ribbon img{width:22px;height:22px}

/* Image zoom */
.zoomable{cursor:zoom-in;transition:transform .2s;}
.zoomable:hover{transform:scale(1.01);}

/* Sticky bar */
.sticky-atc{position:fixed;left:0;right:0;bottom:-120px;transition:bottom .25s ease;z-index:60}
.sticky-atc.show{bottom:0}

/* Accordions */
.acc{border:1px solid #e5e7eb;border-radius:1rem;overflow:hidden}
.acc summary{padding:1rem 1.25rem;cursor:pointer;font-weight:700;background:#fff}
.acc .acc-body{padding:1rem 1.25rem;background:#fafafa;border-top:1px solid #e5e7eb}

/* Section tabs (anchors) */
.section-nav a{padding:.35rem .6rem;border-radius:.6rem}
.section-nav a.active{background:#111;color:#fff}

/* Recommended card ribbon */
.card-ribbon{position:absolute;top:.5rem;left:.5rem;background:#fff;border:1px solid #e5e7eb;border-radius:9999px;padding:.15rem .45rem;display:flex;align-items:center;gap:.3rem;font-size:.7rem;font-weight:700}
.card-ribbon img{width:14px;height:14px}

/* Low stock */
.low-stock{background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;border-radius:.75rem;padding:.5rem .75rem;font-size:.9rem;font-weight:600}

/* Stock symbol (prominent round indicator) */
.stock-symbol{width:12px;height:12px;border-radius:9999px;display:inline-block;border:1px solid #e5e7eb}
</style>
</head>
<body class="text-gray-800 antialiased">

<?php include 'includes/header.php'; ?>

<main class="container mx-auto px-4 md:px-6 py-8 md:py-12">

  <!-- Breadcrumbs -->
  <nav class="text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
    <ol class="list-reset inline-flex items-center gap-2">
      <li><a class="hover:underline" href="/">Home</a></li>
      <li>/</li>
      <li><a class="hover:underline" href="/shop">Shop</a></li>
      <li>/</li>
      <li aria-current="page" class="text-gray-800 font-medium"><?= safe($product['name']); ?></li>
    </ol>
  </nav>

  <?php if (!empty($missing_notice)): ?>
    <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 text-amber-900 p-4"><?= safe($missing_notice) ?></div>
  <?php endif; ?>

  <!-- Section quick-nav -->
  <div class="section-nav flex flex-wrap gap-2 mb-6 text-sm">
    <a href="#details" class="hover:bg-gray-200">Details</a>
    <?php if (!empty($product['specs'])): ?><a href="#specs" class="hover:bg-gray-200">Specs</a><?php endif; ?>
    <a href="#reviews" class="hover:bg-gray-200">Reviews</a>
    <a href="#shipping" class="hover:bg-gray-200">Shipping & Returns</a>
    <a href="#faq" class="hover:bg-gray-200">FAQ</a>
  </div>

  <section class="mb-16 grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-16">
    <!-- Thumbs + main image -->
    <div class="flex flex-col-reverse md:flex-row gap-4 items-start">
      <div class="flex flex-row justify-center space-x-2 w-full md:flex-col md:w-20 md:space-x-0 md:space-y-3">
        <?php foreach ($images as $i => $img): ?>
          <button type="button" class="block border rounded-xl overflow-hidden <?= $i===0?'ring-2 ring-black':'' ?>" aria-label="Select image <?= (int)$i+1 ?>">
            <img src="<?= safe($img) ?>" class="block w-full h-20 md:h-24 object-contain" data-full-size-url="<?= safe($img) ?>" alt="Thumb <?= (int)$i+1 ?>">
          </button>
        <?php endforeach; ?>
      </div>
      <div class="relative flex-grow flex items-center justify-center">
        <div class="fish-ribbon" title="<?php echo SITE_NAME; ?> Original">
          <img src="<?= safe(fish_badge_url()) ?>" alt="Fish logo">
          <span class="font-semibold text-sm"><?php echo SITE_NAME; ?></span>
        </div>
        <div class="w-full max-w-[600px] h-[500px] md:h-[600px] bg-white rounded-2xl shadow-xl border border-gray-100 flex items-center justify-center overflow-hidden">
          <img id="main-product-image" src="<?= safe($mainImg) ?>" alt="<?= safe($product['name']) ?> image" class="zoomable block w-full h-full object-contain">
        </div>
      </div>
    </div>

    <!-- Info -->
    <div class="lg:sticky lg:top-24 self-start text-center md:text-left">
      <!-- Badge row including fish -->
      <div class="flex flex-wrap items-center gap-2 mb-3">
        <span class="fish-badge"><img src="<?= safe(fish_badge_url()) ?>" alt="">Official <?php echo SITE_NAME; ?></span>
        <?php if (!empty($product['brand'])): ?>
          <span class="badge">Brand: <?= safe($product['brand']) ?></span>
        <?php endif; ?>
        <span class="badge">30-Day Returns</span>
        <span class="badge">Secure Checkout</span>
      </div>

      <h1 class="text-3xl md:text-4xl font-extrabold mb-1"><?= safe($product['name']); ?></h1>

      <!-- Rating summary -->
      <div class="flex items-center justify-center md:justify-start gap-3 mb-3">
        <?php if ($avgRating !== null): ?>
          <div class="flex items-center gap-1" aria-label="Average rating <?= safe((string)$avgRating) ?> out of 5">
            <?php
              $full = floor($avgRating);
              $half = ($avgRating - $full) >= 0.5 ? 1 : 0;
              $empty = 5 - $full - $half;
              echo str_repeat('<span class="text-yellow-400" aria-hidden="true">★</span>', $full);
              echo str_repeat('<span class="text-yellow-400" aria-hidden="true">☆</span>', $half);
              echo str_repeat('<span class="text-gray-300" aria-hidden="true">☆</span>', $empty);
            ?>
          </div>
          <div class="text-sm text-gray-600">(<?= safe((string)$avgRating) ?>/5 • <?= (int)$count ?> review<?= $count===1?'':'s' ?>)</div>
        <?php else: ?>
          <div class="text-sm text-gray-500">No reviews yet</div>
        <?php endif; ?>
      </div>

      <div class="flex items-center gap-3 mb-2">
        <span class="text-3xl font-bold text-gray-900" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
          <meta itemprop="priceCurrency" content="<?= safe(get_currency_code()) ?>">
          <span itemprop="price"><?= currency($product['price'] ?? 0) ?></span>
        </span>
        <span class="badge" aria-label="<?= safe($stockLabel) ?>">
          <span class="badge-dot <?= safe($stockColorClass) ?>"></span>
          <?= safe($stockLabel) ?>
        </span>
      </div>

      <?php if ($lowStockNote): ?>
        <div class="low-stock mb-4"><?= safe($lowStockNote) ?></div>
      <?php endif; ?>

      <?php if (!empty($product['features'])): ?>
      <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 text-left">
        <?php foreach ($product['features'] as $f): ?><li><?= safe($f) ?></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <div class="flex flex-col md:flex-row items-center md:items-stretch gap-4">
        <div class="flex items-center gap-2">
          <button id="decreaseQty" class="bg-gray-200 text-gray-700 w-10 h-10 rounded-full flex items-center justify-center text-xl font-bold hover:bg-gray-300" aria-label="Decrease quantity">-</button>
          <input id="qty" type="text" value="1" class="w-12 h-10 text-center rounded-md border border-gray-300" inputmode="numeric" aria-label="Quantity">
          <button id="increaseQty" class="bg-gray-200 text-gray-700 w-10 h-10 rounded-full flex items-center justify-center text-xl font-bold hover:bg-gray-300" aria-label="Increase quantity">+</button>
        </div>

        <button
          class="addToCartBtn px-8 py-4 bg-black text-white font-bold rounded-full shadow hover:bg-gray-800 disabled:opacity-50"
          data-id="<?= safe($product['id']) ?>"
          data-slug="<?= safe($product['slug']) ?>"
          data-name="<?= safe($product['name']) ?>"
          data-price="<?= safe((string)($product['price'] ?? 0)) ?>"
          data-image="<?= safe($mainImg) ?>"
          data-url="/product.php?slug=<?= urlencode($product['slug']) ?>"
          data-qty-selector="#qty"
          <?= $inStock ? '' : 'disabled aria-disabled="true"' ?>
        ><?= $inStock ? 'Add to Cart' : 'Out of Stock' ?></button>

        <a href="/checkout.php" class="px-8 py-4 text-center bg-black text-white font-bold rounded-full shadow hover:bg-gray-800">Proceed to Checkout</a>
      </div>

      <!-- Share -->
      <div class="mt-4 flex items-center gap-3 text-sm text-gray-600">
        <button id="share-copy" class="underline">Copy Link</button>
        <a class="underline" target="_blank" rel="noopener"
           href="https://twitter.com/intent/tweet?text=<?= rawurlencode(($product['name']??'').' by '.SITE_NAME) ?>&url=<?= rawurlencode($canonical) ?>">Share on X</a>
        <a class="underline" target="_blank" rel="noopener"
           href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($canonical) ?>">Facebook</a>
        <a class="underline" target="_blank" rel="noopener"
           href="https://pinterest.com/pin/create/button/?url=<?= rawurlencode($canonical) ?>&media=<?= rawurlencode($mainImg) ?>&description=<?= rawurlencode($product['name']??'') ?>">Pinterest</a>
        <span id="share-copied" class="hidden text-green-600">Link copied!</span>
      </div>
    </div>
  </section>

  <!-- Details -->
  <?php if (!empty($product['description'])): ?>
  <section class="mb-16" id="details">
    <h2 class="text-3xl font-bold text-center mb-10">Product Details</h2>
    <div class="bg-white rounded-2xl p-8 md:p-12 shadow-xl border-t-4 border-black">
      <p class="text-gray-700 leading-relaxed text-center max-w-4xl mx-auto"><?= nl2br(safe($product['description'])) ?></p>
    </div>
  </section>
  <?php endif; ?>

  <!-- Specs -->
  <?php if (!empty($product['specs'])): ?>
  <section class="mb-16" id="specs">
    <h2 class="text-3xl font-bold text-center mb-10">Specifications</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-2xl shadow-lg overflow-hidden">
        <thead class="bg-gray-100">
          <tr><th class="py-3 px-6 text-left">Specification</th><th class="py-3 px-6 text-left">Value</th><th class="py-3 px-6 text-left">Notes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($product['specs'] as $spec): ?>
          <tr class="border-t border-gray-200">
            <td class="py-3 px-6"><?= safe($spec[0] ?? '') ?></td>
            <td class="py-3 px-6"><?= safe($spec[1] ?? '') ?></td>
            <td class="py-3 px-6"><?= safe($spec[2] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
  <?php endif; ?>

  <!-- Customer Reviews -->
  <section class="mb-16" id="reviews">
    <h2 class="text-3xl font-bold text-center mb-10">Customer Reviews</h2>
    <div class="max-w-4xl mx-auto space-y-6">
      <div class="space-y-6">
        <?php if ($productReviews): foreach ($productReviews as $r): ?>
          <article class="bg-white p-6 rounded-2xl shadow-lg" itemscope itemtype="https://schema.org/Review">
            <div class="flex justify-between items-center mb-2">
              <span class="font-bold text-gray-900" itemprop="author"><?= safe($r['author']) ?></span>
              <span class="text-yellow-400" aria-label="Rating <?= (int)$r['rating'] ?> out of 5"><?= str_repeat('★', (int)$r['rating']) ?></span>
            </div>
            <p class="text-gray-700 mb-1 text-sm" itemprop="datePublished"><?= safe($r['date']) ?></p>
            <p class="text-gray-700" itemprop="reviewBody"><?= safe($r['text']) ?></p>
          </article>
        <?php endforeach; else: ?>
          <p class="text-center text-gray-600">No reviews yet. Be the first to review this product!</p>
        <?php endif; ?>
      </div>

      <!-- Add Review -->
      <div class="bg-white p-6 rounded-2xl shadow-lg">
        <h3 class="text-xl font-bold mb-4">Add a Review</h3>
        <form method="POST" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?= safe($csrf_token) ?>">
          <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
          <label class="block">
            <span class="text-sm font-medium">Your name</span>
            <input name="author" class="mt-1 w-full border rounded p-2" placeholder="Name" required>
          </label>
          <label class="block">
            <span class="text-sm font-medium">Rating (1–5)</span>
            <input type="number" name="rating" min="1" max="5" class="mt-1 w-full border rounded p-2" placeholder="5" required>
          </label>
          <label class="block">
            <span class="text-sm font-medium">Your review</span>
            <textarea name="text" class="mt-1 w-full border rounded p-2" placeholder="Share details about your experience" required></textarea>
          </label>
          <button class="bg-black text-white px-6 py-3 rounded-full font-bold hover:bg-gray-800">Submit Review</button>
        </form>
      </div>
    </div>
  </section>

  <!-- Shipping & Returns / Warranty Accordions -->
  <section class="mb-16" id="shipping">
    <h2 class="text-3xl font-bold text-center mb-10">Shipping, Returns & Warranty</h2>
    <div class="grid md:grid-cols-2 gap-6 max-w-5xl mx-auto">
      <details class="acc">
        <summary>Shipping & Delivery</summary>
        <div class="acc-body text-sm text-gray-700">
          <ul class="list-disc ml-5 space-y-1">
            <li>Free shipping over $250 in the continental U.S.</li>
            <li>Orders ship in 1–2 business days.</li>
            <li>Tracking provided via email immediately on fulfillment.</li>
          </ul>
        </div>
      </details>
      <details class="acc">
        <summary>Returns & Exchanges</summary>
        <div class="acc-body text-sm text-gray-700">
          <ul class="list-disc ml-5 space-y-1">
            <li>30-day hassle-free returns.</li>
            <li>Items must be unused and in original packaging.</li>
            <li>Contact us for a prepaid return label.</li>
          </ul>
        </div>
      </details>
      <details class="acc md:col-span-2">
        <summary>Warranty</summary>
        <div class="acc-body text-sm text-gray-700">
          <p>All <?php echo SITE_NAME; ?> products include a 1-year limited warranty against defects in materials and workmanship.</p>
        </div>
      </details>
    </div>
  </section>

  <!-- FAQ -->
  <section class="mb-16" id="faq">
    <h2 class="text-3xl font-bold text-center mb-10">Frequently Asked Questions</h2>
    <div class="max-w-3xl mx-auto space-y-3">
      <?php
        $faq = $product['faq'] ?? [
          ['Is this an official <?php echo SITE_NAME; ?> product?', 'Yes. Look for the fish badge — it marks authentic <?php echo SITE_NAME; ?> gear.'],
          ['How soon will my order ship?', 'Most orders ship within 1–2 business days with tracking.'],
          ['Do you ship internationally?', 'Yes, we ship worldwide. Rates are calculated at checkout.'],
        ];
        foreach ($faq as $row):
      ?>
      <details class="acc">
        <summary><?= safe($row[0] ?? '') ?></summary>
        <div class="acc-body text-sm text-gray-700"><?= nl2br(safe($row[1] ?? '')) ?></div>
      </details>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Recommended -->
  <?php if (!empty($recommended)): ?>
  <section class="mb-16">
    <h2 class="text-3xl font-bold text-center mb-10">You May Also Like</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
      <?php foreach ($recommended as $rec): ?>
        <?php
          $rimg = $rec['images'][0] ?? '/images/placeholder.png';
          $rdesc = trim((string)($rec['description'] ?? ''));
          $rdesc = mb_substr($rdesc, 0, 120) . (mb_strlen((string)($rec['description'] ?? '')) > 120 ? '…' : '');
          // derive rec stock quickly (supports same keys)
          $recInStock = true;
          if (isset($rec['stock_status'])) {
            $status = strtolower(trim((string)$rec['stock_status']));
            $recInStock = in_array($status, ['in','in stock','available','yes','true','1'], true);
          } elseif (array_key_exists('in_stock', $rec)) {
            $recInStock = filter_var($rec['in_stock'], FILTER_VALIDATE_BOOLEAN);
          } elseif (array_key_exists('stock', $rec)) {
            if (is_numeric($rec['stock'])) {
              $recInStock = ((int)$rec['stock'] > 0);
            } else {
              $status = strtolower(trim((string)$rec['stock']));
              $recInStock = in_array($status, ['in','in stock','available','yes','true','1'], true);
            }
          }
          $recColor = $recInStock ? 'bg-green-500' : 'bg-red-500';
          $recLabel = $recInStock ? 'In stock' : 'Out of stock';
        ?>
        <div class="relative bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-200 hover:shadow-2xl transition">
          <div class="card-ribbon" title="<?php echo SITE_NAME; ?> Original">
            <img src="<?= safe(fish_badge_url()) ?>" alt="">GF
          </div>
          <div class="absolute top-2 right-2 flex items-center gap-1 text-[11px] bg-white/90 border border-gray-200 rounded-full px-2 py-1">
            <span class="stock-symbol <?= $recColor ?>"></span>
            <span class="font-medium text-gray-700"><?= safe($recLabel) ?></span>
          </div>
          <a class="block" href="/product/<?= urlencode($rec['slug']) ?>">
            <div class="w-full h-48 bg-white flex items-center justify-center overflow-hidden">
              <img class="block w-full h-full object-contain" src="<?= safe($rimg) ?>" alt="<?= safe($rec['name']) ?>" loading="lazy">
            </div>
            <div class="p-6 text-center">
              <h3 class="text-xl font-bold line-clamp-1"><?= safe($rec['name']) ?></h3>
              <p class="text-gray-600 text-sm mt-2 line-clamp-2"><?= safe($rdesc) ?></p>
              <div class="mt-4 text-2xl font-extrabold"><?= currency($rec['price'] ?? 0) ?></div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Recently Viewed -->
  <?php
    $recent = array_values(array_unique($_SESSION['recently_viewed'] ?? []));
    $recent = array_filter($recent, fn($s) => isset($products[$s]) && $s !== $slug);
    if (!empty($recent)):
  ?>
  <section class="mb-20">
    <h2 class="text-2xl font-bold mb-6">Recently Viewed</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <?php foreach ($recent as $s): $p=$products[$s]; $ri=$p['images'][0] ?? '/images/placeholder.png';
        $pIn = true;
        if (isset($p['stock_status'])) {
          $status = strtolower(trim((string)$p['stock_status']));
          $pIn = in_array($status, ['in','in stock','available','yes','true','1'], true);
        } elseif (array_key_exists('in_stock', $p)) {
          $pIn = filter_var($p['in_stock'], FILTER_VALIDATE_BOOLEAN);
        } elseif (array_key_exists('stock', $p)) {
          if (is_numeric($p['stock'])) { $pIn = ((int)$p['stock'] > 0); }
          else { $status = strtolower(trim((string)$p['stock'])); $pIn = in_array($status, ['in','in stock','available','yes','true','1'], true); }
        }
        $pColor = $pIn ? 'bg-green-500' : 'bg-red-500';
      ?>
      <a href="/product/<?= urlencode($p['slug']) ?>" class="group bg-white border rounded-2xl p-3 hover:shadow-md transition">
        <div class="relative h-32 flex items-center justify-center overflow-hidden bg-white rounded-xl">
          <img src="<?= safe($ri) ?>" alt="<?= safe($p['name']) ?>" class="object-contain w-full h-full">
          <div class="card-ribbon" style="top:.35rem;left:.35rem"><img src="<?= safe(fish_badge_url()) ?>" alt="">GF</div>
          <span class="stock-symbol <?= $pColor ?>" style="position:absolute;right:.4rem;top:.45rem"></span>
        </div>
        <div class="mt-2 text-sm font-semibold line-clamp-1"><?= safe($p['name']) ?></div>
        <div class="text-sm text-gray-600"><?= currency($p['price'] ?? 0) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

</main>

<?php include 'footer.php'; ?>

<!-- Toast -->
<div id="cart-toast" aria-live="polite" aria-atomic="true" style="display:none">
  <div class="toast">
    <div class="thumb"><img id="cart-toast-img" alt=""></div>
    <div class="info">
      <div class="title font-bold">Added to Cart</div>
      <div class="desc text-sm text-gray-300" id="cart-toast-desc"></div>
    </div>
    <div class="actions flex gap-2 ml-2">
      <a href="/cart" class="px-3 py-1 rounded-full bg-white text-black text-xs font-bold">View Cart</a>
      <a href="/checkout" class="px-3 py-1 rounded-full border border-gray-600 text-xs font-bold">Checkout</a>
    </div>
  </div>
</div>

<!-- Sticky Quick Add (mobile/scroll) -->
<div class="sticky-atc bg-white/95 backdrop-blur border-t p-3 md:hidden">
  <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      <img src="<?= safe($mainImg) ?>" alt="" class="w-12 h-12 rounded-lg object-contain border bg-white">
      <div>
        <div class="text-sm font-semibold line-clamp-1"><?= safe($product['name']) ?></div>
        <div class="flex items-center gap-2 text-sm">
          <span class="stock-symbol <?= safe($stockColorClass) ?>"></span>
          <span><?= currency($product['price'] ?? 0) ?></span>
        </div>
      </div>
    </div>
    <button
      class="addToCartBtn px-4 py-2 bg-black text-white text-sm font-bold rounded-full shadow hover:bg-gray-800 disabled:opacity-50"
      data-id="<?= safe($product['id']) ?>"
      data-slug="<?= safe($product['slug']) ?>"
      data-name="<?= safe($product['name']) ?>"
      data-price="<?= safe((string)($product['price'] ?? 0)) ?>"
      data-image="<?= safe($mainImg) ?>"
      data-url="/product.php?slug=<?= urlencode($product['slug']) ?>"
      data-qty="1"
      <?= $inStock ? '' : 'disabled aria-disabled="true"' ?>
    ><?= $inStock ? 'Add' : 'Out' ?></button>
  </div>
</div>

<script>
// thumbs -> main image
document.querySelectorAll('[data-full-size-url]').forEach(t=>{
  t.parentElement.addEventListener('click', ()=>{
    const main=document.getElementById('main-product-image');
    if(main) main.src=t.dataset.fullSizeUrl;
    document.querySelectorAll('[data-full-size-url]').forEach(i=>i.parentElement.classList.remove('ring-2','ring-black'));
    t.parentElement.classList.add('ring-2','ring-black');
  });
});

// keyboard: 1..9 to pick thumbs
document.addEventListener('keydown', (e)=>{
  if (e.target && ['INPUT','TEXTAREA'].includes(e.target.tagName)) return;
  const idx = parseInt(e.key, 10);
  if (!isNaN(idx) && idx>=1){
    const t = document.querySelectorAll('[data-full-size-url]')[idx-1];
    if (t) t.parentElement.click();
  }
});

// qty controls
const qtyInput=document.getElementById('qty');
document.getElementById('increaseQty').addEventListener('click',()=> qtyInput.value=(parseInt(qtyInput.value)||1)+1);
document.getElementById('decreaseQty').addEventListener('click',()=>{ const v=(parseInt(qtyInput.value)||1)-1; qtyInput.value=Math.max(1,v); });

// share copy
const shareCopyBtn = document.getElementById('share-copy');
const shareCopied  = document.getElementById('share-copied');
if (shareCopyBtn) {
  shareCopyBtn.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(window.location.href);
      if (shareCopied) { shareCopied.classList.remove('hidden'); setTimeout(()=>shareCopied.classList.add('hidden'), 1800); }
    } catch (e) {}
  });
}

// sticky atc visibility on scroll
(function(){
  const bar = document.querySelector('.sticky-atc');
  const hero = document.getElementById('main-product-image');
  if(!bar || !hero) return;
  const observer = new IntersectionObserver(entries=>{
    entries.forEach(entry=>{
      if(entry.isIntersecting){ bar.classList.remove('show'); }
      else { bar.classList.add('show'); }
    });
  }, {threshold: 0.2});
  observer.observe(hero);
})();

// smooth scroll for section-nav
document.querySelectorAll('.section-nav a').forEach(a=>{
  a.addEventListener('click', (e)=>{
    if(a.getAttribute('href')?.startsWith('#')){
      e.preventDefault();
      const el = document.querySelector(a.getAttribute('href'));
      if(el) el.scrollIntoView({behavior:'smooth', block:'start'});
    }
  });
});

// highlight active section link
const secLinks = Array.from(document.querySelectorAll('.section-nav a')).filter(a=>a.hash);
const secTargets = secLinks.map(a=>document.querySelector(a.hash)).filter(Boolean);
if (secTargets.length){
  const obs = new IntersectionObserver((entries)=>{
    entries.forEach(entry=>{
      const id = entry.target.getAttribute('id');
      const link = document.querySelector('.section-nav a[href="#'+id+'"]');
      if (link){
        if (entry.isIntersecting) {
          document.querySelectorAll('.section-nav a').forEach(x=>x.classList.remove('active'));
          link.classList.add('active');
        }
      }
    });
  }, {threshold: 0.2});
  secTargets.forEach(t=>obs.observe(t));
}
</script>
</body>
</html>

