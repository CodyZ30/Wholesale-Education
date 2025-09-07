<?php
session_start();
include_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/check_blocked_ip.php';
include_once __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stickers - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<main class="mt-8">
    <!-- Hero Section (reverted structure, neutral colors) -->
    <div class="text-center mb-12">
        <h1 class="text-5xl font-bold mb-4 text-black">Sticker Collection</h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Show your love for fishing with our premium vinyl stickers. Perfect for tackle boxes, coolers, cars, and anywhere you want to display your passion for the sport.
        </p>
    </div>

    <!-- Program Options: Random vs Pick Your Own -->
    <section class="mb-16">
        <h2 class="text-4xl font-bold text-center mb-8 text-black">Choose Your Monthly Experience</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-black text-white rounded-2xl p-8 shadow-xl">
                <h3 class="text-2xl font-semibold mb-2">Random Picks</h3>
                <p class="text-gray-300 mb-6">Let us surprise you with 2 fresh designs every month. Our team curates the coolest, newest fishing stickers and ships them right to your door.</p>
                <button class="addToCartBtn bg-white text-black px-6 py-3 rounded-full font-semibold hover:bg-gray-100 transition"
                        data-id="15"
                        data-slug="monthly-sticker-subscription"
                        data-name="Monthly Sticker Subscription (Random Picks)"
                        data-price="4.99"
                        data-image="/images/new-sticker-images-coming-soon.png"
                        data-url="/stickers"
                        data-qty="1">
                    Subscribe – $4.99/mo
                </button>
            </div>
            <div class="bg-white rounded-2xl p-8 shadow-xl border border-gray-200">
                <h3 class="text-2xl font-semibold mb-2">Pick Your Own</h3>
                <p class="text-gray-700 mb-6">Prefer to choose? Pick your 2 favorites each month from our latest drops, and we’ll ship exactly what you select.</p>
                <a href="#coming-soon" class="inline-block bg-black text-white px-6 py-3 rounded-full font-semibold hover:bg-gray-900 transition">Log in to Pick</a>
                <p class="text-sm text-gray-500 mt-3">Selection management coming soon to your account dashboard.</p>
            </div>
        </div>
    </section>

    <!-- Best Sellers -->
    <section class="mb-16">
        <h2 class="text-4xl font-bold text-center mb-8 text-black">Best Sellers</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Classic Logo Sticker" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Classic Logo Sticker</h3>
                    <p class="text-gray-600 text-sm mb-4">Our iconic logo, printed on durable vinyl.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$2.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition"
                                data-id="5"
                                data-slug="sticker-classic-logo"
                                data-name="Classic Logo Sticker"
                                data-price="2.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">Add to Cart</button>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Minimalist Sticker" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Minimalist Sticker</h3>
                    <p class="text-gray-600 text-sm mb-4">Clean, modern mark for any surface.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$2.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition"
                                data-id="6"
                                data-slug="sticker-minimalist"
                                data-name="Minimalist Sticker"
                                data-price="2.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">Add to Cart</button>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Wave Crest Sticker" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Wave Crest Sticker</h3>
                    <p class="text-gray-600 text-sm mb-4">Bold waves for coolers, buckets, and more.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$2.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition"
                                data-id="7"
                                data-slug="sticker-wave-crest"
                                data-name="Wave Crest Sticker"
                                data-price="2.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- New This Month -->
    <section class="mb-16">
        <h2 class="text-4xl font-bold text-center mb-8 text-black">New This Month</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="New Drop #1" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">New Drop #1</h3>
                    <p class="text-gray-600 text-sm mb-4">Limited-run design available this month only.</p>
                    <span class="inline-block text-xs px-2 py-1 bg-black text-white rounded-full">Subscriber Early Access</span>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="New Drop #2" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">New Drop #2</h3>
                    <p class="text-gray-600 text-sm mb-4">Bold colorways inspired by coastal fishing.</p>
                    <span class="inline-block text-xs px-2 py-1 bg-black text-white rounded-full">Subscriber Early Access</span>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="New Drop #3" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">New Drop #3</h3>
                    <p class="text-gray-600 text-sm mb-4">Crisp lines, clean finish—built for the elements.</p>
                    <span class="inline-block text-xs px-2 py-1 bg-black text-white rounded-full">Subscriber Early Access</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Monthly Subscription Program (reverted structure, toned colors) -->
    <div class="bg-gray-900 text-white rounded-2xl p-8 mb-12">
        <div class="text-center">
            <h2 class="text-4xl font-bold mb-4">Monthly Sticker Program</h2>
            <p class="text-xl mb-6">Get 2 brand new stickers delivered to your door every month!</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white/10 rounded-lg p-6">
                    <i class="fas fa-calendar-alt text-3xl mb-3"></i>
                    <h3 class="text-xl font-semibold mb-2">Monthly Delivery</h3>
                    <p>Fresh stickers arrive at your door every month</p>
                </div>
                <div class="bg-white/10 rounded-lg p-6">
                    <i class="fas fa-star text-3xl mb-3"></i>
                    <h3 class="text-xl font-semibold mb-2">Most Up-To-Date</h3>
                    <p>Always get the latest designs first</p>
                </div>
                <div class="bg-white/10 rounded-lg p-6">
                    <i class="fas fa-gift text-3xl mb-3"></i>
                    <h3 class="text-xl font-semibold mb-2">Exclusive Designs</h3>
                    <p>Special stickers only available to subscribers</p>
                </div>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button class="addToCartBtn bg-white text-blue-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition-colors"
                        data-id="15"
                        data-slug="monthly-sticker-subscription"
                        data-name="Monthly Sticker Subscription"
                        data-price="4.99"
                        data-image="/images/new-sticker-images-coming-soon.png"
                        data-url="/stickers"
                        data-qty="1">
                    <i class="fas fa-shopping-cart mr-2"></i>Subscribe Now - $4.99/month
                </button>
                <a href="#subscription-details" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-full font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                    Learn More
                </a>
            </div>
        </div>
    </div>

    <!-- Individual Stickers Section -->
    <section class="mb-16">
        <h2 class="text-4xl font-bold text-center mb-8 text-black">Individual Stickers</h2>
        <p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto">
            Choose from our collection of premium vinyl stickers. Each one is designed to withstand the elements and look great on any surface.
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Classic Logo Sticker -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Classic Logo Sticker" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Classic Logo Sticker</h3>
                    <p class="text-gray-600 text-sm mb-4">Durable vinyl sticker with our classic logo. Perfect for any surface - tackle boxes, coolers, cars, and more.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$2.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition-colors"
                                data-id="5"
                                data-slug="sticker-classic-logo"
                                data-name="Classic Logo Sticker"
                                data-price="2.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Minimalist Sticker -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Minimalist Sticker" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Minimalist Sticker</h3>
                    <p class="text-gray-600 text-sm mb-4">A sleek, minimalist design that fits anywhere. Clean and modern look for the contemporary angler.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$2.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition-colors"
                                data-id="6"
                                data-slug="sticker-minimalist"
                                data-name="Minimalist Sticker"
                                data-price="2.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tagline Sticker -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Tagline Sticker" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Tagline Sticker</h3>
                    <p class="text-gray-600 text-sm mb-4">Our classic tagline sticker. A must-have for every angler who lives by the motto!</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$2.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition-colors"
                                data-id="7"
                                data-slug="sticker-tagline"
                                data-name="Tagline Sticker"
                                data-price="2.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Vibrant Design Sticker -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Vibrant Design Sticker" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Vibrant Design Sticker</h3>
                    <p class="text-gray-600 text-sm mb-4">A vibrant and eye-catching design to stand out on your gear. Bold colors for bold anglers.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$2.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition-colors"
                                data-id="8"
                                data-slug="sticker-vibrant"
                                data-name="Vibrant Design Sticker"
                                data-price="2.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Small Logo Sticker -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Small Logo Sticker" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Small Logo Sticker</h3>
                    <p class="text-gray-600 text-sm mb-4">A small, clean logo sticker, perfect for subtle branding on smaller items.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$1.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition-colors"
                                data-id="9"
                                data-slug="sticker-small-logo"
                                data-name="Small Logo Sticker"
                                data-price="1.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Fun Design Sticker -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Fun Design Sticker" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Fun Design Sticker</h3>
                    <p class="text-gray-600 text-sm mb-4">A bold and fun sticker for the angler with a sense of humor!</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$2.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition-colors"
                                data-id="10"
                                data-slug="sticker-fun-design"
                                data-name="Fun Design Sticker"
                                data-price="2.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sticker Packages Section -->
    <section class="mb-16">
        <h2 class="text-4xl font-bold text-center mb-8 text-black">Sticker Packages</h2>
        <p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto">
            Get more value with our sticker packages! Perfect for sharing with friends or building your collection.
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- 2-Pack -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Sticker 2-Pack" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Sticker 2-Pack</h3>
                    <p class="text-gray-600 text-sm mb-4">Get 2 random stickers from our collection. Perfect for sharing with a fishing buddy!</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$4.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition-colors"
                                data-id="11"
                                data-slug="sticker-2-pack"
                                data-name="Sticker 2-Pack"
                                data-price="4.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3-Pack -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Sticker 3-Pack" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Sticker 3-Pack</h3>
                    <p class="text-gray-600 text-sm mb-4">Get 3 random stickers from our collection. Great value for the sticker enthusiast!</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$6.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition-colors"
                                data-id="12"
                                data-slug="sticker-3-pack"
                                data-name="Sticker 3-Pack"
                                data-price="6.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- 5-Pack -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Sticker 5-Pack" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Sticker 5-Pack</h3>
                    <p class="text-gray-600 text-sm mb-4">Get 5 random stickers from our collection. Perfect for decking out all your gear!</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$9.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition-colors"
                                data-id="13"
                                data-slug="sticker-5-pack"
                                data-name="Sticker 5-Pack"
                                data-price="9.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>

            <!-- 10-Pack -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                <div class="w-full h-48 bg-white flex items-center justify-center p-4">
                    <img src="/images/new-sticker-images-coming-soon.png" alt="Sticker 10-Pack" class="w-full h-full object-contain">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Sticker 10-Pack</h3>
                    <p class="text-gray-600 text-sm mb-4">Get 10 random stickers from our collection. The ultimate sticker bundle for the serious collector!</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-black">$17.99</span>
                        <button class="addToCartBtn bg-black text-white px-6 py-2 rounded-full hover:bg-gray-800 transition-colors"
                                data-id="14"
                                data-slug="sticker-10-pack"
                                data-name="Sticker 10-Pack"
                                data-price="17.99"
                                data-image="/images/new-sticker-images-coming-soon.png"
                                data-url="/stickers"
                                data-qty="1">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Subscription Details Section -->
    <section id="subscription-details" class="mb-16">
        <div class="bg-gray-50 rounded-2xl p-8">
            <h2 class="text-4xl font-bold text-center mb-8 text-black">Monthly Subscription Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-2xl font-semibold mb-4">How It Works</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span>Subscribe once for $4.99/month</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span>Receive 2 brand new stickers every month</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span>Always get the most up-to-date designs first</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span>Exclusive subscriber-only designs</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span>Cancel anytime - no commitment</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-2xl font-semibold mb-4">Benefits</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-star text-yellow-500 mt-1 mr-3"></i>
                            <span>Never miss a new design</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-star text-yellow-500 mt-1 mr-3"></i>
                            <span>Build your collection automatically</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-star text-yellow-500 mt-1 mr-3"></i>
                            <span>Perfect for gift-giving</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-star text-yellow-500 mt-1 mr-3"></i>
                            <span>Free shipping on all subscription orders</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-star text-yellow-500 mt-1 mr-3"></i>
                            <span>Special subscriber discounts on other products</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="text-center mt-8">
                <p class="text-gray-600 mb-4">Ready to start your sticker collection?</p>
                <button class="addToCartBtn bg-blue-600 text-white px-8 py-3 rounded-full font-semibold hover:bg-blue-700 transition-colors"
                        data-id="15"
                        data-slug="monthly-sticker-subscription"
                        data-name="Monthly Sticker Subscription"
                        data-price="4.99"
                        data-image="/images/new-sticker-images-coming-soon.png"
                        data-url="/stickers"
                        data-qty="1">
                    <i class="fas fa-shopping-cart mr-2"></i>Start Subscription - $4.99/month
                </button>
            </div>
        </div>
    </section>

    <!-- Quality & Features Section -->
    <section class="mb-16">
        <h2 class="text-4xl font-bold text-center mb-8 text-black">Premium Quality Stickers</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Weather Resistant</h3>
                <p class="text-gray-600">Our vinyl stickers are designed to withstand sun, rain, and all weather conditions.</p>
            </div>
            
            <div class="text-center">
                <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-palette text-2xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Vibrant Colors</h3>
                <p class="text-gray-600">High-quality printing ensures your stickers stay bright and colorful for years.</p>
            </div>
            
            <div class="text-center">
                <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-sticky-note text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Easy Application</h3>
                <p class="text-gray-600">Simple peel-and-stick application that works on any clean, smooth surface.</p>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
