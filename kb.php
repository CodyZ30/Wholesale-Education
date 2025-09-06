<?php
session_start();
include_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/check_blocked_ip.php';

function slugify($s){ $s = strtolower((string)$s); $s = preg_replace('/[^a-z0-9]+/i','-', $s); return trim($s,'-'); }

$kbFile = __DIR__ . '/data/knowledge_base.json';
$articles = [];
if (file_exists($kbFile)) {
  $decoded = json_decode((string)file_get_contents($kbFile), true);
  if (is_array($decoded)) $articles = $decoded;
}

$slug = isset($_GET['slug']) ? (string)$_GET['slug'] : '';
$found = null;
if ($slug !== '') {
  foreach ($articles as $a) {
    $id = (string)($a['id'] ?? '');
    $built = slugify((string)($a['title'] ?? 'article')) . '-' . strtolower($id);
    if ($built === $slug) { $found = $a; break; }
  }
}

// Get related articles (same category, excluding current)
$related = [];
if ($found) {
  $category = $found['category'] ?? '';
  $related = array_filter($articles, function($a) use ($category, $found) {
    return ($a['category'] ?? '') === $category && ($a['id'] ?? '') !== ($found['id'] ?? '');
  });
  $related = array_slice($related, 0, 4);
}

// Get popular articles (most recent, excluding current)
$popular = array_filter($articles, function($a) use ($found) {
  return ($a['id'] ?? '') !== ($found['id'] ?? '');
});
usort($popular, function($a,$b){
  $ua = strtotime((string)($a['updated_at'] ?? '')) ?: 0;
  $ub = strtotime((string)($b['updated_at'] ?? '')) ?: 0;
  return $ub <=> $ua;
});
$popular = array_slice($popular, 0, 6);

