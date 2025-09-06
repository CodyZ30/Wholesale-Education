<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .brand-font {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-500 to-emerald-700 min-h-screen flex items-center justify-center p-4 text-white">

    <div class="text-center space-y-8 p-8 max-w-2xl w-full bg-white bg-opacity-10 backdrop-blur-sm rounded-3xl shadow-2xl border border-white border-opacity-20 transform transition-all duration-500 ease-out scale-95 hover:scale-100">
        <!-- Logo Section -->
        <div class="flex flex-col items-center justify-center space-y-4">
            <img src="images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="max-w-[150px] h-auto drop-shadow-lg">
        </div>

        <!-- Coming Soon Message -->
        <h2 class="text-3xl md:text-5xl font-bold tracking-wide mt-8">We're Brewing Something Special!</h2>
        <p class="text-lg md:text-xl opacity-80 max-w-prose mx-auto leading-relaxed">
            Our new website is under construction and will be ready to unveil an unparalleled fishing experience soon. Get ready for premium gear, expert guides, and a vibrant community.
        </p>

        <!-- Countdown or Call to Action (Optional, for future expansion) -->
        <!--
        <div class="mt-10">
            <p class="text-2xl font-semibold">Launch in:</p>
            <div id="countdown" class="text-6xl font-bold mt-2"></div>
        </div>
        -->

        <!-- Social Media / Newsletter Signup (Optional, for future expansion) -->
        <div class="mt-10 space-y-4">
            <p class="text-lg font-semibold">Follow us for updates:</p>
            <div class="flex justify-center space-x-6 text-3xl">
                <a href="#" class="text-white hover:text-blue-200 transition-colors"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-white hover:text-blue-200 transition-colors"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-white hover:text-blue-200 transition-colors"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-white hover:text-blue-200 transition-colors"><i class="fab fa-youtube"></i></a>
            </div>
            <p class="text-sm opacity-60 mt-8">&copy; <span id="current-year"></span> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </div>

    <script>
        document.getElementById('current-year').textContent = new Date().getFullYear();
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

</body>
</html>
