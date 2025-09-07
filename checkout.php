<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();

/*
  NOTE:
  Checkout now reads the cart from localStorage on the client,
  including each item's `image` and optional `url`.
  You no longer need to inject demo items into $_SESSION here.
*/

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> Checkout Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="container mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold mb-6">Checkout</h1>

  <div class="md:flex md:gap-6">
    <!-- Left: Customer Info, Shipping, Billing -->
    <div class="md:w-2/3 space-y-6">
      
      <!-- Customer Information -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Customer Information</h2>
        <input type="text" id="name" placeholder="Full Name" class="input-field mb-2">
        <input type="email" id="email" placeholder="Email" class="input-field mb-2">
      </div>

      <!-- Shipping Address -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Shipping Address</h2>
        <input type="text" id="ship-address" placeholder="Address" class="input-field mb-2">
        <div class="flex gap-2 mb-2">
          <input type="text" id="ship-city" placeholder="City" class="input-field flex-1">
          <input type="text" id="ship-state" placeholder="State" class="input-field flex-1">
        </div>
        <div class="flex gap-2 mb-2">
          <input type="text" id="ship-zip" placeholder="ZIP" class="input-field flex-1">
          <select id="ship-country" class="input-field flex-1">
            <option value="US">United States</option>
            <option value="CA">Canada</option>
            <option value="MX">Mexico</option>
          </select>
        </div>
      </div>

      <!-- Billing Address -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Billing Address</h2>
        <label class="inline-flex items-center gap-2 mb-2">
          <input type="checkbox" id="same-as-shipping" class="form-checkbox">
          Same as shipping address
        </label>
        <input type="text" id="bill-address" placeholder="Address" class="input-field mb-2">
        <div class="flex gap-2 mb-2">
          <input type="text" id="bill-city" placeholder="City" class="input-field flex-1">
          <input type="text" id="bill-state" placeholder="State" class="input-field flex-1">
        </div>
        <div class="flex gap-2 mb-2">
          <input type="text" id="bill-zip" placeholder="ZIP" class="input-field flex-1">
          <select id="bill-country" class="input-field flex-1">
            <option value="US">United States</option>
            <option value="CA">Canada</option>
            <option value="MX">Mexico</option>
          </select>
        </div>
      </div>

      <!-- Payment Info -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Payment Information</h2>
        <p class="text-gray-600 mb-2">Choose your payment method</p>
        <div id="payment-options" class="space-y-2">
          <button class="button w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded">PayPal</button>
          <button class="button w-full bg-gray-800 hover:bg-gray-900 text-white py-2 rounded">Credit/Debit Card (Future)</button>
        </div>
      </div>

    </div>

    <!-- Right: Order Summary -->
    <div class="md:w-1/3 space-y-6 mt-6 md:mt-0">
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Order Summary</h2>

        <div id="cart-items" class="space-y-3"></div>

        <div class="flex justify-between border-t pt-2">
          <span>Subtotal</span>
          <span>$<span id="subtotal">0.00</span></span>
        </div>
        <div class="flex justify-between">
          <span>Tax (<span id="tax-rate-label">0%</span>)</span>
          <span>$<span id="tax">0.00</span></span>
        </div>
        <div class="flex justify-between">
          <span>Shipping</span>
          <span>$<span id="shipping">0.00</span></span>
        </div>
        <div class="flex justify-between font-bold text-lg mt-2">
          <span>Total</span>
          <span>$<span id="total">0.00</span></span>
        </div>

        <label class="inline-flex items-center gap-2 mt-4">
          <input type="checkbox" id="terms" class="form-checkbox">
          I agree to the <a href="#" class="text-blue-600 underline">terms and conditions</a>
        </label>

        <button id="place-order" class="button w-full mt-4 bg-green-600 hover:bg-green-700 text-white py-2 rounded">Place Order</button>
      </div>
    </div>
  </div>
</div>

