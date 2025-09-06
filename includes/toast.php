<!-- Global Toast (auto-wired by /assets/cart.js) -->
<div id="cart-toast" aria-live="polite" aria-atomic="true" style="position:fixed;bottom:20px;right:20px;z-index:1050;display:none;">
  <div class="toast" style="background:#fff;border-radius:8px;padding:1rem;box-shadow:0 4px 12px rgba(0,0,0,.15);display:flex;align-items:center;transform:translateX(100%);opacity:0;transition:transform .3s,opacity .3s;">
    <div class="thumb" style="margin-right:15px;"><img id="cart-toast-img" alt="" style="width:60px;height:60px;object-fit:contain;"></div>
    <div class="info" style="flex-grow:1;">
      <div class="title" style="font-weight:700;font-size:1rem;color:#111;">Successfully Added To Cart</div>
      <div class="desc" id="cart-toast-desc" style="color:#666;font-size:.875rem;"></div>
    </div>
    <div class="actions" style="margin-left:20px;">
      <a href="/cart" style="display:block;margin-top:5px;text-decoration:none;font-size:.8rem;font-weight:600;padding:5px 10px;border-radius:4px;background:#f1f5f9;color:#334155;">View Cart</a>
      <a href="/checkout" style="display:block;margin-top:5px;text-decoration:none;font-size:.8rem;font-weight:600;padding:5px 10px;border-radius:4px;background:#111;color:#fff;text-align:center;">Checkout</a>
    </div>
  </div>
</div>

<!-- Optional Center Popup (animated check) -->
<div id="cart-popup" role="dialog" aria-modal="true" aria-label="Cart Success"
     style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;padding:30px;border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,.2);display:none;flex-direction:column;align-items:center;text-align:center;z-index:1100;opacity:0;transition:opacity .3s;">
  <div style="width:100px;height:100px;position:relative;">
    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"
         style="width:100%;height:100%;stroke:#4CAF50;stroke-width:5;stroke-linecap:round;stroke-linejoin:round;fill:none;">
      <circle cx="26" cy="26" r="25" style="stroke-dasharray:166;stroke-dashoffset:166;"></circle>
      <path d="M14 27l7 7 17-17" style="stroke-dasharray:48;stroke-dashoffset:48;"></path>
    </svg>
  </div>
  <div style="font-size:28px;font-weight:800;color:#111;">Successfully Added To Cart</div>
  <div style="font-size:16px;color:#4b5563;">Your item has been added to the cart.</div>
</div>

<style>
#cart-toast .toast.show{transform:translateX(0);opacity:1;}
#cart-popup.show{display:flex;opacity:1;}
</style>
