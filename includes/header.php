<?php 
include_once __DIR__ . '/config.php';
// IP blocking check moved to individual pages to avoid session issues
?>

<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- IMPORTANT: Cart API must load BEFORE header wiring -->
<script src="/assets/cart.js" defer></script>
<?php if (defined('SITE_NAME')): ?>
<script>
  window.SITE_NAME_JS = "<?php echo addslashes((string)SITE_NAME); ?>";
</script>
<?php endif; ?>

<!-- Header wiring: mini-cart UI (Cart binder itself lives in /assets/cart.js) -->
<script defer>
(function () {
  if (window.__GF_HEADER_WIRED) return;
  window.__GF_HEADER_WIRED = true;

  function wireMiniCart() {
    if (window.__GF_MINICART_WIRED) return;
    window.__GF_MINICART_WIRED = true;

    const btn       = document.getElementById('cart-button');
    const panel     = document.getElementById('mini-cart-panel');
    const overlay   = document.getElementById('mini-cart-overlay');
    const closeBtn  = document.getElementById('mini-cart-close');
    const itemsEl   = document.getElementById('mini-cart-items');
    const subtotalEl= document.getElementById('mini-cart-subtotal');
    const countEl   = document.getElementById('cart-count');

    if (!btn || !panel || !itemsEl || !subtotalEl || !countEl) return;

    const currency = (n) => `$${Number(n || 0).toFixed(2)}`;
    const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

    function lockScroll(lock){
      if (lock) { document.documentElement.style.overflow = 'hidden'; document.body.style.overflow = 'hidden'; }
      else { document.documentElement.style.overflow = ''; document.body.style.overflow = ''; }
    }

    async function renderMiniCart() {
      const cart = (window.Cart?.get() || []);
      if (cart.length === 0) {
        itemsEl.innerHTML = `<div class="p-6 text-center text-gray-500">Your cart is empty.</div>`;
        subtotalEl.textContent = currency(0);
        return;
      }

      // Get unique product slugs from cart
      const slugs = [...new Set(cart.map(item => item.slug).filter(Boolean))];
      
      // Fetch product images from backend
      let productImages = {};
      if (slugs.length > 0) {
        try {
          const response = await fetch('/get_product_images.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ slugs: slugs })
          });
          
          if (response.ok) {
            productImages = await response.json();
          }
        } catch (error) {
          console.error('Error fetching product images:', error);
        }
      }

      itemsEl.innerHTML = cart.map(item => {
        const total = Number(item.price) * Number(item.qty);
        
        // Use backend image if available, otherwise fallback to frontend image or placeholder
        const backendProduct = productImages[item.slug];
        const imgSrc = backendProduct?.image || item.image || '/images/placeholder.png';
        const alt = (item.name || 'Item').toString().replace(/"/g,'&quot;');
        
        return `
          <div class="p-4 flex items-center gap-3">
            <div class="mini-thumb w-16 h-16 rounded-xl border bg-white overflow-hidden flex items-center justify-center">
              <img src="${imgSrc}" alt="${alt}" class="mini-img max-w-[90%] max-h-[90%] object-contain">
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-medium truncate">${item.name}</div>
              <div class="text-sm text-gray-500">
                ${currency(item.price)} • Qty
                <input type="number" min="1" value="${item.qty}" data-mini-qty="${item.id}" class="w-14 ml-1 border rounded text-center">
              </div>
            </div>
            <div class="text-right">
              <div class="font-semibold">${currency(total)}</div>
              <button class="text-xs text-red-500 hover:underline mt-1" data-mini-remove="${item.id}">Remove</button>
            </div>
          </div>
        `;
      }).join('');
      subtotalEl.textContent = currency(window.Cart?.subtotal() || 0);
    }

    function refreshBadge() {
      const count = window.Cart?.count?.() || 0;
      countEl.textContent = count;
    }

    function openPanel() {
      panel.classList.remove('hidden');
      btn.setAttribute('aria-expanded', 'true');
      if (isMobile()) { overlay?.classList.add('show'); lockScroll(true); }
      renderMiniCart();
    }
    function closePanel() {
      panel.classList.add('hidden');
      btn.setAttribute('aria-expanded', 'false');
      overlay?.classList.remove('show');
      lockScroll(false);
    }

    panel.addEventListener('click', (e) => {
      const a = e.target.closest('a.view-bag, a.checkout');
      if (a) closePanel();
    });

    itemsEl.addEventListener('input', (e) => {
      const id = e.target.getAttribute('data-mini-qty');
      if (id) {
        const val = parseInt(e.target.value, 10);
        if (isNaN(val) || val < 1) window.Cart.remove(id);
        else window.Cart.updateQty(id, val);
      }
    });
    itemsEl.addEventListener('click', (e) => {
      const r = e.target.closest('[data-mini-remove]');
      if (r) window.Cart.remove(r.getAttribute('data-mini-remove'));
    });

    window.addEventListener('cart:updated', () => {
      refreshBadge();
      if (!panel.classList.contains('hidden')) renderMiniCart();
    });

    btn.addEventListener('click', () => panel.classList.contains('hidden') ? openPanel() : closePanel());
    closeBtn?.addEventListener('click', closePanel);
    overlay?.addEventListener('click', closePanel);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePanel(); });

    document.addEventListener('DOMContentLoaded', refreshBadge);
  }

  function init() {
    if (!window.Cart) {
      console.warn('Cart API not found. Creating lightweight fallback.');
      const LS_KEY = 'cart';
      window.Cart = {
        get(){ try{return JSON.parse(localStorage.getItem(LS_KEY)||'[]');}catch{return[];} },
        set(items){ try{localStorage.setItem(LS_KEY, JSON.stringify(items));}catch(_){} window.dispatchEvent(new CustomEvent('cart:updated',{detail:{cart:items}})); },
        count(){ return this.get().reduce((n,it)=> n + Number(it.qty||0), 0); },
        subtotal(){ return this.get().reduce((s,it)=> s + Number(it.price||0)*Number(it.qty||0), 0); },
        add(item){ const items=this.get(); const id=String(item.id||item.slug||item.name||''); if(!id) return; const i=items.findIndex(it=>String(it.id)===id); if(i>-1){ items[i].qty=Number(items[i].qty||0)+Number(item.qty||1);} else { items.push({ id, slug:item.slug||'', name:item.name||'Item', price:Number(item.price||0), image:item.image||'', url:item.url||'', qty:Number(item.qty||1) }); } this.set(items); window.dispatchEvent(new CustomEvent('cart:added',{detail:{item}})); }
      };
    }
    wireMiniCart();
    // Traffic capture beacon (UTM + referrer)
    try {
      const url = new URL(window.location.href);
      const utm = {
        source: url.searchParams.get('utm_source') || '',
        medium: url.searchParams.get('utm_medium') || '',
        campaign: url.searchParams.get('utm_campaign') || '',
        term: url.searchParams.get('utm_term') || '',
        content: url.searchParams.get('utm_content') || ''
      };
      const payload = {
        href: window.location.href,
        path: window.location.pathname,
        referrer: document.referrer || '',
        utm
      };
      const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
      
      // Try sendBeacon first, fallback to fetch
      if (navigator.sendBeacon) {
        navigator.sendBeacon('/track_visit.php', blob);
        console.log('Traffic tracking: sendBeacon sent');
      } else {
        fetch('/track_visit.php', {
          method: 'POST',
          body: blob,
          keepalive: true
        }).then(() => {
          console.log('Traffic tracking: fetch sent');
        }).catch(err => {
          console.error('Traffic tracking error:', err);
        });
      }
    } catch (e) { 
      console.error('Traffic tracking error:', e);
    }
    // Mobile menu toggle
    const toggle = document.getElementById('mobile-toggle');
    const menu = document.getElementById('mobile-menu');
    const closeBtn = document.getElementById('mobile-close');
    function open(){ if(menu){ menu.classList.remove('hidden'); document.documentElement.style.overflow='hidden'; document.body.style.overflow='hidden'; } }
    function close(){ if(menu){ menu.classList.add('hidden'); document.documentElement.style.overflow=''; document.body.style.overflow=''; } }
    toggle?.addEventListener('click', ()=> menu?.classList.contains('hidden') ? open() : close());
    closeBtn?.addEventListener('click', close);
    menu?.addEventListener('click', (e)=>{ if(e.target.id==='mobile-menu') close(); });
    menu?.querySelectorAll('a').forEach(a=> a.addEventListener('click', close));
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once:true });
  } else {
    init();
  }
})();
</script>

