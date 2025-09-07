<?php
session_start();
include_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/check_blocked_ip.php';

$supportFile = __DIR__ . '/data/support_tickets.json';
$tickets = [];
if (file_exists($supportFile)) {
    $tickets = json_decode(file_get_contents($supportFile), true) ?: [];
}

$error = '';
$success_message = '';

// Handle ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $category = $_POST['category'] ?? 'general';
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Generate unique ticket ID
        $ticket_id = 'TKT-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 8);
        
        $ticket = [
            'id' => $ticket_id,
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'priority' => $priority,
            'category' => $category,
            'status' => 'open',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        $tickets[$ticket_id] = $ticket;
        file_put_contents($supportFile, json_encode($tickets, JSON_PRETTY_PRINT));
        
        $success_message = "Thank you! Your support ticket has been created. Ticket ID: $ticket_id";
        
        // Clear form data
        $name = $email = $subject = $message = '';
    }
}

// Load knowledge base articles
$knowledgeFile = __DIR__ . '/data/knowledge_base.json';
$knowledge_articles = [];
if (file_exists($knowledgeFile)) {
    $knowledge_articles = json_decode(file_get_contents($knowledgeFile), true) ?: [];
}

// Search functionality
$search_query = $_GET['search'] ?? '';
$search_results = [];
if (!empty($search_query)) {
    $search_results = array_filter($knowledge_articles, function($article) use ($search_query) {
        return stripos($article['title'], $search_query) !== false ||
               stripos($article['content'], $search_query) !== false ||
               stripos($article['category'], $search_query) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Support - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .support-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .support-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="mt-8">
        <!-- Hero Section -->
        <div class="bg-black text-white py-16">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Customer Support</h1>
                <p class="text-xl md:text-2xl mb-8">We're here to help you with any questions or issues</p>
                <div class="flex justify-center">
                    <a href="#knowledge-base" class="bg-white text-gray-900 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                        <i class="fas fa-book mr-2"></i>Browse Knowledge Base
                    </a>
                </div>
            </div>
        </div>

        <!-- How We Support You Section -->
        <section class="container mx-auto px-4 mb-16 py-16">
            <div class="bg-white p-10 rounded-2xl shadow-xl border border-gray-200">
                <h2 class="text-3xl font-bold mb-6 text-gray-900 text-center">How We Support You</h2>
                <div class="grid md:grid-cols-2 gap-8 text-gray-700">
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Response Times</h3>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Email: within 24 hours (usually much faster)</li>
                            <li>Live Chat: under 5 minutes during business hours</li>
                            <li>Urgent issues: prioritized same-day</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Support Channels</h3>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Email support for detailed questions</li>
                            <li>Live chat for immediate assistance</li>
                            <li>Knowledge base for self-service</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Help Section -->
        <div class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Quick Help</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="support-card bg-gray-50 p-8 rounded-lg text-center">
                        <div class="text-gray-800 text-4xl mb-4">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-4">Shipping & Delivery</h3>
                        <p class="text-gray-600 mb-4">Track your order, check delivery times, and learn about our shipping policies.</p>
                        <a href="#shipping" class="text-gray-800 font-semibold hover:underline">Learn More →</a>
                    </div>
                    
                    <div class="support-card bg-gray-50 p-8 rounded-lg text-center">
                        <div class="text-gray-800 text-4xl mb-4">
                            <i class="fas fa-undo"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-4">Returns & Exchanges</h3>
                        <p class="text-gray-600 mb-4">Need to return or exchange an item? We make it easy with our hassle-free process.</p>
                        <a href="#returns" class="text-gray-800 font-semibold hover:underline">Learn More →</a>
                    </div>
                    
                    <div class="support-card bg-gray-50 p-8 rounded-lg text-center">
                        <div class="text-gray-800 text-4xl mb-4">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-4">Payment & Billing</h3>
                        <p class="text-gray-600 mb-4">Questions about payments, refunds, or billing? We've got you covered.</p>
                        <a href="#billing" class="text-gray-800 font-semibold hover:underline">Learn More →</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Knowledge Base Section -->
        <section id="knowledge-base" class="bg-white py-16">
            <div class="container mx-auto px-4">
                <h2 class="text-4xl md:text-5xl font-bold text-center mb-12 text-gray-900">Knowledge Base</h2>
            
                <!-- Search -->
                <div class="max-w-3xl mx-auto mb-8">
                    <div class="relative">
                        <input id="kb-search" type="text" value="" placeholder="Search our knowledge base..." 
                               class="w-full px-6 py-4 pr-12 border-2 border-gray-200 rounded-full focus:ring-2 focus:ring-gray-900 focus:border-transparent text-lg">
                        <button class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-search text-xl"></i>
                        </button>
                    </div>
                    <?php
                      $categoryCounts = [];
                      foreach ($knowledge_articles as $a) {
                        $c = (string)($a['category'] ?? 'Uncategorized');
                        $categoryCounts[$c] = ($categoryCounts[$c] ?? 0) + 1;
                      }
                      ksort($categoryCounts);
                      $totalKb = count($knowledge_articles);
                    ?>
                    <div class="mt-4">
                      <div id="kb-chips" class="flex flex-wrap gap-2 justify-center">
                        <button data-cat="" class="kb-chip active px-3 py-1 rounded-full border border-gray-300 text-sm">All (<?php echo (int)$totalKb; ?>)</button>
                        <?php foreach ($categoryCounts as $catName => $catCount): ?>
                          <button data-cat="<?php echo htmlspecialchars($catName); ?>" class="kb-chip px-3 py-1 rounded-full border border-gray-300 text-sm hover:bg-gray-100"><?php echo htmlspecialchars($catName); ?> (<?php echo (int)$catCount; ?>)</button>
                        <?php endforeach; ?>
                      </div>
                    </div>
                </div>

                <!-- Featured Articles -->
                <?php
                  $sortedKb = $knowledge_articles;
                  usort($sortedKb, function($a,$b){
                    $ua = strtotime((string)($a['updated_at'] ?? '')) ?: 0;
                    $ub = strtotime((string)($b['updated_at'] ?? '')) ?: 0;
                    return $ub <=> $ua;
                  });
                  $featuredKb = array_slice($sortedKb, 0, 6);
                ?>
                <?php if (!empty($featuredKb)): ?>
                <div class="mb-10">
                  <h3 class="text-2xl font-bold mb-4 text-gray-900">Featured Articles</h3>
                  <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($featuredKb as $article): 
                      $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-', (string)($article['title'] ?? ''))) . '-' . strtolower((string)($article['id'] ?? ''));
                      $excerpt = trim(mb_substr((string)($article['content'] ?? ''), 0, 160));
                      if (mb_strlen((string)($article['content'] ?? '')) > 160) $excerpt .= '...';
                    ?>
                      <a href="/kb.php?slug=<?php echo urlencode($slug); ?>" class="block bg-white border border-gray-200 rounded-md p-5 hover:shadow-md transition">
                        <div class="text-sm text-gray-500 mb-1"><?php echo htmlspecialchars((string)($article['category'] ?? '')); ?></div>
                        <div class="font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars((string)($article['title'] ?? '')); ?></div>
                        <div class="text-sm text-gray-600"><?php echo htmlspecialchars($excerpt); ?></div>
                        <div class="text-xs text-gray-400 mt-3"><?php echo htmlspecialchars((string)($article['updated_at'] ?? '')); ?></div>
                      </a>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <!-- Categories -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    $categories = array_unique(array_column($knowledge_articles, 'category'));
                    foreach ($categories as $category):
                        $category_articles = array_filter($knowledge_articles, fn($article) => $article['category'] === $category);
                    ?>
                        <div class="bg-white p-6 border border-gray-200 rounded-md">
                            <h3 class="text-xl font-semibold mb-4 text-gray-900"><?php echo htmlspecialchars($category); ?></h3>
                            <ul class="space-y-2">
                                <?php foreach (array_slice($category_articles, 0, 5) as $article): ?>
                                    <li>
                                        <?php 
                                          $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-', (string)($article['title'] ?? ''))) . '-' . strtolower((string)($article['id'] ?? '')); 
                                        ?>
                                        <a href="/kb.php?slug=<?php echo urlencode($slug); ?>" class="text-gray-800 hover:text-black hover:underline transition"><?php echo htmlspecialchars($article['title']); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if (count($category_articles) > 5): ?>
                                <p class="text-sm text-gray-500 mt-2">+<?php echo count($category_articles) - 5; ?> more articles</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- All Articles -->
                <div class="mt-12">
                  <?php
                    $allSorted = $knowledge_articles;
                    usort($allSorted, function($a,$b){
                      $ua = strtotime((string)($a['updated_at'] ?? '')) ?: 0;
                      $ub = strtotime((string)($b['updated_at'] ?? '')) ?: 0;
                      return $ub <=> $ua;
                    });
                    $initialAll = array_slice($allSorted, 0, 30);
                    $totalAll = count($allSorted);
                  ?>
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="text-2xl font-bold text-gray-900">All Articles</h3>
                    <div class="text-sm text-gray-500"><span id="kb-all-count"><?php echo (int)$totalAll; ?></span> total</div>
                  </div>
                  <ul id="kb-all-list" class="divide-y border border-gray-200 rounded-md" data-offset="<?php echo (int)count($initialAll); ?>">
                    <?php foreach ($initialAll as $article):
                      $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-', (string)($article['title'] ?? ''))) . '-' . strtolower((string)($article['id'] ?? ''));
                      $excerpt = trim(mb_substr((string)($article['content'] ?? ''), 0, 160));
                      if (mb_strlen((string)($article['content'] ?? '')) > 160) $excerpt .= '...';
                    ?>
                      <li class="p-3 hover:bg-gray-50">
                        <a href="/kb.php?slug=<?php echo urlencode($slug); ?>" class="block">
                          <div class="font-medium text-gray-900"><?php echo htmlspecialchars((string)($article['title'] ?? '')); ?></div>
                          <div class="text-sm text-gray-600"><?php echo htmlspecialchars($excerpt); ?></div>
                          <div class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars((string)($article['category'] ?? '')); ?> • <?php echo htmlspecialchars((string)($article['updated_at'] ?? '')); ?></div>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                  <div class="text-center mt-4">
                    <button id="kb-load-more" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50<?php echo (count($initialAll) >= $totalAll) ? ' hidden' : ''; ?>">Load more</button>
                  </div>
                </div>
            </div>
        </section>

        <!-- Contact Information -->
        <section class="container mx-auto px-4 mb-16">
            <div class="bg-gradient-to-r from-gray-900 to-black text-white rounded-[4rem] p-12 shadow-2xl">
                <h2 class="text-4xl md:text-5xl font-bold font-extrabold text-center mb-12">Still Need Help?</h2>
                <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                    <div class="text-center">
                        <div class="text-4xl mb-4 text-green-400">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Phone Support</h3>
                        <p class="text-gray-300">1-800-SUPPORT</p>
                        <p class="text-sm text-gray-400">Mon-Fri 9AM-6PM EST</p>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl mb-4 text-blue-400">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Email Support</h3>
                        <p class="text-gray-300">support@gotta.fish</p>
                        <p class="text-sm text-gray-400">24/7 Response</p>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl mb-4 text-yellow-400">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Live Chat</h3>
                        <p class="text-gray-300">Available Now</p>
                        <p class="text-sm text-gray-400">Mon-Fri 9AM-6PM EST</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
        // Live KB Search
        const input = document.getElementById('kb-search');
        const chipsWrap = document.getElementById('kb-chips');
        const allList = document.getElementById('kb-all-list');
        const allCount = document.getElementById('kb-all-count');
        const loadMoreBtn = document.getElementById('kb-load-more');
        let currentCat = '';
        let allOffset = 0;
        let lastQuery = '';
        let searchTimer = null;
        
        function slugify(s){ return (s||'').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,''); }
        function escapeHtml(t){ const d=document.createElement('div'); d.textContent=t||''; return d.innerHTML; }
        
        // Search functionality
        async function doSearch(q){
            const url = `/kb_search.php?q=${encodeURIComponent(q)}&category=${encodeURIComponent(currentCat)}&limit=20`;
            try {
                const res = await fetch(url, { credentials:'include' });
                if (!res.ok) return;
                const data = await res.json();
                const items = data.results || [];
                
                // Update the all articles list with search results
                allList.innerHTML = '';
                for (const it of items) {
                    const li = document.createElement('li');
                    li.className = 'p-3 hover:bg-gray-50';
                    li.innerHTML = `<a href="/kb.php?slug=${encodeURIComponent(it.slug)}" class="block">
                                      <div class="font-medium text-gray-900">${escapeHtml(it.title)}</div>
                                      <div class="text-sm text-gray-600">${escapeHtml(it.excerpt)}</div>
                                      <div class="text-xs text-gray-400 mt-1">${escapeHtml(it.category)} • ${escapeHtml(it.updated_at)}</div>
                                    </a>`;
                    allList.appendChild(li);
                }
                allCount.textContent = data.total || items.length;
                loadMoreBtn.classList.add('hidden');
            } catch (error) {
                console.error('Search error:', error);
            }
        }
        
        input && input.addEventListener('input', () => {
            const q = input.value.trim();
            lastQuery = q;
            if (searchTimer) clearTimeout(searchTimer);
            searchTimer = setTimeout(() => { 
                if (q.length === 0){ 
                    // Reset to show all articles
                    allOffset = 0;
                    loadMore();
                } else { 
                    doSearch(q); 
                } 
            }, 200);
        });

        // Category chips filter
        chipsWrap && chipsWrap.addEventListener('click', (e) => {
            const btn = e.target.closest('.kb-chip');
            if (!btn) return;
            [...chipsWrap.querySelectorAll('.kb-chip')].forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentCat = btn.dataset.cat || '';
            // Reset All list and reload
            allList.innerHTML = '';
            allOffset = 0;
            lastQuery = '';
            loadMore();
        });

        // All Articles pagination
        async function loadMore(){
            const url = `/kb_search.php?q=${encodeURIComponent(lastQuery)}&category=${encodeURIComponent(currentCat)}&limit=30&offset=${allOffset}`;
            try {
                const res = await fetch(url, { credentials:'include' });
                if (!res.ok) {
                    console.error('Failed to load articles:', res.status);
                    return;
                }
                const data = await res.json();
                allCount.textContent = data.total || 0;
                const items = data.results || [];
                for (const it of items) {
                    const li = document.createElement('li');
                    li.className = 'p-3 hover:bg-gray-50';
                    li.innerHTML = `<a href="/kb.php?slug=${encodeURIComponent(it.slug)}" class="block">
                                      <div class="font-medium text-gray-900">${escapeHtml(it.title)}</div>
                                      <div class="text-sm text-gray-600">${escapeHtml(it.excerpt)}</div>
                                      <div class="text-xs text-gray-400 mt-1">${escapeHtml(it.category)} • ${escapeHtml(it.updated_at)}</div>
                                    </a>`;
                    allList.appendChild(li);
                }
                allOffset += items.length;
                loadMoreBtn && loadMoreBtn.classList.toggle('hidden', items.length === 0 || allOffset >= (data.total || 0));
            } catch (error) {
                console.error('Error loading articles:', error);
            }
        }
        loadMoreBtn && loadMoreBtn.addEventListener('click', loadMore);
        // Initial load of All Articles
        if (allList) loadMore();

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
