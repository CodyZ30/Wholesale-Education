<?php include_once __DIR__ . '/includes/config.php'; ?>
<footer class="relative bg-black text-white pt-12 md:pt-16">
  <!-- Glow effect -->
  <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-[800px] h-[400px] bg-gradient-radial from-green-500/15 to-transparent pointer-events-none z-0"></div>

  <!-- Footer Container -->
  <div class="relative z-10 w-full mx-auto px-4 md:px-6">

    <!-- Main Grid: Logo + Links -->
    <div class="flex flex-col md:flex-row md:justify-between border-b border-gray-700 pb-8 md:pb-12 mb-8">
      
      <!-- Logo + Description -->
      <div class="md:w-1/3 text-center md:text-left mb-8 md:mb-0">
        <center><img src="/images/white-logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="mx-auto md:mx-0 h-24 w-auto mb-4">
        <p class="text-gray-300 max-w-sm mx-auto md:mx-0">
          Crafting innovative, high-quality gear for a better fishing experience and a healthier planet. Get out there and fish!
        </p>
        <p class="text-gray-400 mt-2">Started In New Jersey!</p></center>
      </div>

      <!-- Links Columns -->
      <div class="md:w-2/3 grid grid-cols-2 md:grid-cols-3 gap-8">
        <!-- Column 1 -->
        <div>
          <h4 class="text-xl font-semibold text-gray-100 mb-4">Fish Species</h4>
          <ul class="space-y-2">
            <li><a href="#" class="hover:text-green-500 transition-colors">Bass</a></li>
            <li><a href="#" class="hover:text-green-500 transition-colors">Trout</a></li>
            <li><a href="#" class="hover:text-green-500 transition-colors">Salmon</a></li>
            <li><a href="#" class="hover:text-green-500 transition-colors">Marlin</a></li>
          </ul>
        </div>

        <!-- Column 2 -->
        <div>
          <h4 class="text-xl font-semibold text-gray-100 mb-4">Techniques</h4>
          <ul class="space-y-2">
            <li><a href="#" class="hover:text-green-500 transition-colors">Fly Fishing</a></li>
            <li><a href="#" class="hover:text-green-500 transition-colors">Trolling</a></li>
            <li><a href="#" class="hover:text-green-500 transition-colors">Ice Fishing</a></li>
            <li><a href="#" class="hover:text-green-500 transition-colors">Saltwater</a></li>
          </ul>
        </div>

        <!-- Column 3 -->
        <div>
          <h4 class="text-xl font-semibold text-gray-100 mb-4">Company</h4>
          <ul class="space-y-2">
            <li><a href="#" class="hover:text-green-500 transition-colors">Our Story</a></li>
            <li><a href="#" class="hover:text-green-500 transition-colors">Contact Us</a></li>
            <li><a href="#" class="hover:text-green-500 transition-colors">Careers</a></li>
            <li><a href="#" class="hover:text-green-500 transition-colors">Partnerships</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Call-to-Action -->
    <div class="text-center mb-8">
      <p class="text-2xl md:text-3xl font-bold text-gray-100 mb-2">GET HOOKED!</p>
      <p class="text-gray-300 mb-4">Your ultimate guide to angling adventures.</p>
      <button class="bg-green-500 text-black px-6 py-3 rounded font-bold hover:opacity-90 transition">
        Explore Now
      </button>
    </div>

    <!-- Bottom Bar: Copyright + Socials -->
    <div class="flex flex-col md:flex-row md:justify-between items-center text-center md:text-left border-t border-gray-700 pt-6 gap-4">
      <p class="text-gray-400 text-sm">&copy; 2025 <?php echo SITE_NAME; ?>. All rights reserved.</p>
      <div class="flex gap-4 justify-center md:justify-start">
        <a href="https://www.facebook.com" aria-label="Facebook" class="text-gray-400 hover:text-green-500 transition-colors text-xl"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com" aria-label="Instagram" class="text-gray-400 hover:text-green-500 transition-colors text-xl"><i class="fab fa-instagram"></i></a>
        <a href="https://www.youtube.com" aria-label="YouTube" class="text-gray-400 hover:text-green-500 transition-colors text-xl"><i class="fab fa-youtube"></i></a>
      </div>
    </div>

  </div>
</footer>
<?php include_once __DIR__ . '/includes/toast.php'; ?>
<script src="/add-to-cart-popup.js"></script>

