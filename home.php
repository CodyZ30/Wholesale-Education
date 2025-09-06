<?php include_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo SITE_NAME; ?> Home</title>
  <style>
    /* Normalize product cards to match /shop */
    .product-card{display:flex;flex-direction:column;height:100%;background:#fff}
    .product-card .w-full.h-48{height:11rem!important}
    .product-card .p-6{display:flex;flex-direction:column;flex:1 1 auto}
    .product-info{min-height:6.25rem}
    .product-card .actions,.product-card .mt-4{margin-top:auto}
    @media(min-width:768px){.product-card .actions{align-items:flex-end}}
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="mt-8">
    <section id="hero-carousel-section" class="relative w-full h-[600px] overflow-hidden rounded-[4rem] shadow-2xl">
        <div class="hero-carousel w-full h-full flex transition-transform duration-700 ease-in-out">
            <div class="hero-slide w-full h-full flex-shrink-0 relative bg-cover bg-center" style="background-image: url('images/hero01.png');">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black opacity-70"></div>
                <div class="relative z-10 flex flex-col items-center justify-center h-full text-white text-center px-4">
                    <h1 class="text-5xl md:text-7xl font-bold font-extrabold tracking-tight drop-shadow-lg">The Lure of the Wild</h1>
                    <p class="mt-4 max-w-2xl text-lg md:text-xl  font-light drop-shadow">Crafting the tools that connect you with nature's rhythm.</p>
                    <a href="#featured-products" class="mt-8 px-10 py-4 bg-black text-white text-lg font-semibold rounded-full shadow-lg hover:bg-gray-800 transition-all transform hover:scale-105">Find Your Gear</a>
                </div>
            </div>
            <div class="hero-slide w-full h-full flex-shrink-0 relative bg-cover bg-center" style="background-image: url('images/hero02.png');">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black opacity-70"></div>
                <div class="relative z-10 flex flex-col items-center justify-center h-full text-white text-center px-4">
                    <h1 class="text-5xl md:text-7xl font-bold font-extrabold tracking-tight drop-shadow-lg">Precision & Passion</h1>
                    <p class="mt-4 max-w-2xl text-lg md:text-xl  font-light drop-shadow">Handcrafted gear for the serious angler, built to last a lifetime.</p>
                    <a href="#featured-products" class="mt-8 px-10 py-4 bg-black text-white text-lg font-semibold rounded-full shadow-lg hover:bg-gray-800 transition-all transform hover:scale-105">Explore Collection</a>
                </div>
            </div>
            <div class="hero-slide w-full h-full flex-shrink-0 relative bg-cover bg-center" style="background-image: url('images/hero03.png');">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black opacity-70"></div>
                <div class="relative z-10 flex flex-col items-center justify-center h-full text-white text-center px-4">
                    <h1 class="text-5xl md:text-7xl font-bold font-extrabold tracking-tight drop-shadow-lg">Protecting Our Waters 🌱</h1>
                    <p class="mt-4 max-w-2xl text-lg md:text-xl  font-light drop-shadow">Committed to sustainable practices for future generations of anglers.</p>
                    <a href="#about-us" class="mt-8 px-10 py-4 bg-black text-white text-lg font-semibold rounded-full shadow-lg hover:bg-gray-800 transition-all transform hover:scale-105">Learn More</a>
                </div>
            </div>
        </div>
        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex space-x-2 z-20">
            <button class="h-3 w-3 rounded-full bg-white opacity-50 hero-dot transition-opacity duration-300"></button>
            <button class="h-3 w-3 rounded-full bg-white opacity-50 hero-dot transition-opacity duration-300"></button>
            <button class="h-3 w-3 rounded-full bg-white opacity-50 hero-dot transition-opacity duration-300"></button>
        </div>
    </section>
</main>
<section id="featured-products" class="py-20 px-4 bg-[#f8f9fa] mt-8 rounded-[4rem] shadow-2xl">
  <div class="container mx-auto">
    <h2 class="text-4xl md:text-5xl font-bold font-bold text-center mb-12 text-black">Featured Products</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

      <!-- The Keeper Gauge -->
      <div class="product-card bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-[#1e5285]">
        <a href="/product/the-keeper-gauge" class="w-full flex flex-col flex-grow">
          <div class="w-full h-48 bg-white flex items-center justify-center">
            <img src="/images/gottafish-product-01.png" alt="The Keeper Gauge" class="w-full h-full object-contain">
          </div>
          <div class="p-6 flex flex-col flex-grow text-center text-gray-900">
            <div class="flex-grow">
              <h3 class="text-xl font-bold font-bold mb-2">The Keeper Gauge</h3>
              <p class="text-gray-600 text-sm  mb-4">
                A professional-grade measuring tool designed for serious anglers. It provides a quick and accurate way to check for keeper-sized clams (1 1/2 in) and blue crabs (4 1/2 in), ensuring you stay within legal limits with ease. Proudly made in the USA.
              </p>
            </div>
            <div><span class="text-2xl font-extrabold block text-gray-900">$4.99</span></div>
          </div>
        </a>
        <button
          type="button"
          class="addToCartBtn mt-4 px-6 py-3 w-full bg-black text-white font-semibold rounded-full hover:bg-gray-800 transition-colors"
          data-id="1"
          data-slug="the-keeper-gauge"
          data-name="The Keeper Gauge"
          data-price="4.99"
          data-image="/images/gottafish-product-01.png"
          data-url="/product/the-keeper-gauge"
          data-qty="1"
        >Add to Cart</button>
      </div>

      <!-- The Bucket Station -->
      <div class="product-card bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-[#1e5285]">
        <a href="/product/the-bucket-station" class="w-full flex flex-col flex-grow">
          <div class="w-full h-48 bg-white flex items-center justify-center">
            <img src="/images/gottafish-product-02.png" alt="The Bucket Station" class="w-full h-full object-contain">
          </div>
          <div class="p-6 flex flex-col flex-grow text-center text-gray-900">
            <div class="flex-grow">
              <h3 class="text-xl font-bold mb-2">The Bucket Station</h3>
              <p class="text-gray-600 text-sm mb-4">
                Crafted from durable stainless steel, this versatile bucket station features an adjustable design to fit various bucket widths. Proudly made in the USA. (Bucket and accessories not included.)
              </p>
            </div>
            <div><span class="text-2xl font-extrabold block text-gray-900">$49.99</span></div>
          </div>
        </a>
        <button
          type="button"
          class="addToCartBtn mt-4 px-6 py-3 w-full bg-black text-white font-semibold rounded-full hover:bg-gray-800 transition-colors"
          data-id="2"
          data-slug="the-bucket-station"
          data-name="The Bucket Station"
          data-price="49.99"
          data-image="/images/gottafish-product-02.png"
          data-url="/product/the-bucket-station"
          data-qty="1"
        >Add to Cart</button>
      </div>

      <!-- The Lucky Bobber -->
      <div class="product-card bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-[#1e5285]">
        <a href="/product/the-lucky-bobber" class="w-full flex flex-col flex-grow">
          <div class="w-full h-48 bg-white flex items-center justify-center">
            <img src="/images/gottafish-product-03.png" alt="The Lucky Bobber" class="w-full h-full object-contain">
          </div>
          <div class="p-6 flex flex-col flex-grow text-center text-gray-900">
            <div class="flex-grow">
              <h3 class="text-xl font-bold font-bold mb-2">The Lucky Bobber</h3>
              <p class="text-gray-600 text-sm  mb-4">
                A professional-grade fishing float designed for the serious angler. It provides unmatched sensitivity and durability for all your fishing needs, ensuring you have the best gear for every cast. Proudly made in the USA.
              </p>
            </div>
            <div><span class="text-2xl font-extrabold block text-gray-900">$4.99</span></div>
          </div>
        </a>
        <button
          type="button"
          class="addToCartBtn mt-4 px-6 py-3 w-full bg-black text-white font-semibold rounded-full hover:bg-gray-800 transition-colors"
          data-id="3"
          data-slug="the-lucky-bobber"
          data-name="The Lucky Bobber"
          data-price="4.99"
          data-image="/images/gottafish-product-03.png"
          data-url="/product/the-lucky-bobber"
          data-qty="1"
        >Add to Cart</button>
      </div>

      <!-- The Command Station -->
      <div class="product-card bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-[#1e5285]">
        <a href="/product/the-command-station" class="w-full flex flex-col flex-grow">
          <div class="w-full h-48 bg-white flex items-center justify-center">
            <img src="/images/gottafish-product-04.png" alt="The Command Station" class="w-full h-full object-contain">
          </div>
          <div class="p-6 flex flex-col flex-grow text-center text-gray-900">
            <div class="flex-grow">
              <h3 class="text-xl font-bold font-bold mb-2">The Command Station</h3>
              <p class="text-gray-600 text-sm  mb-4">
                The Command Station is the ultimate tool for boat organization, designed for the serious angler. This station helps you get organized and fish more efficiently, so you can spend less time searching for gear and more time casting, ensuring you have the best setup for every trip. Proudly made in the USA.
              </p>
            </div>
            <div><span class="text-2xl font-extrabold block text-gray-900">$199.99</span></div>
          </div>
        </a>
        <button
          type="button"
          class="addToCartBtn mt-4 px-6 py-3 w-full bg-black text-white font-semibold rounded-full hover:bg-gray-800 transition-colors"
          data-id="4"
          data-slug="the-command-station"
          data-name="The Command Station"
          data-price="199.99"
          data-image="/images/gottafish-product-04.png"
          data-url="/product/the-command-station"
          data-qty="1"
        >Add to Cart</button>
      </div>
<div class="text-center mt-12">
            <a href="#" class="inline-block px-12 py-4 bg-emerald-600 text-white font-semibold rounded-full shadow-lg hover:bg-emerald-700 transition-colors">View All Products</a>
        </div>
    </div>
  </div>
</section>



<section id="about-us" class="py-20 px-4 bg-gradient-to-br from-emerald-600 to-emerald-700 text-white mt-8 rounded-[4rem] shadow-2xl">
    <div class="container mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center text-white">
            <div>
                <img src="images/brand-photo.png" alt="Our Story" class="rounded-3xl shadow-xl border border-white/20">
            </div>
            <div>
                <h2 class="text-4xl md:text-5xl font-bold font-bold mb-6">Our Story: More Than a Brand</h2>
                <p class="text-lg  leading-relaxed opacity-90">
                    At <?php echo SITE_NAME; ?>, we're not just selling fishing gear; we're fostering a passion. Our journey began with a simple belief: the right equipment can turn a good day on the water into an unforgettable one. We've dedicated ourselves to creating innovative, high-quality, and reliable products that empower every angler, from the seasoned veteran to the curious newcomer.
                </p>
                <p class="text-lg  leading-relaxed mt-4 opacity-90">
                    We're deeply involved in the fishing community, sponsoring local tournaments and advocating for sustainable fishing practices to ensure our waterways thrive for generations to come. When you choose <?php echo SITE_NAME; ?>, you're not just buying a product; you're joining a movement dedicated to the art and science of fishing.
                </p>
            </div>
        </div>
    </div>
</section>

<section id="business-info" class="py-20 px-4 bg-[#f8f9fa] mt-8 rounded-[4rem] shadow-2xl">
    <div class="container mx-auto text-center">
        <h2 class="text-4xl md:text-5xl font-bold font-bold mb-12 text-black">Information for Your Adventure</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="info-card p-8 rounded-3xl bg-white shadow-lg border border-gray-200">
                <div class="text-[#1e5285] text-5xl mb-4">
                    <span class="inline-block align-middle">
                        <img src="images/symbol01.png" alt="Shipping Icon" class="h-48 w-48"/>
                    </span>
                </div>
                <h3 class="text-2xl font-bold font-bold mb-2 text-black">Shipping Times</h3>
                <p class="text-gray-600 ">Most orders are processed and shipped within 24-48 hours. Standard shipping within the US typically takes 3-5 business days.</p>
            </div>
            <div class="info-card p-8 rounded-3xl bg-white shadow-lg border border-gray-200">
                <div class="text-[#1e5285] text-5xl mb-4">
                    <span class="inline-block align-middle">
                        <img src="images/symbol02.png" alt="Payment Icon" class="h-48 w-48"/>
                    </span>
                </div>
                <h3 class="text-2xl font-bold font-bold mb-2 text-black">Payment Methods</h3>
                <p class="text-gray-600 ">We accept all major credit cards including Visa, Mastercard, American Express, and Discover. We also offer secure payments via PayPal and Apple Pay.</p>
            </div>
            <div class="info-card p-8 rounded-3xl bg-white shadow-lg border border-gray-200">
                <div class="text-[#1e5285] text-5xl mb-4">
                    <span class="inline-block align-middle">
                        <img src="images/symbol03.png" alt="Returns Icon" class="h-48 w-48"/>
                    </span>
                </div>
                <h3 class="text-2xl font-bold font-bold mb-2 text-black">Easy Returns</h3>
                <p class="text-gray-600 ">We stand by our products with a 30-day money-back guarantee. If you're not satisfied, simply return the item for a full refund or exchange.</p>
            </div>
        </div>
    </div>
</section>

<section id="blog-posts" class="py-20 px-4 bg-[#f8f9fa] mt-8 rounded-[4rem] shadow-2xl">
    <div class="container mx-auto">
        <h2 class="text-4xl md:text-5xl font-bold font-bold text-center mb-12 text-black">Latest from the Blog</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="blog-card bg-white rounded-3xl shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl hover:border-b-4 hover:border-[#1e5285]">
                <img src="images/5-best-fishing-lures.png" alt="Best Lures for Bass" class="w-full h-56 object-cover">
                <div class="p-6">
                    <h3 class="text-2xl font-bold font-bold mb-2 text-black">5 Best Lures for Bass Fishing</h3>
                    <p class="text-gray-600  text-sm mb-4">Learn which lures are essential for landing that trophy bass this season. Our experts share their top picks and tips for using them effectively.</p>
                    <a href="#" class="text-black font-semibold hover:underline">Read More &rarr;</a>
                </div>
            </div>
            <div class="blog-card bg-white rounded-3xl shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl hover:border-b-4 hover:border-[#1e5285]">
                <img src="images/beginners-guide-to-fly-fishing.png" alt="Beginner's Guide to Fly Fishing" class="w-full h-56 object-cover">
                <div class="p-6">
                    <h3 class="text-2xl font-bold font-bold mb-2 text-black">A Beginner's Guide to Fly Fishing</h3>
                    <p class="text-gray-600  text-sm mb-4">New to fly fishing? This guide covers the basics from choosing your rod to mastering your first cast. Get ready to hit the river!</p>
                    <a href="#" class="text-black font-semibold hover:underline">Read More &rarr;</a>
                </div>
            </div>
            <div class="blog-card bg-white rounded-3xl shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl hover:border-b-4 hover:border-[#1e5285]">
                <img src="images/ethical-angling.png" alt="Catch and Release Tips" class="w-full h-56 object-cover">
                <div class="p-6">
                    <h3 class="text-2xl font-bold font-bold mb-2 text-black">Ethical Angling: Catch & Release Tips</h3>
                    <p class="text-gray-600  text-sm mb-4">Protect our fish and their habitats with these essential catch and release techniques. A must-read for every responsible angler.</p>
                    <a href="#" class="text-black font-semibold hover:underline">Read More &rarr;</a>
                </div>
            </div>
        </div>
        <div class="text-center mt-12">
            <a href="#" class="inline-block px-12 py-4 bg-black text-white font-semibold rounded-full shadow-lg hover:bg-gray-800 transition-colors">View All Blog Posts</a>
        </div>
    </div>
</section>

<section id="cta" class="py-20 px-4 bg-black mt-8 rounded-[4rem] shadow-2xl text-center text-white">
    <div class="container mx-auto max-w-2xl">
        <h2 class="text-4xl md:text-5xl font-bold font-bold mb-4">Stay Connected!</h2>
        <p class="text-lg  font-light mb-8">Join our newsletter to receive the latest fishing tips, product drops, and exclusive offers right to your inbox.</p>
        <form id="newsletterForm" class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 justify-center">
            <input id="newsletterEmail" type="email" placeholder="Enter your email address" required class="px-6 py-3 w-full sm:w-auto rounded-full text-gray-900 bg-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-white placeholder-gray-500">
            <button id="newsletterSubmit" type="submit" class="px-8 py-3 w-full sm:w-auto bg-emerald-600 text-white font-semibold rounded-full hover:bg-emerald-700 transition-colors">Subscribe</button>
        </form>
        <div id="newsletterMessage" class="mt-4 text-sm" style="display:none;"></div>
    </div>
</section>

<section id="video-showcase" class="py-20 px-4 bg-[#f8f9fa] mt-8 rounded-[4rem] shadow-2xl">
    <div class="container mx-auto text-center">
        <h2 class="text-4xl md:text-5xl font-bold font-bold mb-8 text-black">A Glimpse of the Action</h2>
        <p class="text-lg text-gray-700  mb-12 max-w-3xl mx-auto">
            See the passion for yourself. This is what it's all about—the thrill of the catch, no matter the conditions.
        </p>
        <div class="flex justify-center flex-wrap gap-8">
            <div class="aspect-[9/16] w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all duration-300 hover:scale-105">
                <iframe src="https://www.youtube.com/embed/17J0hoau7B4"
                        title="Fishing in the rain 🌧.....I GOTTA FISH!"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        class="w-full h-full"></iframe>
            </div>

            <div class="aspect-[9/16] w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all duration-300 hover:scale-105">
                <iframe src="https://www.facebook.com/plugins/video.php?height=476&href=https%3A%2F%2Fwww.facebook.com%2F61576613473164%2Fvideos%2F2216781275423924%2F&show_text=false&width=267&t=0"
                        style="border:none;overflow:hidden"
                        scrolling="no"
                        frameborder="0"
                        allowfullscreen="true"
                        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                        class="w-full h-full"></iframe>
            </div>
        </div>
    </div>
</section>

<section id="stickers-products" class="py-20 px-4 bg-[#f8f9fa] mt-8 rounded-[4rem] shadow-2xl">
    <div class="container mx-auto">
        <h2 class="text-4xl md:text-5xl font-bold font-bold text-center mb-12 text-black">Featured Stickers</h2>
        <p class="text-lg text-gray-700  mb-12 max-w-3xl mx-auto text-center">
            Show off your passion for the outdoors with our collection of high-quality, durable <?php echo SITE_NAME; ?> stickers. Perfect for your boat, cooler, or tackle box.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-8">
            <div class="product-card bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col items-center border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-[#1e5285]">
                <img src="images/new-sticker-images-coming-soon.png" alt="<?php echo SITE_NAME; ?> Sticker 1" class="w-full h-32 object-contain p-4">
                <div class="p-4 text-center flex-grow flex flex-col justify-between w-full text-gray-900">
                    <div>
                        <h3 class="text-lg font-bold font-bold mb-2"><?php echo SITE_NAME; ?> Sticker</h3>
                        <p class="text-gray-600 text-xs  mb-2">Durable vinyl sticker with our classic logo. Perfect for any surface.</p>
                    </div>
                    <div class="mt-2">
                        <span class="text-xl font-extrabold block text-gray-900">$2.99</span>
                        <button
                          type="button"
                          class="addToCartBtn mt-2 px-6 py-3 w-full bg-black text-white font-semibold rounded-full hover:bg-gray-800 transition-colors"
                          data-id="sticker-1"
                          data-slug="sticker-1"
                          data-name="<?php echo SITE_NAME; ?> Sticker 1"
                          data-price="2.99"
                          data-image="/images/new-sticker-images-coming-soon.png"
                          data-url="/stickers"
                          data-qty="1"
                        >Add to Cart</button>
                    </div>
                </div>
            </div>
            <div class="product-card bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col items-center border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-[#1e5285]">
                <img src="images/new-sticker-images-coming-soon.png" alt="<?php echo SITE_NAME; ?> Sticker 2" class="w-full h-32 object-contain p-4">
                <div class="p-4 text-center flex-grow flex flex-col justify-between w-full text-gray-900">
                    <div>
                        <h3 class="text-lg font-bold font-bold mb-2"><?php echo SITE_NAME; ?> Sticker</h3>
                        <p class="text-gray-600 text-xs  mb-2">A sleek, minimalist design that fits anywhere.</p>
                    </div>
                    <div class="mt-2">
                        <span class="text-xl font-extrabold block text-gray-900">$2.99</span>
                        <button
                          type="button"
                          class="addToCartBtn mt-2 px-6 py-3 w-full bg-black text-white font-semibold rounded-full hover:bg-gray-800 transition-colors"
                          data-id="sticker-2"
                          data-slug="sticker-2"
                          data-name="<?php echo SITE_NAME; ?> Sticker 2"
                          data-price="2.99"
                          data-image="/images/new-sticker-images-coming-soon.png"
                          data-url="/stickers"
                          data-qty="1"
                        >Add to Cart</button>
                    </div>
                </div>
            </div>
            <div class="product-card bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col items-center border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-[#1e5285]">
                <img src="images/new-sticker-images-coming-soon.png" alt="<?php echo SITE_NAME; ?> Sticker 3" class="w-full h-32 object-contain p-4">
                <div class="p-4 text-center flex-grow flex flex-col justify-between w-full text-gray-900">
                    <div>
                        <h3 class="text-lg font-bold font-bold mb-2"><?php echo SITE_NAME; ?> Sticker</h3>
                        <p class="text-gray-600 text-xs  mb-2">Our classic tagline sticker. A must-have for every angler!</p>
                    </div>
                    <div class="mt-2">
                        <span class="text-xl font-extrabold block text-gray-900">$2.99</span>
                        <button
                          type="button"
                          class="addToCartBtn mt-2 px-6 py-3 w-full bg-black text-white font-semibold rounded-full hover:bg-gray-800 transition-colors"
                          data-id="sticker-3"
                          data-slug="sticker-3"
                          data-name="<?php echo SITE_NAME; ?> Sticker 3"
                          data-price="2.99"
                          data-image="/images/new-sticker-images-coming-soon.png"
                          data-url="/stickers"
                          data-qty="1"
                        >Add to Cart</button>
                    </div>
                </div>
            </div>
            <div class="product-card bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col items-center border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-[#1e5285]">
                <img src="images/new-sticker-images-coming-soon.png" alt="<?php echo SITE_NAME; ?> Sticker 4" class="w-full h-32 object-contain p-4">
                <div class="p-4 text-center flex-grow flex flex-col justify-between w-full text-gray-900">
                    <div>
                        <h3 class="text-lg font-bold font-bold mb-2"><?php echo SITE_NAME; ?> Sticker</h3>
                        <p class="text-gray-600 text-xs  mb-2">A vibrant and eye-catching design to stand out on your gear.</p>
                    </div>
                    <div class="mt-2">
                        <span class="text-xl font-extrabold block text-gray-900">$2.99</span>
                        <button
                          type="button"
                          class="addToCartBtn mt-2 px-6 py-3 w-full bg-black text-white font-semibold rounded-full hover:bg-gray-800 transition-colors"
                          data-id="sticker-4"
                          data-slug="sticker-4"
                          data-name="<?php echo SITE_NAME; ?> Sticker 4"
                          data-price="2.99"
                          data-image="/images/new-sticker-images-coming-soon.png"
                          data-url="/stickers"
                          data-qty="1"
                        >Add to Cart</button>
                    </div>
                </div>
            </div>
            <div class="product-card bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col items-center border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-[#1e5285]">
                <img src="images/new-sticker-images-coming-soon.png" alt="<?php echo SITE_NAME; ?> Sticker 5" class="w-full h-32 object-contain p-4">
                <div class="p-4 text-center flex-grow flex flex-col justify-between w-full text-gray-900">
                    <div>
                        <h3 class="text-lg font-bold font-bold mb-2"><?php echo SITE_NAME; ?> Sticker</h3>
                        <p class="text-gray-600 text-xs  mb-2">A small, clean logo sticker, perfect for subtle branding.</p>
                    </div>
                    <div class="mt-2">
                        <span class="text-xl font-extrabold block text-gray-900">$2.99</span>
                        <button
                          type="button"
                          class="addToCartBtn mt-2 px-6 py-3 w-full bg-black text-white font-semibold rounded-full hover:bg-gray-800 transition-colors"
                          data-id="sticker-5"
                          data-slug="sticker-5"
                          data-name="<?php echo SITE_NAME; ?> Sticker 5"
                          data-price="2.99"
                          data-image="/images/new-sticker-images-coming-soon.png"
                          data-url="/stickers"
                          data-qty="1"
                        >Add to Cart</button>
                    </div>
                </div>
            </div>
            <div class="product-card bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col items-center border border-gray-200 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-[#1e5285]">
                <img src="images/new-sticker-images-coming-soon.png" alt="<?php echo SITE_NAME; ?> Sticker 6" class="w-full h-32 object-contain p-4">
                <div class="p-4 text-center flex-grow flex flex-col justify-between w-full text-gray-900">
                    <div>
                        <h3 class="text-lg font-bold font-bold mb-2"><?php echo SITE_NAME; ?> Sticker</h3>
                        <p class="text-gray-600 text-xs  mb-2">A bold and fun sticker for the angler with a sense of humor!</p>
                    </div>
                    <div class="mt-2">
                        <span class="text-xl font-extrabold block text-gray-900">$2.99</span>
                        <button
                          type="button"
                          class="addToCartBtn mt-2 px-6 py-3 w-full bg-black text-white font-semibold rounded-full hover:bg-gray-800 transition-colors"
                          data-id="sticker-6"
                          data-slug="sticker-6"
                          data-name="<?php echo SITE_NAME; ?> Sticker 6"
                          data-price="2.99"
                          data-image="/images/new-sticker-images-coming-soon.png"
                          data-url="/stickers"
                          data-qty="1"
                        >Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Hero Carousel Logic
        const carousel = document.querySelector('.hero-carousel');
        const slides = document.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.hero-dot');
        let currentIndex = 0;

        function updateCarousel() {
            const slideWidth = slides[0].getBoundingClientRect().width;
            carousel.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
            
            dots.forEach((dot, index) => {
                dot.classList.remove('active');
                dot.style.opacity = '0.5';
            });
            dots[currentIndex].classList.add('active');
            dots[currentIndex].style.opacity = '1';
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentIndex = index;
                updateCarousel();
            });
        });

        function autoSlide() {
            currentIndex = (currentIndex + 1) % slides.length;
            updateCarousel();
        }

        let slideInterval = setInterval(autoSlide, 5000);

        // Optional: Pause on hover
        carousel.addEventListener('mouseenter', () => clearInterval(slideInterval));
        carousel.addEventListener('mouseleave', () => slideInterval = setInterval(autoSlide, 5000));

        // Initial setup
        updateCarousel();

        // Recalculate on resize
        window.addEventListener('resize', updateCarousel);

        // Mobile Menu Logic
        const mobileMenu = document.getElementById('mobile-menu');
        const menuButton = document.getElementById('menu-button');
        const closeMenuButton = document.getElementById('close-menu-button');
        const mobileLinks = mobileMenu.querySelectorAll('a');

        function toggleMobileMenu() {
            mobileMenu.classList.toggle('translate-x-full');
        }

        if (menuButton) menuButton.addEventListener('click', toggleMobileMenu);
        if (closeMenuButton) closeMenuButton.addEventListener('click', toggleMobileMenu);

        mobileLinks.forEach(link => {
            link.addEventListener('click', toggleMobileMenu);
        });
    });

    // Newsletter AJAX submit
    (function(){
      const form = document.getElementById('newsletterForm');
      if (!form) return;
      const emailInput = document.getElementById('newsletterEmail');
      const submitBtn = document.getElementById('newsletterSubmit');
      const msgEl = document.getElementById('newsletterMessage');
      // Build a hidden fallback form for environments blocking fetch
      const fallbackForm = document.createElement('form');
      fallbackForm.method = 'POST';
      fallbackForm.action = '/subscribe.php';
      fallbackForm.style.display = 'none';
      const hiddenEmail = document.createElement('input');
      hiddenEmail.type = 'hidden';
      hiddenEmail.name = 'email';
      fallbackForm.appendChild(hiddenEmail);
      document.body.appendChild(fallbackForm);
      function showMessage(text, ok){
        if (!msgEl) return;
        msgEl.textContent = text;
        msgEl.style.display = 'block';
        msgEl.style.color = ok ? '#1dd171' : '#ef4444';
      }
      form.addEventListener('submit', async function(e){
        e.preventDefault();
        const email = (emailInput?.value || '').trim();
        if (!email) { showMessage('Please enter a valid email.', false); return; }
        submitBtn.disabled = true;
        submitBtn.textContent = 'Subscribing...';
        try {
          const res = await fetch('/subscribe.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
          });
          const data = await res.json().catch(()=>({success:false}));
          if (res.ok && data && data.success) {
            showMessage('Thank you for subscribing!', true);
            if (emailInput) emailInput.value='';
          } else {
            // Fallback to a real form POST if fetch failed on server
            submitFallback(email);
          }
        } catch (err) {
          // Network blocked? Use server POST fallback
          submitFallback(email);
        } finally {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Subscribe';
        }
      });
    })();

    // Include Google Fonts
    const link1 = document.createElement('link');
    link1.rel = 'preconnect';
    link1.href = 'https://fonts.googleapis.com';
    document.head.appendChild(link1);

    const link2 = document.createElement('link');
    link2.rel = 'preconnect';
    link2.href = 'https://fonts.gstatic.com';
    link2.crossOrigin = 'anonymous';
    document.head.appendChild(link2);

    const link3 = document.createElement('link');
    link3.href = 'https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Montserrat:wght@700;800&display=swap';
    link3.rel = 'stylesheet';
    document.head.appendChild(link3);
</script>
</body>
</html>