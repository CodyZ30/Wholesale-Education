<?php
session_start();
include_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Debug - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/cart.js" defer></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Cart Debug Page</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Test Products -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Test Products</h2>
                
                <div class="space-y-4">
                    <div class="border p-4 rounded">
                        <h3 class="font-medium">The Keeper Gauge</h3>
                        <p class="text-gray-600">$4.99</p>
                        <button class="addToCartBtn mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                data-id="1"
                                data-slug="the-keeper-gauge"
                                data-name="The Keeper Gauge"
                                data-price="4.99"
                                data-image="/images/gottafish-product-01.png"
                                data-url="/product/the-keeper-gauge"
                                data-qty="1">
                            Add to Cart
                        </button>
                    </div>
                    
                    <div class="border p-4 rounded">
                        <h3 class="font-medium">The Bucket Station</h3>
                        <p class="text-gray-600">$49.99</p>
                        <button class="addToCartBtn mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                data-id="2"
                                data-slug="the-bucket-station"
                                data-name="The Bucket Station"
                                data-price="49.99"
                                data-image="/images/gottafish-product-02.png"
                                data-url="/product/the-bucket-station"
                                data-qty="1">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Cart Debug Info -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Cart Debug Info</h2>
                
                <div class="space-y-4">
                    <div>
                        <h3 class="font-medium">Local Storage Cart:</h3>
                        <pre id="localStorage-cart" class="bg-gray-100 p-2 rounded text-sm overflow-auto max-h-32"></pre>
                    </div>
                    
                    <div>
                        <h3 class="font-medium">Server Session Cart:</h3>
                        <pre id="server-cart" class="bg-gray-100 p-2 rounded text-sm overflow-auto max-h-32"></pre>
                    </div>
                    
                    <div>
                        <h3 class="font-medium">Cart Count:</h3>
                        <span id="cart-count" class="font-bold text-lg">0</span>
                    </div>
                    
                    <div>
                        <h3 class="font-medium">Cart Subtotal:</h3>
                        <span id="cart-subtotal" class="font-bold text-lg">$0.00</span>
                    </div>
                    
                    <div class="flex gap-2">
                        <button onclick="refreshDebug()" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                            Refresh
                        </button>
                        <button onclick="clearCart()" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                            Clear Cart
                        </button>
                        <button onclick="testAddItem()" class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
                            Test Add Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cart Items Display -->
        <div class="mt-6 bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Cart Items Display</h2>
            <div id="cart-items-display" class="space-y-2">
                <p class="text-gray-500">No items in cart</p>
            </div>
        </div>
    </div>

    <script>
        function refreshDebug() {
            // Update localStorage cart display
            const localCart = JSON.parse(localStorage.getItem('cart') || '[]');
            document.getElementById('localStorage-cart').textContent = JSON.stringify(localCart, null, 2);
            
            // Update cart count and subtotal
            if (window.Cart) {
                document.getElementById('cart-count').textContent = window.Cart.count();
                document.getElementById('cart-subtotal').textContent = '$' + window.Cart.subtotal().toFixed(2);
            }
            
            // Update cart items display
            updateCartItemsDisplay();
            
            // Fetch server cart
            fetch('/current_cart.php', { credentials: 'include' })
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    document.getElementById('server-cart').textContent = JSON.stringify(data, null, 2);
                })
                .catch(err => {
                    document.getElementById('server-cart').textContent = 'Error: ' + err.message;
                });
        }
        
        function clearCart() {
            if (window.Cart) {
                window.Cart.set([]);
                refreshDebug();
            }
        }
        
        function testAddItem() {
            if (window.Cart) {
                window.Cart.add({
                    id: 'test-item',
                    slug: 'test-item',
                    name: 'Test Item',
                    price: 9.99,
                    image: '/images/gottafish-product-01.png',
                    url: '/product/test-item',
                    qty: 1
                });
                refreshDebug();
            }
        }
        
        async function updateCartItemsDisplay() {
            const cart = window.Cart ? window.Cart.get() : [];
            const container = document.getElementById('cart-items-display');
            
            if (cart.length === 0) {
                container.innerHTML = '<p class="text-gray-500">No items in cart</p>';
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
            
            container.innerHTML = cart.map(item => {
                // Use backend image if available, otherwise fallback to frontend image or placeholder
                const backendProduct = productImages[item.slug];
                const imgSrc = backendProduct?.image || item.image || '/images/placeholder.png';
                
                return `
                    <div class="flex items-center justify-between p-3 border rounded">
                        <div class="flex items-center gap-3">
                            <img src="${imgSrc}" alt="${item.name}" class="w-12 h-12 object-contain">
                            <div>
                                <div class="font-medium">${item.name}</div>
                                <div class="text-sm text-gray-500">$${item.price} × ${item.qty}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold">$${(item.price * item.qty).toFixed(2)}</div>
                            <button onclick="removeItem('${item.id}')" class="text-sm text-red-500 hover:underline">Remove</button>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function removeItem(id) {
            if (window.Cart) {
                window.Cart.remove(id);
                refreshDebug();
            }
        }
        
        // Listen for cart updates
        window.addEventListener('cart:updated', refreshDebug);
        
        // Initial load
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(refreshDebug, 1000); // Wait for cart.js to load
        });
    </script>
</body>
</html>