$pageTitle = $found ? ($found['title'] ?? 'Article') : 'Knowledge Base';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle) . ' - ' . SITE_NAME; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .prose {
      max-width: none;
      color: #374151;
      line-height: 1.7;
    }
    .prose h2 {
      font-size: 1.5rem;
      font-weight: 600;
      margin-top: 2rem;
      margin-bottom: 1rem;
      color: #111827;
    }
    .prose h3 {
      font-size: 1.25rem;
      font-weight: 600;
      margin-top: 1.5rem;
      margin-bottom: 0.75rem;
      color: #111827;
    }
    .prose p {
      margin-bottom: 1rem;
    }
    .prose ul, .prose ol {
      margin: 1rem 0;
      padding-left: 1.5rem;
    }
    .prose li {
      margin-bottom: 0.5rem;
    }
    .prose strong {
      font-weight: 600;
      color: #111827;
    }
    .prose a {
      color: #2563eb;
      text-decoration: underline;
    }
    .prose a:hover {
      color: #1d4ed8;
    }
    .sticky-nav {
      position: sticky;
      top: 2rem;
    }
    .article-card {
      transition: all 0.2s ease;
      border: 1px solid #e5e7eb;
    }
    .article-card:hover {
      border-color: #d1d5db;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      transform: translateY(-1px);
    }
    .category-badge {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .search-highlight {
      background-color: #fef3c7;
      padding: 0.125rem 0.25rem;
      border-radius: 0.25rem;
    }
    .toc-item {
      transition: all 0.2s ease;
    }
    .toc-item:hover {
      background-color: #f3f4f6;
      border-radius: 0.375rem;
    }
    .toc-item.active {
      background-color: #dbeafe;
      border-left: 3px solid #2563eb;
    }
  </style>
</head>
<body class="bg-gray-50">
  <?php include __DIR__ . '/includes/header.php'; ?>
  
  <main class="min-h-screen">
    <?php if ($found): ?>
      <!-- Hero Section -->
      <div class="bg-gradient-to-br from-blue-50 to-indigo-100 border-b border-gray-200">
        <div class="container mx-auto px-4 py-12">
          <div class="max-w-4xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center space-x-2 text-sm text-gray-600 mb-6">
              <a href="/support" class="hover:text-gray-900 transition">Support</a>
              <i class="fas fa-chevron-right text-xs"></i>
              <a href="/support/knowledge-base/all" class="hover:text-gray-900 transition">Knowledge Base</a>
              <i class="fas fa-chevron-right text-xs"></i>
              <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($found['category'] ?? ''); ?></span>
            </nav>
            
            <!-- Article Header -->
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-4">
                  <span class="category-badge text-white text-xs font-semibold px-3 py-1 rounded-full">
                    <?php echo htmlspecialchars($found['category'] ?? ''); ?>
                  </span>
                  <span class="text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    Updated <?php echo date('M j, Y', strtotime($found['updated_at'] ?? 'now')); ?>
                  </span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 leading-tight">
                  <?php echo htmlspecialchars($found['title'] ?? ''); ?>
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                  <?php echo htmlspecialchars(substr($found['content'] ?? '', 0, 200)); ?>...
                </p>
              </div>
              
              <!-- Quick Actions -->
              <div class="hidden lg:flex flex-col gap-3 ml-8">
                <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                  <i class="fas fa-print text-gray-600"></i>
                  <span class="text-sm font-medium">Print</span>
                </button>
                <button onclick="copyToClipboard()" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                  <i class="fas fa-link text-gray-600"></i>
                  <span class="text-sm font-medium">Copy Link</span>
                </button>
                <button onclick="shareArticle()" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                  <i class="fas fa-share text-gray-600"></i>
                  <span class="text-sm font-medium">Share</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
          <div class="grid lg:grid-cols-4 gap-8">
            <!-- Table of Contents (Desktop) -->
            <div class="hidden lg:block">
              <div class="sticky-nav">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                  <h3 class="font-semibold text-gray-900 mb-4">On this page</h3>
                  <nav class="space-y-2" id="toc">
                    <!-- TOC will be populated by JavaScript -->
                  </nav>
                </div>
              </div>
            </div>

            <!-- Article Content -->
            <div class="lg:col-span-2">
              <article class="bg-white rounded-xl border border-gray-200 p-8 md:p-12">
                <div class="prose prose-lg max-w-none" id="article-content">
                  <?php echo nl2br(htmlspecialchars((string)($found['content'] ?? ''))); ?>
                </div>
                
                <!-- Article Footer -->
                <div class="mt-12 pt-8 border-t border-gray-200">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                      <div class="flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-thumbs-up text-green-500"></i>
                        <span>Was this helpful?</span>
                      </div>
                      <div class="flex gap-2">
                        <button onclick="rateArticle('helpful')" class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition">
                          <i class="fas fa-thumbs-up mr-1"></i>Yes
                        </button>
                        <button onclick="rateArticle('not-helpful')" class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded-full hover:bg-red-200 transition">
                          <i class="fas fa-thumbs-down mr-1"></i>No
                        </button>
                      </div>
                    </div>
                    <div class="text-sm text-gray-500">
                      Last updated: <?php echo date('M j, Y', strtotime($found['updated_at'] ?? 'now')); ?>
                    </div>
                  </div>
                </div>
              </article>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
              <div class="space-y-6">
                <!-- Related Articles -->
                <?php if (!empty($related)): ?>
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                  <h3 class="font-semibold text-gray-900 mb-4">Related Articles</h3>
                  <div class="space-y-3">
                    <?php foreach ($related as $relatedArticle):
                      $relatedId = (string)($relatedArticle['id'] ?? '');
                      $relatedSlug = slugify((string)($relatedArticle['title'] ?? 'article')) . '-' . strtolower($relatedId);
                    ?>
                    <a href="/kb.php?slug=<?php echo urlencode($relatedSlug); ?>" class="block group">
                      <div class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition">
                        <?php echo htmlspecialchars((string)($relatedArticle['title'] ?? '')); ?>
                      </div>
                      <div class="text-xs text-gray-500 mt-1">
                        <?php echo htmlspecialchars((string)($relatedArticle['category'] ?? '')); ?>
                      </div>
                    </a>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <!-- Popular Articles -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                  <h3 class="font-semibold text-gray-900 mb-4">Popular Articles</h3>
                  <div class="space-y-3">
                    <?php foreach ($popular as $popularArticle):
                      $popularId = (string)($popularArticle['id'] ?? '');
                      $popularSlug = slugify((string)($popularArticle['title'] ?? 'article')) . '-' . strtolower($popularId);
                    ?>
                    <a href="/kb.php?slug=<?php echo urlencode($popularSlug); ?>" class="block group">
                      <div class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition">
                        <?php echo htmlspecialchars((string)($popularArticle['title'] ?? '')); ?>
                      </div>
                      <div class="text-xs text-gray-500 mt-1">
                        <?php echo htmlspecialchars((string)($popularArticle['category'] ?? '')); ?>
                      </div>
                    </a>
                    <?php endforeach; ?>
                  </div>
                </div>

                <!-- Contact Support -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-6">
                  <div class="text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                      <i class="fas fa-headset text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Still need help?</h3>
                    <p class="text-sm text-gray-600 mb-4">Our support team is here to assist you.</p>
                    <a href="/support" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                      <i class="fas fa-comment"></i>
                      Contact Support
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- 404 State -->
      <div class="container mx-auto px-4 py-16">
        <div class="max-w-2xl mx-auto text-center">
          <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-search text-gray-400 text-3xl"></i>
          </div>
          <h1 class="text-3xl font-bold text-gray-900 mb-4">Article Not Found</h1>
          <p class="text-gray-600 mb-8">The article you're looking for doesn't exist or may have been moved.</p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/support/knowledge-base/all" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
              Browse All Articles
            </a>
            <a href="/support" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
              Back to Support
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script>
    // Table of Contents Generation
    function generateTOC() {
      const content = document.getElementById('article-content');
      const headings = content.querySelectorAll('h2, h3');
      const toc = document.getElementById('toc');
      
      if (headings.length === 0) return;
      
      headings.forEach((heading, index) => {
        const id = `heading-${index}`;
        heading.id = id;
        
        const tocItem = document.createElement('a');
        tocItem.href = `#${id}`;
        tocItem.className = 'toc-item block px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition';
        tocItem.textContent = heading.textContent;
        
        toc.appendChild(tocItem);
      });
    }

    // Smooth scrolling for TOC links
    document.addEventListener('DOMContentLoaded', function() {
      generateTOC();
      
      // Highlight active TOC item
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const id = entry.target.id;
            const tocItem = document.querySelector(`a[href="#${id}"]`);
            if (tocItem) {
              document.querySelectorAll('.toc-item').forEach(item => item.classList.remove('active'));
              tocItem.classList.add('active');
            }
          }
        });
      }, { rootMargin: '-20% 0px -70% 0px' });
      
      document.querySelectorAll('h2, h3').forEach(heading => {
        observer.observe(heading);
      });
    });

    // Utility Functions
    function copyToClipboard() {
      navigator.clipboard.writeText(window.location.href).then(() => {
        showToast('Link copied to clipboard!', 'success');
      });
    }

    function shareArticle() {
      if (navigator.share) {
        navigator.share({
          title: document.title,
          url: window.location.href
        });
      } else {
        copyToClipboard();
      }
    }

    function rateArticle(rating) {
      // Here you would typically send the rating to your backend
      showToast(`Thank you for your feedback!`, 'success');
      
      // Disable buttons after rating
      document.querySelectorAll('button[onclick*="rateArticle"]').forEach(btn => {
        btn.disabled = true;
        btn.classList.add('opacity-50');
      });
    }

    function showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 transition-all duration-300 ${
        type === 'success' ? 'bg-green-500' : 'bg-blue-500'
      }`;
      toast.textContent = message;
      
      document.body.appendChild(toast);
      
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    // Search functionality
    function searchKB() {
      const query = document.getElementById('kb-search').value;
      if (query.length > 2) {
        window.location.href = `/support/knowledge-base/all?search=${encodeURIComponent(query)}`;
      }
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
      if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
          case 'k':
            e.preventDefault();
            document.getElementById('kb-search')?.focus();
            break;
          case 'f':
            e.preventDefault();
            document.getElementById('kb-search')?.focus();
            break;
        }
      }
    });
  </script>
</body>
</html>