<?php
// admin/layout.php — Global admin layout wrapper
// Usage:
//   include_once __DIR__ . '/layout.php';
//   admin_layout_start('Page Title', 'dashboard');
//   ... page content ...
//   admin_layout_end();

if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/translation.php';

function admin_layout_nav_items(): array {
  return [
    ['key'=>'dashboard', 'href'=>'dashboard.php', 'icon'=>'fas fa-tachometer-alt', 'label'=>__('dashboard')],
    ['key'=>'products',  'href'=>'products.php',  'icon'=>'fas fa-box',           'label'=>__('products')],
    ['key'=>'guides',    'href'=>'guides.php',    'icon'=>'fas fa-book',          'label'=>__('guides')],
    ['key'=>'reviews',   'href'=>'reviews.php',   'icon'=>'fas fa-comments',      'label'=>__('reviews')],
    ['key'=>'sales',     'href'=>'sales.php',     'icon'=>'fas fa-chart-line',    'label'=>__('sales')],
    ['key'=>'traffic',   'href'=>'traffic.php',   'icon'=>'fas fa-traffic-light', 'label'=>__('traffic')],
    ['key'=>'customers', 'href'=>'customers.php', 'icon'=>'fas fa-users',         'label'=>__('customers')],
    ['key'=>'support',   'href'=>'support.php',   'icon'=>'fas fa-headset',       'label'=>__('support')],
    ['key'=>'knowledge_base', 'href'=>'knowledge_base.php', 'icon'=>'fas fa-question-circle', 'label'=>__('knowledge_base')],
    ['key'=>'live_chat', 'href'=>'live_chat.php', 'icon'=>'fas fa-comments',      'label'=>__('live_chat')],
    ['key'=>'cart_log',  'href'=>'cart_log.php',  'icon'=>'fas fa-list',          'label'=>__('cart_log')],
    ['key'=>'messages',  'href'=>'messages.php',  'icon'=>'fas fa-inbox',         'label'=>__('messages')],
    ['key'=>'settings',  'href'=>'settings.php',  'icon'=>'fas fa-gear',          'label'=>__('settings')],
    ['key'=>'users',     'href'=>'users.php',     'icon'=>'fas fa-users',         'label'=>__('users')],
  ];
}

function admin_layout_start(string $title, string $active = ''): void {
  $site = defined('SITE_NAME') ? SITE_NAME : 'Admin';
  $items = admin_layout_nav_items();
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($title) . ' - ' . htmlspecialchars($site); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="admin_styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="/admin/ui.js" defer></script>
  <script src="notification_system.js"></script>
</head>
<body>
  <div class="sidebar">
    <div class="sidebar-header">
      <img src="../images/white-logo.png" alt="<?php echo htmlspecialchars($site); ?> Logo" class="sidebar-logo mx-auto">
      <div class="text-sm text-gray-400 mt-2"><?php echo __('employee_portal'); ?></div>
    </div>
    <nav class="sidebar-nav">
      <ul>
        <?php foreach ($items as $it):
          $activeClass = ($active === $it['key']) ? 'active' : ''; ?>
          <li><a href="<?php echo $it['href']; ?>" class="<?php echo $activeClass; ?>">
            <i class="<?php echo $it['icon']; ?>"></i> <?php echo htmlspecialchars($it['label']); ?>
          </a></li>
        <?php endforeach; ?>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <?php echo __('logout'); ?></a></li>
      </ul>
    </nav>
  </div>

  <div class="main-content">
    <div class="top-navbar">
      <div class="flex items-center gap-2">
        <img src="../images/white-logo.png" alt="<?php echo htmlspecialchars($site); ?>" style="height:28px;width:auto;border-radius:6px;">
        <span class="text-xl font-semibold"><?php echo htmlspecialchars($title); ?></span>
      </div>
      <div class="user-profile">
        <?php
        // Get current user's profile picture
        $current_username = $_SESSION['admin_username'] ?? '';
        $current_user_picture = '';
        
        if (!empty($current_username)) {
          $users_file = __DIR__ . '/../users.json';
          if (file_exists($users_file)) {
            $users_data = json_decode(file_get_contents($users_file), true);
            if (is_array($users_data)) {
              foreach ($users_data as $user) {
                if (($user['username'] ?? '') === $current_username) {
                  $current_user_picture = $user['profile_picture'] ?? '';
                  break;
                }
              }
            }
          }
        }
        ?>
        <?php if (!empty($current_user_picture)): ?>
          <img src="../uploads/profiles/<?php echo htmlspecialchars($current_user_picture); ?>" alt="User Avatar" class="w-8 h-8 rounded-full object-cover border border-gray-300">
        <?php else: ?>
          <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-xs font-semibold">
            <?php echo strtoupper(substr($current_username ?: 'A', 0, 1)); ?>
          </div>
        <?php endif; ?>
        <span class="username"><?php echo htmlspecialchars($current_username ?: 'Admin'); ?></span>
      </div>
      
      <!-- Language Selector -->
      <div class="language-selector">
        <form id="language-form" method="POST" action="set_language.php" style="display: inline;">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
          <select name="language" id="language-select" onchange="this.form.submit()" class="language-dropdown">
            <?php foreach (getAvailableLanguages() as $code => $name): ?>
              <option value="<?php echo $code; ?>" <?php echo (getCurrentLanguage() === $code) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
    </div>

    <div class="dashboard-grid">
<?php
}

function admin_layout_end(): void {
  ?>
    </div>
  </div>
</body>
</html>
<?php
}

