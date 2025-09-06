<?php
// Footer content
?>
<footer class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Company Info -->
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center mb-4">
                    <img src="/images/white-logo.png" alt="<?php echo defined('SITE_NAME') ? SITE_NAME : 'Gotta.Fish'; ?> logo" class="h-12 w-auto">
                </div>
                <p class="text-gray-300 mb-4">
                    Your trusted source for premium fishing gear and accessories. 
                    Quality products for serious anglers.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="/home" class="text-gray-300 hover:text-white transition-colors">Home</a></li>
                    <li><a href="/shop" class="text-gray-300 hover:text-white transition-colors">Shop</a></li>
                    <li><a href="/stickers" class="text-gray-300 hover:text-white transition-colors">Stickers</a></li>
                    <li><a href="/guides" class="text-gray-300 hover:text-white transition-colors">Guides</a></li>
                    <li><a href="/about" class="text-gray-300 hover:text-white transition-colors">About</a></li>
                </ul>
            </div>
            
            <!-- Support -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Support</h3>
                <ul class="space-y-2">
                    <li><a href="/contact" class="text-gray-300 hover:text-white transition-colors">Contact Us</a></li>
                    <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Shipping Info</a></li>
                    <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Returns</a></li>
                    <li><a href="#" class="text-gray-300 hover:text-white transition-colors">FAQ</a></li>
                    <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Size Guide</a></li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-800 mt-8 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">
                    © <?php echo date('Y'); ?> <?php echo defined('SITE_NAME') ? SITE_NAME : 'Gotta.Fish'; ?>. All rights reserved.
                </p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Privacy Policy</a>
                    <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Terms of Service</a>
                    <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Cookie Policy</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Font Awesome for icons -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