<style>
.top-bar{background:#00a651;color:#fff;font-size:.8rem;padding:.25rem 1rem;text-align:center;}
header.gf-header{background:#fff;color:#333;border-bottom:4px solid #00a651;z-index:50;}
.gf-logo img{height:60px;width:auto;}

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
.mini-thumb{ width:64px; height:64px; border-radius:12px; background:#fff; border:1px solid #e5e7eb; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; }
.mini-img{ max-width:90%; max-height:90%; width:auto; height:auto; object-fit:contain; display:block; }
</style>
<header class="gf-header sticky top-0 shadow-lg">
  <div class="top-bar">🌎 Free Shipping on Orders Over $250 | Call us: (123) 456-7890</div>
  <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-20 items-center justify-between">
      <a href="/home" class="gf-logo inline-flex items-center"><img src="/images/logo.png" alt="<?php echo SITE_NAME; ?> logo"></a>

      <!-- Main menu (dropdowns) -->
      <?php include __DIR__ . '/menu.php'; ?>

      <div class="flex items-center gap-4">
        <!-- Mini Cart Trigger -->
        <div class="relative" id="mini-cart-root">
          <button id="cart-button" type="button" class="cart-button" aria-haspopup="dialog" aria-expanded="false" aria-controls="mini-cart-panel">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M3 3h2l.4 2M7 13h10l4-8H5.4" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="10" cy="20" r="1.6" fill="#fff"></circle>
              <circle cx="18" cy="20" r="1.6" fill="#fff"></circle>
            </svg>
            <span>Cart</span>
            <span class="cart-count" id="cart-count">0</span>
          </button>

          <!-- Backdrop for mobile sheet -->
          <div id="mini-cart-overlay" aria-hidden="true"></div>

          <!-- Mini Cart Dropdown Panel -->
          <div id="mini-cart-panel" class="hidden absolute right-0 mt-2 w-96 max-h-[70vh] overflow-auto bg-white shadow-2xl rounded-2xl border z-50" role="dialog" aria-label="Mini cart" aria-modal="true">
            <div class="p-4 border-b flex items-center justify-between mini-cart-header">
              <h3 class="font-semibold text-lg">Your Cart</h3>
              <button id="mini-cart-close" class="text-sm text-gray-500 hover:text-black">Close</button>
            </div>
            <div id="mini-cart-items" class="divide-y"></div>
            <div class="p-4 border-t space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Subtotal</span>
                <span id="mini-cart-subtotal" class="font-semibold">$0.00</span>
              </div>
              <div class="flex gap-2">
                <a href="/cart" class="view-bag text-center border py-2 rounded-full font-medium w-1/2">View Cart</a>
                <a href="/checkout" class="checkout bg-black text-white text-center py-2 rounded-full font-semibold w-1/2">Checkout</a>
              </div>
              <p class="text-[12px] text-gray-500">Shipping & taxes calculated at checkout.</p>
            </div>
          </div>
        </div>

        <!-- Mobile menu toggle (optional) -->
        <button id="mobile-toggle" class="md:hidden h-10 w-10 flex items-center justify-center rounded-md border border-gray-200">
          <span class="sr-only">Open menu</span>
          <div class="space-y-1.5">
            <span class="block h-0.5 w-5 bg-gray-700"></span>
            <span class="block h-0.5 w-5 bg-gray-700"></span>
            <span class="block h-0.5 w-5 bg-gray-700"></span>
          </div>
        </button>
      </div>
    </div>
  </nav>
  <!-- Mobile menu drawer -->
  <div id="mobile-menu" class="hidden fixed inset-0 z-50 bg-white/95 backdrop-blur">
    <div class="max-w-7xl mx-auto px-6 py-4">
      <div class="flex items-center justify-between mb-4">
        <a href="/" class="gf-logo inline-flex items-center"><img src="/images/logo.png" alt="<?php echo SITE_NAME; ?> logo" style="height:40px;width:auto;"></a>
        <button id="mobile-close" class="h-10 w-10 flex items-center justify-center rounded-md border border-gray-200">✕</button>
      </div>
      <div class="space-y-2 text-lg">
        <a class="block py-2" href="/home">Home</a>
        <a class="block py-2" href="/shop">Shop</a>
        <a class="block py-2" href="/home#stickers-products">Stickers</a>
        <a class="block py-2" href="/guides">Guides</a>
        <a class="block py-2" href="/about">About</a>
        <a class="block py-2" href="/home#business-info">Support</a>
      </div>
    </div>
  </div>
</header>
