<?php
session_start();
include_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo SITE_NAME; ?> Shopping Cart</title>
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="container mx-auto px-4 md:px-6 py-8 md:py-12">
  <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>

  <div class="md:flex md:space-x-8">
    <!-- Left: Cart Items -->
    <div class="md:w-2/3 bg-white p-6 rounded-2xl shadow">
      <!-- Skeleton while loading -->
      <style>
        .skeleton{position:relative;overflow:hidden;background:#f3f4f6}
        .skeleton::after{content:"";position:absolute;inset:0;transform:translateX(-100%);
          background:linear-gradient(90deg,transparent,rgba(255,255,255,.6),transparent);animation:shimmer 1.4s infinite}
        @keyframes shimmer{100%{transform:translateX(100%)}}

        /* Contained thumbnails */
        .cart-thumb{
          width:80px; height:80px;
          border-radius:14px;
          background:#fff;
          border:1px solid #e5e7eb;
          box-shadow: inset 0 0 0 1px rgba(0,0,0,0.02);
          display:flex; align-items:center; justify-content:center;
          overflow:hidden; flex-shrink:0;
        }
        .cart-thumb img{
          max-width:90%; max-height:90%;
          width:auto; height:auto;
          object-fit:contain; display:block;
        }

        /* Single qty control (buttons + readonly qty) */
        .qty-group{ display:inline-flex; align-items:center; gap:.25rem; }
        .qty-btn{
          width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;
          border:1px solid #e5e7eb; border-radius:8px; background:#fafafa; cursor:pointer;
        }
        .qty-btn:hover{ background:#f3f4f6; }
        .qty-display{
          min-width:2ch; text-align:center; font-weight:600; padding:0 .25rem;
        }
      </style>

      <div id="cart-items">
        <div class="space-y-4" id="cart-skeleton">
          <div class="flex items-center gap-4">
            <div class="w-20 h-20 rounded skeleton"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 w-2/3 rounded skeleton"></div>
              <div class="h-4 w-1/3 rounded skeleton"></div>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <div class="w-20 h-20 rounded skeleton"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 w-1/2 rounded skeleton"></div>
              <div class="h-4 w-1/3 rounded skeleton"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Order Summary -->
    <div class="md:w-1/3 mt-6 md:mt-0">
      <div class="bg-white p-6 rounded-2xl shadow">
        <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
        <div class="flex justify-between py-2 border-b"><span>Subtotal</span><span id="subtotal">$0.00</span></div>
        <div class="flex justify-between py-2 border-b hidden" id="discount-row"><span>Discount</span><span id="discount">-$0.00</span></div>
        <div class="flex justify-between py-2 border-b"><span>Estimated Shipping</span><span id="shipping">$0.00</span></div>
        <div class="flex justify-between py-2 border-b"><span>Estimated Tax</span><span id="tax">—</span></div>
        <div class="flex justify-between py-4 font-bold text-lg"><span>Total</span><span id="total">$0.00</span></div>

        <!-- Checkout buttons -->
        <a href="/checkout" class="w-full inline-block text-center bg-black text-white py-3 rounded-full font-bold mb-3 hover:bg-gray-800">Checkout</a>

        <!-- Promo Code -->
        <div class="mt-4">
          <label for="promo" class="block text-sm font-medium mb-1">Promo Code</label>
          <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
            <input id="promo" type="text" class="flex-1 w-full border rounded px-3 py-2" placeholder="Enter code">
            <button id="apply-promo" class="w-full sm:w-auto px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Apply</button>
          </div>
          <div class="mt-2 hidden sm:flex sm:items-center sm:justify-between gap-2" id="applied-promo-pill">
            <span class="text-sm">Applied: <strong id="applied-promo-name"></strong></span>
            <button id="remove-promo" class="text-sm text-red-600 hover:underline self-start sm:self-auto">Remove</button>
          </div>
          <p id="promo-msg" class="text-xs text-gray-500 mt-1 min-h-[1rem]"></p>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-6">
    <a href="/" class="text-gray-700 underline">← Continue Shopping</a>
  </div>
</main>
<?php include 'footer.php'; ?>

<script>
/* cart.php — single qty control + robust decrement rule + promos */
const currency = (n) => `$${Number(n || 0).toFixed(2)}`;

// Server-driven: coupons and totals are computed on the server
function getAppliedPromo(){ return null; }
function setAppliedPromo(_){ /* no-op; server stores session coupon */ }

// Always read latest qty from cart before changing (prevents stale-click bugs)
function changeQty(id, delta){
  const cart = (window.Cart?.get?.() || []);
  const idx = cart.findIndex(it => String(it.id) === String(id));
  if (idx === -1) return;
  const current = Number(cart[idx].qty) || 1;
  let next = current + delta;

  // Rule: qty cannot go below 1; if user tries to go below, remove item
  if (next < 1) {
    window.Cart.remove(id);
    return;
  }
  window.Cart.updateQty(id, next);
}

// Server summary hook
function applyServerSummary(sum){
  if (!sum || sum.success === false) return;
  const subtotal = Number(sum.subtotal || 0);
  const discount = Number(sum.discount || 0);
  const shipping = Number(sum.shipping || 0);
  const tax = Number(sum.tax || 0);
  const total = Number(sum.total || 0);

  document.getElementById('subtotal').textContent = currency(subtotal);
  const discountRow = document.getElementById('discount-row');
  if (discount > 0) {
    discountRow.classList.remove('hidden');
    document.getElementById('discount').textContent = `-${currency(discount)}`;
  } else {
    discountRow.classList.add('hidden');
  }
  document.getElementById('shipping').textContent = shipping === 0 ? 'Free' : currency(shipping);
  document.getElementById('tax').textContent = tax ? currency(tax) : '—';
  document.getElementById('total').textContent = currency(total);

  const pill = document.getElementById('applied-promo-pill');
  const name = document.getElementById('applied-promo-name');
  const msg = document.getElementById('promo-msg');
  if (sum.coupon) {
    pill.classList.remove('hidden');
    name.textContent = `${sum.coupon.code} — ${sum.coupon.name || ''}`.trim();
    msg.textContent = '';
  } else {
    pill.classList.add('hidden');
  }
}

async function renderCart() {
  const cart = (window.Cart?.get?.() || []);
  const container = document.getElementById('cart-items');
  const skel = document.getElementById('cart-skeleton');
  if (skel) skel.remove();

  if (!cart.length) {
    container.innerHTML = `
      <div class="text-center py-10">
        <p class="text-gray-500 mb-4">Your cart is empty.</p>
        <a href="/" class="inline-block border px-4 py-2 rounded-full hover:bg-gray-50">Continue Shopping</a>
      </div>`;
    updateSummary();
    return;
  }

  // Get unique product slugs from cart
  const slugs = [...new Set(cart.map(item => item.slug).filter(Boolean))];
  
  // Fetch product images from backend (temporarily disabled for debugging)
  let productImages = {};
  // Temporarily disable backend fetching to restore images
  /*
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
  */

  container.innerHTML = cart.map((item) => {
    const price = Number(item.price) || 0;
    const qty   = Number(item.qty) || 1;
    const itemTotal = price * qty;
    const id = (item.id != null ? item.id : item.slug || item.name); // fallback if id missing
    const subtitle = [item.variant, item.size, item.color, item.sku].filter(Boolean).join(' • ');
    const nameHtml = item.url ? `<a href="${item.url}" class="hover:underline">${item.name}</a>` : item.name;
    
    // Use backend image if available, otherwise fallback to frontend image or placeholder
    const backendProduct = productImages[item.slug];
    const imgSrc = backendProduct?.image || item.image || '/images/placeholder.png';
    const alt = (item.name || 'Item').toString().replace(/"/g,'&quot;');

    return `
      <div class="flex items-center justify-between py-4 border-b">
        <div class="flex items-center space-x-4">
          <a href="${item.url || '#'}" class="shrink-0 cart-thumb" aria-label="${alt}">
            <img src="${imgSrc}" alt="${alt}">
          </a>
          <div>
            <div class="font-semibold">${nameHtml}</div>
            ${subtitle ? `<div class="text-sm text-gray-500">${subtitle}</div>` : ''}
            <div class="text-sm text-gray-500">${currency(price)} each</div>
            ${item.noDiscount ? `<div class="text-[11px] text-gray-400">(Excluded from promos)</div>` : ''}
            <button onclick="window.Cart.remove('${String(id).replace(/"/g,'&quot;')}')" class="text-sm text-red-500 hover:underline mt-1">Remove</button>
          </div>
        </div>

        <div class="flex items-center gap-6">
          <!-- SINGLE qty control -->
          <div class="qty-group" aria-label="Quantity controls">
            <button class="qty-btn" aria-label="Decrease quantity"
              onclick="changeQty('${String(id).replace(/"/g,'&quot;')}', -1)">−</button>
            <span class="qty-display" aria-live="polite">${qty}</span>
            <button class="qty-btn" aria-label="Increase quantity"
              onclick="changeQty('${String(id).replace(/"/g,'&quot;')}', 1)">+</button>
          </div>
          <div class="font-semibold">${currency(itemTotal)}</div>
        </div>
      </div>
    `;
  }).join('');

  updateSummary();
}

function updateSummary() { /* summary now driven by cart:summary */ }

document.addEventListener('DOMContentLoaded', () => {
  renderCart();
  // Re-render and keep qty display fresh whenever the cart updates
  window.addEventListener('cart:updated', () => renderCart());
  // Listen to server summary
  window.addEventListener('cart:summary', (e) => applyServerSummary(e.detail));
  // Trigger a sync to get initial server totals
  try { window.dispatchEvent(new CustomEvent('cart:updated', { detail: { cart: (window.Cart?.get?.()||[]) } })); } catch(_){ }

  // Hard fetch summary once to avoid any race conditions
  try {
    fetch('/update_cart.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(window.Cart?.get?.()||[]) })
      .then(r=>r.ok?r.json():null)
      .then(sum=>{ if(sum) applyServerSummary(sum); })
      .catch(()=>{});
  } catch(_){ }

  // Promo handlers
  const applyBtn = document.getElementById('apply-promo');
  const removeBtn = document.getElementById('remove-promo');
  const input = document.getElementById('promo');
  const msg = document.getElementById('promo-msg');

  applyBtn.addEventListener('click', async () => {
    const code = (input.value || '').trim().toUpperCase();
    if (!code) { msg.textContent = 'Please enter a code.'; return; }
    try {
      const resp = await fetch('/apply_coupon.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({code}) });
      const data = await resp.json();
      if (!data.success) { msg.textContent = data.error || 'Coupon not valid.'; return; }
      msg.textContent = 'Code applied.';
      input.value = '';
      // Force a cart sync to refresh totals
      window.dispatchEvent(new CustomEvent('cart:updated', { detail: { cart: (window.Cart?.get?.()||[]) } }));
    } catch (e) {
      msg.textContent = 'Error applying code.';
    }
  });

  removeBtn.addEventListener('click', async () => {
    try {
      await fetch('/apply_coupon.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({code:'REMOVE'}) });
      msg.textContent = 'Promo removed.';
      window.dispatchEvent(new CustomEvent('cart:updated', { detail: { cart: (window.Cart?.get?.()||[]) } }));
    } catch (e) {
      msg.textContent = 'Error removing code.';
    }
  });
});

</script>
</body>
</html>
