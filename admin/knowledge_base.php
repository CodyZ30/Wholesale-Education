<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/translation.php';
include_once __DIR__ . '/layout.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$knowledgeFile = __DIR__ . '/../data/knowledge_base.json';
$articles = [];
if (file_exists($knowledgeFile)) {
    $articles = json_decode(file_get_contents($knowledgeFile), true) ?: [];
}

$error = '';
$success_message = '';

// Handle article actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $title = trim($_POST['title'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            
            if (empty($title) || empty($category) || empty($content)) {
                $error = 'Title, category, and content are required.';
            } else {
                $new_article = [
                    'id' => 'kb' . str_pad(count($articles) + 1, 3, '0', STR_PAD_LEFT),
                    'title' => $title,
                    'category' => $category,
                    'content' => $content,
                    'tags' => array_map('trim', explode(',', $tags)),
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d')
                ];
                
                $articles[] = $new_article;
                file_put_contents($knowledgeFile, json_encode($articles, JSON_PRETTY_PRINT));
                $success_message = 'Article added successfully.';
            }
        } elseif ($action === 'edit') {
            $id = $_POST['id'] ?? '';
            $title = trim($_POST['title'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            
            if (empty($title) || empty($category) || empty($content)) {
                $error = 'Title, category, and content are required.';
            } else {
                foreach ($articles as &$article) {
                    if ($article['id'] === $id) {
                        $article['title'] = $title;
                        $article['category'] = $category;
                        $article['content'] = $content;
                        $article['tags'] = array_map('trim', explode(',', $tags));
                        $article['updated_at'] = date('Y-m-d');
                        break;
                    }
                }
                file_put_contents($knowledgeFile, json_encode($articles, JSON_PRETTY_PRINT));
                $success_message = 'Article updated successfully.';
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? '';
            $articles = array_filter($articles, function($article) use ($id) {
                return $article['id'] !== $id;
            });
            file_put_contents($knowledgeFile, json_encode(array_values($articles), JSON_PRETTY_PRINT));
            $success_message = 'Article deleted successfully.';
        }
    }
}

// Get unique categories
$categories = array_unique(array_column($articles, 'category'));
sort($categories);

// Filter articles
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$filtered_articles = $articles;
if (!empty($category_filter)) {
    $filtered_articles = array_filter($filtered_articles, function($article) use ($category_filter) {
        return $article['category'] === $category_filter;
    });
}
if (!empty($search)) {
    $filtered_articles = array_filter($filtered_articles, function($article) use ($search) {
        return stripos($article['title'], $search) !== false ||
               stripos($article['content'], $search) !== false ||
               stripos($article['category'], $search) !== false;
    });
}

admin_layout_start(__('knowledge_base'), 'knowledge_base');
?>

<div class="dashboard-grid">
    <!-- Knowledge Base Overview -->
    <div class="dashboard-card">
        <h3 class="text-lg font-medium mb-4"><?php echo __('knowledge_base_overview'); ?></h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-500"><?php echo count($articles); ?></div>
                <div class="text-sm text-gray-400"><?php echo __('total_articles'); ?></div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-500"><?php echo count($categories); ?></div>
                <div class="text-sm text-gray-400"><?php echo __('categories'); ?></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="dashboard-card">
        <h3 class="text-lg font-medium mb-4"><?php echo __('filters'); ?></h3>
        <form method="GET" class="space-y-3">
            <div>
                <label class="form-label"><?php echo __('category'); ?>:</label>
                <select name="category" class="form-input">
                    <option value=""><?php echo __('all_categories'); ?></option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $category_filter === $category ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label"><?php echo __('search'); ?>:</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-input" placeholder="<?php echo __('search_articles'); ?>">
            </div>
            <button type="submit" class="btn btn-primary w-full"><?php echo __('apply_filters'); ?></button>
            <a href="knowledge_base.php" class="btn btn-secondary w-full"><?php echo __('clear'); ?></a>
        </form>
    </div>

    <!-- Articles List -->
    <div class="dashboard-card md:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium"><?php echo __('knowledge_base_articles'); ?></h3>
            <button onclick="showAddModal()" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i><?php echo __('add_article'); ?>
            </button>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error mb-4"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success mb-4"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($filtered_articles)): ?>
            <div class="space-y-4">
                <?php foreach ($filtered_articles as $article): ?>
                    <div class="bg-gray-800 p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($article['title']); ?></h4>
                                <p class="text-gray-400 mb-2"><?php echo htmlspecialchars(substr($article['content'], 0, 150)); ?>...</p>
                                <div class="flex gap-4 text-sm text-gray-400">
                                    <span><i class="fas fa-folder mr-1"></i><?php echo htmlspecialchars($article['category']); ?></span>
                                    <span><i class="fas fa-calendar mr-1"></i><?php echo htmlspecialchars($article['updated_at']); ?></span>
                                    <?php if (!empty($article['tags'])): ?>
                                        <span><i class="fas fa-tags mr-1"></i><?php echo htmlspecialchars(implode(', ', $article['tags'])); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <button onclick="editArticle('<?php echo $article['id']; ?>')" class="btn btn-primary text-sm">
                                    <i class="fas fa-edit mr-1"></i><?php echo __('edit'); ?>
                                </button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('<?php echo __('confirm_delete_article'); ?>');">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($article['id']); ?>">
                                    <button type="submit" class="btn btn-danger text-sm">
                                        <i class="fas fa-trash mr-1"></i><?php echo __('delete'); ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-book text-4xl mb-4"></i>
                <p><?php echo __('no_articles_found'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Article Modal -->
<div id="articleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold" id="modalTitle"><?php echo __('add_article'); ?></h3>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="articleForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="articleId">
                    
                    <div class="mb-4">
                        <label class="form-label"><?php echo __('title'); ?> *</label>
                        <input type="text" name="title" id="articleTitle" class="form-input" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label"><?php echo __('category'); ?> *</label>
                        <select name="category" id="articleCategory" class="form-input" required>
                            <option value=""><?php echo __('select_category'); ?></option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                            <?php endforeach; ?>
                            <option value="new_category"><?php echo __('new_category'); ?></option>
                        </select>
                        <input type="text" name="new_category" id="newCategory" class="form-input mt-2 hidden" placeholder="<?php echo __('enter_new_category'); ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label"><?php echo __('tags'); ?></label>
                        <input type="text" name="tags" id="articleTags" class="form-input" placeholder="<?php echo __('comma_separated_tags'); ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label"><?php echo __('content'); ?> *</label>
                        <textarea name="content" id="articleContent" class="form-input" rows="10" required></textarea>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary"><?php echo __('save_article'); ?></button>
                        <button type="button" onclick="closeModal()" class="btn btn-secondary"><?php echo __('cancel'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const articles = <?php echo json_encode($articles); ?>;

function showAddModal() {
    document.getElementById('modalTitle').textContent = '<?php echo __('add_article'); ?>';
    document.getElementById('formAction').value = 'add';
    document.getElementById('articleForm').reset();
    document.getElementById('articleModal').classList.remove('hidden');
}

function editArticle(id) {
    const article = articles.find(a => a.id === id);
    if (!article) return;
    
    document.getElementById('modalTitle').textContent = '<?php echo __('edit_article'); ?>';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('articleId').value = id;
    document.getElementById('articleTitle').value = article.title;
    document.getElementById('articleCategory').value = article.category;
    document.getElementById('articleTags').value = article.tags ? article.tags.join(', ') : '';
    document.getElementById('articleContent').value = article.content;
    document.getElementById('articleModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('articleModal').classList.add('hidden');
}

// Handle new category option
document.getElementById('articleCategory').addEventListener('change', function() {
    const newCategoryInput = document.getElementById('newCategory');
    if (this.value === 'new_category') {
        newCategoryInput.classList.remove('hidden');
        newCategoryInput.required = true;
    } else {
        newCategoryInput.classList.add('hidden');
        newCategoryInput.required = false;
    }
});

// Close modal when clicking outside
document.getElementById('articleModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php admin_layout_end(); ?>
