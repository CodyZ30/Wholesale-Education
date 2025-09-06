<?php include_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> Product Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        /* Custom styles for the thumbnail border */
        .thumbnail-image.selected {
            border: 2px solid #333;
            opacity: 1;
        }
    </style>
</head>
<body class="text-gray-800 antialiased">

    <?php include 'header.php'; ?>
<body class="antialiased">

    <main class="container mx-auto px-4 md:px-6 py-8 md:py-12">
        <!-- Main Product Section (Amazon-style hero) -->
        <section class="mb-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-16">
                <!-- Image Gallery -->
                <div class="flex flex-col-reverse md:flex-row gap-4 items-start">
                    <!-- Thumbnails -->
                    <div class="flex flex-row justify-center space-x-2 w-full md:flex-col md:w-20 md:space-x-0 md:space-y-3">
                        <img src="https://placehold.co/200x200/ffffff/000000?text=View+1" alt="Product thumbnail view 1" class="thumbnail-image w-1/4 md:w-full rounded-xl shadow-md cursor-pointer hover:opacity-75 transition-opacity duration-300 selected" data-full-size-url="https://placehold.co/800x600/ffffff/000000?text=Main+Product+Image">
                        <img src="https://placehold.co/200x200/ffffff/000000?text=View+2" alt="Product thumbnail view 2" class="thumbnail-image w-1/4 md:w-full rounded-xl shadow-md cursor-pointer hover:opacity-75 transition-opacity duration-300" data-full-size-url="https://placehold.co/800x600/ffffff/000000?text=Side+View">
                        <img src="https://placehold.co/200x200/ffffff/000000?text=View+3" alt="Product thumbnail view 3" class="thumbnail-image w-1/4 md:w-full rounded-xl shadow-md cursor-pointer hover:opacity-75 transition-opacity duration-300" data-full-size-url="https://placehold.co/800x600/ffffff/000000?text=Detailed+Shot">
                        <img src="https://placehold.co/200x200/ffffff/000000?text=View+4" alt="Product thumbnail view 4" class="thumbnail-image w-1/4 md:w-full rounded-xl shadow-md cursor-pointer hover:opacity-75 transition-opacity duration-300" data-full-size-url="https://placehold.co/800x600/ffffff/000000?text=Action+Shot">
                    </div>
                    <!-- Main Image -->
                    <div class="flex-grow">
                        <img id="main-product-image" src="https://placehold.co/800x600/ffffff/000000?text=Main+Product+Image" alt="Main product view" class="w-full rounded-2xl shadow-xl">
                    </div>
                </div>

                <!-- Product Info & CTA Section -->
                <div class="lg:sticky lg:top-24 self-start text-center md:text-left">
                    <h1 class="text-3xl md:text-4xl font-extrabold mb-2 tracking-tight">The [Product Name]</h1>
                    
                    <!-- Ratings -->
                    <div class="flex items-center justify-center md:justify-start mb-4">
                        <div class="text-yellow-400 flex">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 5.143l2.364 4.864 5.385.741-3.903 3.75 1.05 5.343-4.896-2.58-4.896 2.58 1.05-5.343-3.903-3.75 5.385-.741z"/></svg>
                        </div>
                        <a href="#" class="text-sm text-gray-800 ml-2 hover:underline">1,245 ratings</a>
                    </div>
                    
                    <!-- Price and Shipping Info -->
                    <div class="bg-gray-100 rounded-xl p-4 mb-6 shadow-sm">
                        <span class="text-3xl font-bold text-gray-900">$[Price]</span>
                        <p class="text-sm text-gray-700 mt-2">FREE shipping on orders over $50.</p>
                        <p class="text-sm text-gray-700 font-semibold mt-1">Standard shipping is an additional $5.99 for orders under $50.</p>
                    </div>

                    <!-- Quick Bullet Points -->
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-8 text-left mx-auto md:mx-0">
                        <li><span class="font-bold">Effortless Casting:</span> Designed for long, precise casts with minimal effort.</li>
                        <li><span class="font-bold">Unmatched Sensitivity:</span> A responsive graphite composite blank that transmits every bite, no matter how subtle.</li>
                        <li><span class="font-bold">Durable Construction:</span> Corrosion-resistant components for both fresh and saltwater use.</li>
                        <li><span class="font-bold">Ergonomic Grip:</span> Split-grip cork handle provides comfort and control during long sessions.</li>
                    </ul>

                    <!-- CTA Section -->
                    <div class="flex flex-col md:flex-row items-center justify-center md:justify-start space-y-4 md:space-y-0 md:space-x-4">
                        <div class="flex items-center space-x-2">
                            <button class="bg-gray-200 text-gray-700 w-10 h-10 rounded-full flex items-center justify-center text-xl font-bold hover:bg-gray-300 transition-colors duration-200">-</button>
                            <input type="text" value="1" class="w-12 h-10 text-center rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-black font-medium">
                            <button class="bg-gray-200 text-gray-700 w-10 h-10 rounded-full flex items-center justify-center text-xl font-bold hover:bg-gray-300 transition-colors duration-200">+</button>
                        </div>
                        <button class="w-full md:w-auto px-12 py-4 bg-black text-white font-bold text-lg rounded-full shadow-lg hover:bg-gray-800 transition-colors duration-300 transform hover:scale-105">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Product Description & A+ Content Section -->
        <section class="mb-16">
            <h2 class="text-3xl font-bold text-center mb-10">Product Details & Specifications</h2>
            <div class="bg-white rounded-2xl p-8 md:p-12 shadow-xl border-t-4 border-black">
                <p class="text-gray-700 leading-relaxed mb-8 text-center max-w-4xl mx-auto">
                    This is where you can provide a much longer, more detailed description, similar to the "From the Manufacturer" section on Amazon. Use this space to tell a story about your product and its benefits. Highlight the unique features that make it a premium choice for serious anglers.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center mb-12">
                    <div>
                        <h3 class="text-2xl font-bold text-black mb-4">Precision Engineering</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Every component is crafted with meticulous care to ensure a flawless fishing experience. From the ergonomic grip to the ultra-smooth bearings, we've obsessed over every detail so you don't have to.
                        </p>
                    </div>
                    <img src="https://placehold.co/600x400/ffffff/000000?text=Component+Breakdown" alt="Detailed breakdown of a product component" class="w-full rounded-xl shadow-md">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center md:flex-row-reverse mb-12">
                    <div>
                        <h3 class="text-2xl font-bold text-black mb-4">Built for the Toughest Conditions</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Our materials are tested in the most demanding environments to guarantee durability and performance. Rain or shine, saltwater or fresh, your gear will stand up to the challenge.
                        </p>
                    </div>
                    <img src="https://placehold.co/600x400/ffffff/000000?text=Weather+Resistant+Materials" alt="Gear used in tough weather conditions" class="w-full rounded-xl shadow-md">
                </div>
            </div>
        </section>

        <!-- Technical Specifications Table -->
        <section class="bg-white rounded-2xl p-8 md:p-12 shadow-xl mb-16">
            <h2 class="text-3xl font-bold text-center mb-8">Specifications at a Glance</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left table-auto">
                    <thead>
                        <tr class="bg-gray-100 rounded-lg">
                            <th class="px-4 py-3 font-semibold text-gray-700 rounded-tl-lg">Attribute</th>
                            <th class="px-4 py-3 font-semibold text-gray-700">Value</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 rounded-tr-lg">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-200">
                            <td class="px-4 py-3 font-medium">Product Weight</td>
                            <td class="px-4 py-3">12.5 oz</td>
                            <td class="px-4 py-3">Lightweight design for all-day comfort.</td>
                        </tr>
                        <tr class="border-b border-gray-200">
                            <td class="px-4 py-3 font-medium">Product Dimensions</td>
                            <td class="px-4 py-3">7' 2" (86 inches)</td>
                            <td class="px-4 py-3">Ideal length for long casts and powerful hooksets.</td>
                        </tr>
                        <tr class="border-b border-gray-200">
                            <td class="px-4 py-3 font-medium">Material</td>
                            <td class="px-4 py-3">Graphite Composite</td>
                            <td class="px-4 py-3">High-modulus blank for strength and sensitivity.</td>
                        </tr>
                        <tr class="border-b border-gray-200">
                            <td class="px-4 py-3 font-medium">Line Rating</td>
                            <td class="px-4 py-3">8-17 lb</td>
                            <td class="px-4 py-3">Versatile line weight for various fishing techniques.</td>
                        </tr>
                         <tr class="border-b border-gray-200">
                            <td class="px-4 py-3 font-medium">UPC Code</td>
                            <td class="px-4 py-3">8-94136-11823-3</td>
                            <td class="px-4 py-3">Standard Universal Product Code for tracking and retail.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Customer Reviews Section -->
        <section class="mb-16">
            <h2 class="text-3xl font-bold text-center mb-10">Customer Reviews</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Review Card 1 -->
                <div class="bg-gray-100 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center mb-2">
                        <div class="text-yellow-400 flex">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 5.143l2.364 4.864 5.385.741-3.903 3.75 1.05 5.343-4.896-2.58-4.896 2.58 1.05-5.343-3.903-3.75 5.385-.741z"/></svg>
                        </div>
                    </div>
                    <p class="font-bold mb-2">"Incredible performance!"</p>
                    <p class="text-sm text-gray-700 leading-relaxed">This rod is super lightweight and sensitive. I can feel every little nibble, and it handles big fish with no problem. Couldn't be happier with this purchase.</p>
                </div>
                <!-- Review Card 2 -->
                <div class="bg-gray-100 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center mb-2">
                        <div class="text-yellow-400 flex">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                        </div>
                    </div>
                    <p class="font-bold mb-2">"My new favorite rod!"</p>
                    <p class="text-sm text-gray-700 leading-relaxed">The quality for the price is outstanding. It feels great in my hand, and the tip action is perfect for the kind of bass fishing I do. Highly recommend this to anyone.</p>
                </div>
                <!-- Review Card 3 -->
                <div class="bg-gray-100 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center mb-2">
                        <div class="text-yellow-400 flex">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.92-7.416 3.92 1.48-8.279-6.064-5.828 8.332-1.151z"/></svg>
                        </div>
                    </div>
                    <p class="font-bold mb-2">"Great value, great performance"</p>
                    <p class="text-sm text-gray-700 leading-relaxed">I've used rods that cost twice as much, and this one performs just as well. It's a fantastic value for the money, and it's built to last.</p>
                </div>
            </div>
        </section>

        <!-- Related Products Section -->
        <section class="mb-16">
            <h2 class="text-3xl font-bold text-center mb-8">Customers Also Bought</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Product Card 1 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition-transform hover:scale-105 duration-300">
                    <img src="https://placehold.co/400x300/ffffff/000000?text=Related+Item+1" alt="Related product 1" class="w-full">
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1">[Related Product Name]</h3>
                        <p class="text-gray-600 text-sm mb-2">[Short description]</p>
                        <p class="font-bold text-black">$[Price]</p>
                    </div>
                </div>
                <!-- Product Card 2 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition-transform hover:scale-105 duration-300">
                    <img src="https://placehold.co/400x300/ffffff/000000?text=Related+Item+2" alt="Related product 2" class="w-full">
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1">[Related Product Name]</h3>
                        <p class="text-gray-600 text-sm mb-2">[Short description]</p>
                        <p class="font-bold text-black">$[Price]</p>
                    </div>
                </div>
                <!-- Product Card 3 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition-transform hover:scale-105 duration-300">
                    <img src="https://placehold.co/400x300/ffffff/000000?text=Related+Item+3" alt="Related product 3" class="w-full">
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1">[Related Product Name]</h3>
                        <p class="text-gray-600 text-sm mb-2">[Short description]</p>
                        <p class="font-bold text-black">$[Price]</p>
                    </div>
                </div>
                <!-- Product Card 4 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition-transform hover:scale-105 duration-300">
                    <img src="https://placehold.co/400x300/ffffff/000000?text=Related+Item+4" alt="Related product 4" class="w-full">
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1">[Related Product Name]</h3>
                        <p class="text-gray-600 text-sm mb-2">[Short description]</p>
                        <p class="font-bold text-black">$[Price]</p>
                    </div>
                </div>
            </div>
        </section>

    </main>

 <?php include 'footer.php'; ?>

    <script>
        const mainImage = document.getElementById('main-product-image');
        const thumbnails = document.querySelectorAll('.thumbnail-image');

        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', () => {
                const newImageUrl = thumbnail.getAttribute('data-full-size-url');
                
                // Update main image source
                mainImage.src = newImageUrl;

                // Remove 'selected' class from all thumbnails
                thumbnails.forEach(t => t.classList.remove('selected'));
                
                // Add 'selected' class to the clicked thumbnail
                thumbnail.classList.add('selected');
            });
        });
    </script>

</body>
</html>