<style>
.input-field { width:100%; padding:0.5rem; border:1px solid #ddd; border-radius:6px; }
.button { transition:0.2s; cursor:pointer; }

.cart-line { display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
.cart-line-left { display:flex; align-items:center; gap:.75rem; min-width:0; }

/* Image wrapper to contain full image without cropping */
.cart-item-thumb {
  width: 64px;
  height: 64px;
  border-radius: 12px;
  background: #fff;
  border: 1px solid #e5e7eb;
  box-shadow: inset 0 0 0 1px rgba(0,0,0,0.02);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  /* small inner padding look without actual padding trimming the image */
}

.cart-item-img {
  max-width: 90%;
  max-height: 90%;
  width: auto;
  height: auto;
  object-fit: contain; /* show the whole image */
  display: block;
  image-rendering: auto;
}

/* Name wraps cleanly without pushing layout */
.cart-line-name { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
</style>

<script>
// -------- Cart rendering (uses localStorage set by /assets/cart.js) --------
function currency(n){ return Number(n||0).toFixed(2); }

async function loadCart() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  const cartContainer = document.getElementById("cart-items");
  cartContainer.innerHTML = "";

  if (!cart.length) {
    cartContainer.innerHTML = '<p class="text-gray-500 text-sm">Your cart is empty.</p>';
    document.getElementById("subtotal").textContent = "0.00";
    document.getElementById("tax").textContent = "0.00";
    document.getElementById("shipping").textContent = "0.00";
    document.getElementById("total").textContent = "0.00";
    // Still reflect configured tax rate in the label even if there is no tax amount
    fetch('/current_cart.php', { credentials:'include' })
      .then(r => r.ok ? r.json() : null)
      .then(data => {
        const rate = (data && data.summary && typeof data.summary.tax_rate === 'number') ? data.summary.tax_rate : 0;
        const pctStr = (Math.round(rate * 1000) / 10).toFixed(1).replace(/\.0$/, '');
        const lbl = document.getElementById('tax-rate-label'); if (lbl) lbl.textContent = pctStr + '%';
      })
      .catch(()=>{});
    return;
  }

  // Get unique product slugs from cart
  const slugs = [...new Set(cart.map(item => item.slug).filter(Boolean))];
  
  // Fetch product images from backend
  let productImages = {};
  if (slugs.length > 0) {
    console.log('Fetching images for slugs:', slugs);
    try {
      const response = await fetch('/get_product_images.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ slugs: slugs })
      });
      
      console.log('Response status:', response.status);
      if (response.ok) {
        productImages = await response.json();
        console.log('Product images received:', productImages);
      } else {
        console.error('Response not ok:', response.status, response.statusText);
      }
    } catch (error) {
      console.error('Error fetching product images:', error);
    }
  }

  let subtotal = 0;

  cart.forEach(item => {
    // Use backend image if available, otherwise fallback to frontend image or placeholder
    const backendProduct = productImages[item.slug];
    const imgSrc = backendProduct?.image || item.image || '/images/placeholder.png';
    console.log(`Item ${item.slug}: backend=${backendProduct?.image}, frontend=${item.image}, final=${imgSrc}`);
    const itemTotal = Number(item.price) * Number(item.qty || 1);
    subtotal += itemTotal;

    const row = document.createElement("div");
    row.className = "cart-line";

    row.innerHTML = `
      <div class="cart-line-left">
        <div class="cart-item-thumb">
          <img src="${imgSrc}" alt="${(item.name||'Item').replace(/"/g,'&quot;')}" class="cart-item-img">
        </div>
        <div class="cart-line-name">
          ${
            item.url
              ? `<a href="${item.url}" class="hover:underline">${item.name || 'Item'}</a>`
              : `<span>${item.name || 'Item'}</span>`
          }
          <span class="text-gray-500"> × ${item.qty || 1}</span>
        </div>
      </div>
      <span>$${currency(itemTotal)}</span>
    `;
    cartContainer.appendChild(row);
  });

  // Get server-authoritative totals (includes proper tax rate, shipping, discounts)
  fetch('/current_cart.php', { credentials:'include' })
    .then(r => r.ok ? r.json() : null)
    .then(data => {
      if (!data || !data.summary) {
        // Fallback to local calculation if server fails
        const tax = subtotal * 0.07;
        const shipping = cart.length > 0 ? 5 : 0;
        const total = subtotal + tax + shipping;
        
        document.getElementById('subtotal').textContent = currency(subtotal);
        document.getElementById('tax').textContent = currency(tax);
        document.getElementById('shipping').textContent = currency(shipping);
        document.getElementById('total').textContent = currency(total);
        document.getElementById('tax-rate-label').textContent = '7%';
        return;
      }
      
      const sum = data.summary;
      document.getElementById('subtotal').textContent = sum.subtotal;
      document.getElementById('tax').textContent = sum.tax;
      document.getElementById('shipping').textContent = sum.shipping;
      document.getElementById('total').textContent = sum.total;
      
      const rate = typeof sum.tax_rate === 'number' ? sum.tax_rate : 0;
      // Show up to 1 decimal; e.g., 0.065 -> 6.5%, 0.06 -> 6%
      let pctStr = (Math.round(rate * 1000) / 10).toFixed(1).replace(/\.0$/, '');
      const lbl = document.getElementById('tax-rate-label'); 
      if (lbl) lbl.textContent = pctStr + '%';
    })
    .catch(err => {
      console.error('Error fetching cart totals:', err);
      // Fallback to local calculation
      const tax = subtotal * 0.07;
      const shipping = cart.length > 0 ? 5 : 0;
      const total = subtotal + tax + shipping;
      
      document.getElementById('subtotal').textContent = currency(subtotal);
      document.getElementById('tax').textContent = currency(tax);
      document.getElementById('shipping').textContent = currency(shipping);
      document.getElementById('total').textContent = currency(total);
      document.getElementById('tax-rate-label').textContent = '7%';
    });
}

// Initial render + keep in sync if cart changes while on this page
document.addEventListener('DOMContentLoaded', () => loadCart());
window.addEventListener('cart:updated', () => loadCart());

// Copy shipping to billing
document.getElementById('same-as-shipping').addEventListener('change', function(){
  if(this.checked){
    document.getElementById('bill-address').value = document.getElementById('ship-address').value;
    document.getElementById('bill-city').value = document.getElementById('ship-city').value;
    document.getElementById('bill-state').value = document.getElementById('ship-state').value;
    document.getElementById('bill-zip').value = document.getElementById('ship-zip').value;
    document.getElementById('bill-country').value = document.getElementById('ship-country').value;
  }
});

// Place order button
document.getElementById('place-order').addEventListener('click', function(){
  if(!document.getElementById('terms').checked){
    alert('You must agree to terms before placing order.');
    return;
  }

  const shipping = {
    name: document.getElementById('name').value,
    email: document.getElementById('email').value,
    address: document.getElementById('ship-address').value,
    city: document.getElementById('ship-city').value,
    state: document.getElementById('ship-state').value,
    zip: document.getElementById('ship-zip').value,
    country: document.getElementById('ship-country').value
  };
  const billing = {
    address: document.getElementById('bill-address').value,
    city: document.getElementById('bill-city').value,
    state: document.getElementById('bill-state').value,
    zip: document.getElementById('bill-zip').value,
    country: document.getElementById('bill-country').value
  };

  fetch('/place_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({
      name: shipping.name,
      email: shipping.email,
      shipping, billing, method: 'manual'
    })
  }).then(r=>r.json()).then(res=>{
    if(res && res.success){ window.location.href = res.redirect || '/thank-you.php'; }
    else { alert('Could not place order.'); }
  }).catch(()=> alert('Network error.'));
});
</script>

<?php include 'footer.php'; ?>
