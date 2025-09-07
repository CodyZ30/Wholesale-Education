// functions.js
// ===== CART =====
function getCart(){ return JSON.parse(localStorage.getItem("cart") || "[]"); }
function saveCart(cart){ localStorage.setItem("cart", JSON.stringify(cart)); renderCart(); }
function renderCart() {
  //... rest of the renderCart function
}

// ===== TOAST & CENTERED POPUP =====
const toastRoot = document.getElementById('cart-toast');
const toastEl = toastRoot.querySelector('.toast');
//... rest of the popup functions

// ===== WISHLIST =====
function toggleWishlist(id){
  //...
}

// ===== COMPARE =====
function toggleCompare(slug) {
  //...
}

function renderCompare() {
  //...
}

// ===== INIT =====
document.addEventListener("DOMContentLoaded",()=>{
  renderCompare();
  renderCart();
  // ... and any other initialization functions
});