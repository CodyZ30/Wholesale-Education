<?php
// shop.php
session_start();
include_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/check_blocked_ip.php';

// Pull the same catalog as product page
$products = require __DIR__ . '/data/products.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo SITE_NAME; ?> Shop</title>

<style>
/* ---------------- Centered popup ---------------- */
#cart-popup {
  position: fixed; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  background: #fff; padding: 30px; border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
  display: none; flex-direction: column; align-items: center; text-align: center;
  z-index: 1000; opacity: 0; transition: opacity .3s ease-in-out;
}
#cart-popup.show { display:flex; opacity:1; }
.checkmark { animation: drawCircle .5s ease forwards, drawCheck .4s ease .5s forwards; }
@keyframes drawCircle { to { stroke-dashoffset: 0; } }
@keyframes drawCheck { to { stroke-dashoffset: 0; } }

/* ---------------- Toast ---------------- */
#cart-toast { position: fixed; bottom: 20px; right: 20px; z-index: 1050; display: none; }
.toast { background: #fff; border-radius: 8px; padding: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; transition: transform .3s ease, opacity .3s ease; transform: translateX(100%); opacity: 0; }
.toast.show { transform: translateX(0); opacity: 1; }
.toast .thumb img { width: 60px; height: 60px; object-fit: contain; margin-right: 15px; }
.toast .info { flex-grow: 1; }
.toast .title { font-weight: bold; font-size: 1rem; color: #111; }
.toast .desc { color: #666; font-size: .875rem; }
.toast .actions { margin-left: 20px; }
.toast .actions a { display: block; margin-top: 5px; text-decoration: none; font-size: .8rem; font-weight: 600; padding: 5px 10px; border-radius: 4px; }
.toast .actions .go-cart { background-color: #f1f5f9; color: #334155; }
.toast .actions .checkout { background-color: #111; color: white; }

/* Make overlays non-interactive except toast actions */
#cart-toast { pointer-events: none; }
#cart-toast .toast { pointer-events: auto; }
#cart-toast .actions a { pointer-events: auto; }
#cart-popup { pointer-events: none; }

/* =========================================================
   CARD STRUCTURE + IMAGE CONTAINMENT
   ========================================================= */
.product-card {
  position: relative;
  isolation: isolate;
  background: #fff;
  display: flex;
  flex-direction: column;
  height: 100%;
}

/* MEDIA box: uniform height + true contain */
.product-card .media {
  position: relative;
  width: 100%;
  height: 11rem;            /* Tailwind h-44 equivalent */
  border-radius: .75rem;
  background: #fff;
  box-shadow: inset 0 0 0 1px rgba(0,0,0,.03);
  overflow: hidden;
  display: flex; align-items: center; justify-content: center;
}
.product-card > .media + div {
  display: flex;
  flex-direction: column;
  flex: 1 1 auto;
}
.product-card .media a { display:flex; width:100%; height:100%; align-items:center; justify-content:center; }
.product-card .media img {
  width: 100%; height: 100%;
  object-fit: contain;
  display: block;
}

/* List mode: square thumb (same rule, but force fixed size) */
.list-mode .product-card .media {
  width: 11rem !important; height: 11rem !important; flex-shrink: 0;
}

/* ACTIONS area */
.product-card .actions { position: relative; z-index: 2; }

/* Tap feel */
.product-card * { -webkit-tap-highlight-color: transparent; }

/* =========================================================
   TEXT ALIGNMENT + CLAMPING
   ========================================================= */
.product-title {
  font-weight: 600;
  font-size: 1.125rem;
  line-height: 1.4;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  min-height: 1.4em;
  margin-bottom: .25rem;
}
.product-desc {
  color: #4b5563;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 2.8em;
  margin-bottom: .25rem;
}
.product-meta-small { color:#6b7280; font-size:.875rem; line-height:1.2; }
.product-info { min-height: 6.25rem; }
.product-card .actions { margin-top: auto; }
@media (min-width: 768px){
  .product-card .actions { align-items: flex-end; }
}

/* Wishlist heart */
.heart {
  position: absolute; top: .5rem; right: .5rem;
  width: 34px; height: 34px;
  display: flex; align-items: center; justify-content: center;
  background: rgba(255,255,255,.96);
  border: 1px solid #e5e7eb; border-radius: 9999px;
  cursor: pointer; font-size: 16px; line-height: 1; color: #9ca3af;
  box-shadow: 0 4px 10px rgba(0,0,0,.06); z-index: 3;
  transition: background .12s ease, color .12s ease, border-color .12s ease;
}
.heart:hover { background: #fff; }
.heart.active { color: #ef4444; border-color: #ef4444; }
</style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="container mx-auto px-4 md:px-6 py-8 md:py-12">

  <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 space-y-4 md:space-y-0">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Shop</h1>
      <p class="text-gray-600 mt-2">Browse our collection of premium fishing gear and accessories.</p>
    </div>
    <div class="flex items-center space-x-3">
      <button id="grid-view" class="p-2 border rounded bg-black text-white">Grid</button>
      <button id="list-view" class="p-2 border rounded">List</button>
    </div>
  </div>

  <div class="md:flex md:space-x-10">
    <aside class="md:w-1/4 space-y-8">
      <div>
        <h2 class="font-semibold mb-3 text-lg">Categories</h2>
        <ul id="category-filter" class="space-y-2 text-gray-700 text-sm">
          <?php
          $cats = array_values(array_unique(array_map(fn($p)=>$p['category'] ?? 'Other', $products)));
          sort($cats);
          foreach ($cats as $cat): ?>
            <li><label><input type="checkbox" value="<?= htmlspecialchars($cat) ?>"> <?= htmlspecialchars($cat) ?></label></li>
          <?php endforeach; ?>
        </ul>

        <?php
          // SEO collections to surface even if not present in catalog yet
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
          // Deduplicate: hide SEO labels that already exist in $cats
          $catsLower = array_map('strtolower', $cats);
          $moreSeo = array_filter($seoCollections, function($slug, $label) use ($catsLower){
            return !in_array(strtolower($label), $catsLower, true);
          }, ARRAY_FILTER_USE_BOTH);
        ?>
        <?php if (!empty($moreSeo)): ?>
        <div class="mt-6">
          <h3 class="font-semibold mb-2 text-md">More Categories</h3>
          <ul class="space-y-2 text-gray-700 text-sm">
            <?php foreach ($moreSeo as $label => $slug): ?>
              <li><a class="block px-2 py-1 rounded hover:bg-gray-100" href="/category/<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($label) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>

      <div>
        <h2 class="font-semibold mb-3 text-lg">Price Range</h2>
        <div class="flex space-x-2">
          <input id="price-min" type="range" min="0" max="250" value="0" class="w-1/2 accent-black">
          <input id="price-max" type="range" min="0" max="250" value="250" class="w-1/2 accent-black">
        </div>
        <div class="flex justify-between text-sm text-gray-500 mt-1">
          <span id="price-min-label">$0</span><span id="price-max-label">$250</span>
        </div>
      </div>

      <div>
        <h2 class="font-semibold mb-3 text-lg">Brands</h2>
        <div id="brand-filter" class="space-y-1 text-sm">
          <?php
          $brands = array_values(array_unique(array_map(fn($p)=>$p['brand'] ?? 'Brand', $products)));
          sort($brands);
          foreach ($brands as $brand): ?>
            <label><input type="checkbox" value="<?= htmlspecialchars($brand) ?>"> <?= htmlspecialchars($brand) ?></label><br>
          <?php endforeach; ?>
        </div>
      </div>

      <div id="compare-section" class="hidden">
        <h2 class="font-semibold mb-3 text-lg flex justify-between items-center">
          Compare
          <button id="reset-compare" class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs hover:bg-gray-300">Reset</button>
        </h2>
        <div id="compare-preview" class="text-sm text-gray-500"></div>
      </div>
    </aside>

    <section class="md:w-3/4">
      <div class="flex justify-between items-center mb-6">
        <p class="text-gray-600"><span id="product-count"><?= count($products) ?></span> products found</p>
        <select id="sort" class="border rounded px-2 py-1">
          <option value="newest">Newest</option>
          <option value="low-high">Price: Low to High</option>
          <option value="high-low">Price: High to Low</option>
        </select>
      </div>

      <div id="product-list" class="grid grid-cols-2 md:grid-cols-3 gap-8">
        <?php foreach ($products as $slug => $p): ?>
          <?php
            $img = $p['images'][0] ?? '/images/placeholder.png';
            $price = number_format((float)($p['price'] ?? 0), 2);
            $url = "/product/" . urlencode($slug);
          ?>
          <div
            class="product-card border rounded-xl p-4 relative shadow-sm hover:shadow-lg transition bg-white"
            data-slug="<?= htmlspecialchars($slug) ?>"
            data-name="<?= htmlspecialchars($p['name']) ?>"
            data-price="<?= htmlspecialchars((string)($p['price'] ?? 0)) ?>"
            data-category="<?= htmlspecialchars($p['category'] ?? '') ?>"
            data-brand="<?= htmlspecialchars($p['brand'] ?? '') ?>"
          >
            <!-- Heart -->
            <button class="heart absolute top-2 right-2" aria-label="Wishlist" data-heart="<?= htmlspecialchars($slug) ?>">♡</button>

            <!-- MEDIA (uniform box; image truly contained) -->
            <div class="media mb-3">
              <a href="<?= $url ?>">
                <img
                  src="<?= htmlspecialchars($img) ?>"
                  alt="<?= htmlspecialchars($p['name']) ?>"
                >
              </a>
            </div>

            <div class="flex flex-col md:flex-row md:justify-between">
              <!-- Info -->
              <div class="flex-1 pr-4 product-info">
                <h3 class="product-title"><a href="<?= $url ?>"><?= htmlspecialchars($p['name']) ?></a></h3>
                <p class="product-desc"><?= htmlspecialchars($p['description'] ?? '') ?></p>
                <?php if (!empty($p['brand'])): ?><p class="product-meta-small">Brand: <?= htmlspecialchars($p['brand']) ?></p><?php endif; ?>
                <?php if (!empty($p['category'])): ?><p class="product-meta-small">Category: <?= htmlspecialchars($p['category']) ?></p><?php endif; ?>
              </div>

              <!-- Actions -->
              <div class="actions flex flex-col items-start md:items-end space-y-2 mt-2 md:mt-0">
                <p class="text-gray-600 font-semibold">$<?= $price ?></p>
                <a href="<?= $url ?>" class="view-product-btn bg-white text-black border-2 border-black px-3 py-1.5 rounded text-sm hover:bg-gray-100">View Product</a>

                <button
                  type="button"
                  class="addToCartBtn bg-black text-white px-3 py-1.5 rounded text-sm hover:bg-gray-800"
                  data-id="<?= htmlspecialchars((string)($p['id'] ?? '')) ?>"
                  data-slug="<?= htmlspecialchars($slug) ?>"
                  data-name="<?= htmlspecialchars($p['name']) ?>"
                  data-price="<?= htmlspecialchars((string)($p['price'] ?? 0)) ?>"
                  data-image="<?= htmlspecialchars($img) ?>"
                  data-url="<?= htmlspecialchars($url) ?>"
                  data-qty="1"
                >
                  Add to Cart
                </button>

                <button
                  type="button"
                  class="checkout-now bg-green-600 text-white px-3 py-1.5 rounded text-sm hover:bg-green-700"
                  data-id="<?= htmlspecialchars((string)($p['id'] ?? '')) ?>"
                  data-slug="<?= htmlspecialchars($slug) ?>"
                  data-name="<?= htmlspecialchars($p['name']) ?>"
                  data-price="<?= htmlspecialchars((string)($p['price'] ?? 0)) ?>"
                  data-image="<?= htmlspecialchars($img) ?>"
                  data-url="<?= htmlspecialchars($url) ?>"
                  data-qty="1"
                  data-checkout="true"
                  data-forward="/checkout"
                >
                  Checkout Now
                </button>

                <label class="text-xs text-gray-600 flex items-center space-x-1">
                  <input type="checkbox" class="compare-checkbox" onchange="toggleCompare('<?= htmlspecialchars($slug) ?>');" data-slug="<?= htmlspecialchars($slug) ?>">
                  <span>Compare</span>
                </label>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div id="pagination" class="flex justify-center space-x-2 mt-10 hidden"></div>
    </section>
  </div>
</main>

<?php include 'footer.php'; ?>
 
<!-- Removed duplicate local toast/popup; footer now provides global toast/popup wiring. -->

<script>
/* ===== WISHLIST ===== */
function syncHeartsUI() {
  const wishlist = JSON.parse(localStorage.getItem("wishlist") || "[]");
  document.querySelectorAll('[data-heart]').forEach(btn => {
    const slug = btn.getAttribute('data-heart');
    const active = wishlist.includes(slug);
    btn.classList.toggle('active', active);
    btn.textContent = active ? '♥' : '♡';
    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
  });
}
document.addEventListener('click', (e) => {
  const heart = e.target.closest('[data-heart]');
  if (!heart) return;
  const slug = heart.getAttribute('data-heart');
  let wishlist = JSON.parse(localStorage.getItem("wishlist") || "[]");
  if (wishlist.includes(slug)) {
    wishlist = wishlist.filter(s => s !== slug);
  } else {
    wishlist.push(slug);
  }
  localStorage.setItem("wishlist", JSON.stringify(wishlist));
  syncHeartsUI();
});

/* ===== COMPARE ===== */
function toggleCompare(slug) {
  let compare = JSON.parse(localStorage.getItem("compare") || "[]");
  const index = compare.indexOf(slug);
  if (index > -1) compare.splice(index, 1); else compare.push(slug);
  localStorage.setItem("compare", JSON.stringify(compare));
  renderCompare();
}
function renderCompare() {
  let compare = JSON.parse(localStorage.getItem("compare") || "[]");
  let section = document.getElementById("compare-section");
  let preview = document.getElementById("compare-preview");

  // Sync checkbox states
  document.querySelectorAll(".compare-checkbox").forEach(cb => {
    cb.checked = compare.includes(cb.dataset.slug);
  });

  if (!compare.length) {
    section.classList.add("hidden");
    preview.innerHTML = "";
    return;
  }

  const rows = compare.map(slug => {
    const card = document.querySelector(`.product-card[data-slug='${CSS.escape(slug)}']`);
    if (!card) return '';
    return `<tr>
      <td class='border p-1'>${card.dataset.name}</td>
      <td class='border p-1'>$${parseFloat(card.dataset.price).toFixed(2)}</td>
      <td class='border p-1'>${card.dataset.brand}</td>
      <td class='border p-1'>${card.dataset.category}</td>
    </tr>`;
  }).join('');

  preview.innerHTML = `<table class='text-xs w-full border mt-2'>
    <thead><tr>
      <th class='border p-1'>Name</th>
      <th class='border p-1'>Price</th>
      <th class='border p-1'>Brand</th>
      <th class='border p-1'>Category</th>
    </tr></thead>
    <tbody>${rows}</tbody>
  </table>`;

  section.classList.remove("hidden");
}
document.getElementById("reset-compare").addEventListener("click", () => {
  localStorage.removeItem("compare");
  renderCompare();
});

/* ===== GRID / LIST VIEW ===== */
function setView(view){
  const productList = document.getElementById("product-list");
  if(view==="grid"){
    productList.className = "grid grid-cols-2 md:grid-cols-3 gap-8";
    document.getElementById("grid-view").classList.add("bg-black","text-white");
    document.getElementById("list-view").classList.remove("bg-black","text-white");
    document.querySelector('section.md\\:w-3\\/4')?.classList.remove('list-mode');
    document.querySelectorAll(".product-card").forEach(card=>{
      card.classList.remove("flex","space-x-4");
    });
  }else{
    productList.className = "space-y-6";
    document.getElementById("list-view").classList.add("bg-black","text-white");
    document.getElementById("grid-view").classList.remove("bg-black","text-white");
    document.querySelector('section.md\\:w-3\\/4')?.classList.add('list-mode');
    document.querySelectorAll(".product-card").forEach(card=>{
      card.classList.add("flex","space-x-4");
    });
  }
}
document.getElementById("grid-view").addEventListener("click",()=>setView("grid"));
document.getElementById("list-view").addEventListener("click",()=>setView("list"));

/* ===== FILTERS ===== */
function applyFilters(){
  let minPrice = parseFloat(document.getElementById("price-min").value);
  let maxPrice = parseFloat(document.getElementById("price-max").value);
  document.getElementById("price-min-label").innerText = "$"+minPrice;
  document.getElementById("price-max-label").innerText = "$"+maxPrice;

  let categories = [...document.querySelectorAll("#category-filter input:checked")].map(i=>i.value);
  let brands = [...document.querySelectorAll("#brand-filter input:checked")].map(i=>i.value);

  document.querySelectorAll(".product-card").forEach(card=>{
    let price = parseFloat(card.dataset.price);
    let category = card.dataset.category;
    let brand = card.dataset.brand;
    let show = price>=minPrice && price<=maxPrice &&
      (categories.length===0 || categories.includes(category)) &&
      (brands.length===0 || brands.includes(brand));
    card.style.display = show ? "" : "none";
  });
}
document.getElementById("price-min").addEventListener("input",applyFilters);
document.getElementById("price-max").addEventListener("input",applyFilters);
document.querySelectorAll("#category-filter input, #brand-filter input").forEach(input=>input.addEventListener("change",applyFilters));

/* ===== SORTING ===== */
document.getElementById("sort").addEventListener("change",function(){
  let cards=[...document.querySelectorAll(".product-card")];
  let list=document.getElementById("product-list");
  let sort=this.value;
  cards.sort((a,b)=>{
    let pa=parseFloat(a.dataset.price), pb=parseFloat(b.dataset.price);
    if(sort==="low-high") return pa-pb;
    if(sort==="high-low") return pb-pa;
    return 0;
  });
  list.innerHTML="";
  cards.forEach(p=>list.appendChild(p));
});

/* ===== PAGINATION (client-side demo) ===== */
function renderPagination(perPage=6){
  let cards=[...document.querySelectorAll(".product-card")].filter(c=>c.style.display!=="none");
  let pagination=document.getElementById("pagination");
  let total=cards.length;
  let pages=Math.ceil(total/perPage);
  if(pages<=1){pagination.classList.add("hidden"); return;}
  pagination.innerHTML="";
  for(let i=1;i<=pages;i++){
    let btn=document.createElement("button");
    btn.innerText=i;
    btn.className=`px-3 py-1 border rounded ${i===1?'bg-black text-white':''}`;
    btn.addEventListener("click",()=>goToPage(i,perPage));
    pagination.appendChild(btn);
  }
  pagination.classList.remove("hidden");
  goToPage(1, perPage);
}
function goToPage(page,perPage){
  let cards=[...document.querySelectorAll(".product-card")].filter(c=>c.style.display!=="none");
  cards.forEach((p,i)=>p.style.display=(i>=(page-1)*perPage && i<page*perPage)?"":"none");
  document.querySelectorAll("#pagination button").forEach(btn=>btn.classList.remove("bg-black","text-white"));
  const btn = document.querySelector(`#pagination button:nth-child(${page})`);
  if (btn) btn.classList.add("bg-black","text-white");
}

/* ===== INIT ===== */
document.addEventListener("DOMContentLoaded",()=>{
  renderCompare();
  applyFilters();
  setView("grid");          // default grid
  renderPagination(6);
  syncHeartsUI();           // reflect wishlist hearts on load
});
</script>
</body>
</html>
