<?php include_once __DIR__ . '/includes/config.php'; ?>
<?php include 'includes/header.php'; ?>

<main class="container mx-auto px-4 md:px-6 py-10">
  <section class="mb-12">
    <div class="grid md:grid-cols-2 gap-8 items-center">
      <div>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4">About <?php echo SITE_NAME; ?></h1>
        <p class="text-gray-700 text-lg leading-relaxed">We build durable, thoughtfully designed fishing gear for anglers who care about time on the water. From boat organization to the smallest tools, every product aims to reduce friction and elevate your day.</p>
        <div class="mt-6 flex gap-3">
          <a href="/shop" class="px-5 py-3 bg-black text-white rounded-full font-semibold">Shop Gear</a>
          <a href="/guides" class="px-5 py-3 border rounded-full font-semibold">Guides & Tips</a>
        </div>
      </div>
      <div class="flex justify-center">
        <img src="/images/brand-photo.png" alt="Brand" class="rounded-2xl border shadow max-w-md w-full">
      </div>
    </div>
  </section>

  <section class="grid md:grid-cols-3 gap-6 mb-12">
    <article class="bg-white rounded-2xl border shadow p-6">
      <h2 class="text-xl font-bold mb-2">Our Mission</h2>
      <p class="text-gray-700">Make better days on the water more common by creating simple, reliable gear that solves real problems and lasts season after season.</p>
    </article>
    <article class="bg-white rounded-2xl border shadow p-6">
      <h2 class="text-xl font-bold mb-2">Design Principles</h2>
      <ul class="list-disc list-inside text-gray-700">
        <li>Function before flash</li>
        <li>Materials that survive salt and sun</li>
        <li>Fast setup, faster teardown</li>
      </ul>
    </article>
    <article class="bg-white rounded-2xl border shadow p-6">
      <h2 class="text-xl font-bold mb-2">Sustainability</h2>
      <p class="text-gray-700">We prioritize durable construction and responsible packaging, and we support conservation efforts that protect our waterways.</p>
    </article>
  </section>

  <section class="bg-white rounded-2xl border shadow p-6 mb-12">
    <h2 class="text-2xl font-bold mb-3">The Story</h2>
    <p class="text-gray-700 leading-relaxed">What started as a few garage-built solutions for our own boats grew into a set of products fellow anglers kept asking for. Today, <?php echo SITE_NAME; ?> remains focused on the same thing: gear that earns its place trip after trip.</p>
  </section>

  <section class="grid md:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl border shadow p-6">
      <h3 class="text-xl font-bold mb-2">Contact</h3>
      <p class="text-gray-700">Questions, feedback, or partnerships? We’d love to hear from you.</p>
      <ul class="mt-2 text-gray-700">
        <li>Email: support@<?php echo str_replace(' ', '', strtolower(SITE_NAME)); ?>.com</li>
      </ul>
    </div>
    <div class="bg-white rounded-2xl border shadow p-6">
      <h3 class="text-xl font-bold mb-2">Join the Community</h3>
      <p class="text-gray-700">Follow along for product updates, on‑water tips, and behind‑the‑scenes.</p>
      <div class="mt-3 flex gap-3">
        <a href="https://www.instagram.com" class="px-4 py-2 border rounded-full">Instagram</a>
        <a href="https://www.youtube.com" class="px-4 py-2 border rounded-full">YouTube</a>
      </div>
    </div>
  </section>
</main>

<?php include 'footer.php'; ?>


