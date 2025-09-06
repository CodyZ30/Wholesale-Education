<?php
session_start();
include_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: login.php');
  exit;
}

$file = __DIR__ . '/../contact_messages.json';
$messages = [];
if (file_exists($file)) {
  $decoded = json_decode((string)file_get_contents($file), true);
  if (is_array($decoded)) $messages = $decoded;
}

// Sort newest first
usort($messages, function($a,$b){ return strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''); });
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages - <?php echo SITE_NAME; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="admin_styles.css">
  <script src="/admin/ui.js" defer></script>
</head>
<body>
  <div class="sidebar">
    <div class="sidebar-header">
      <img src="../images/white-logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="sidebar-logo mx-auto">
      <div class="text-sm text-gray-400 mt-2">Employee Portal</div>
    </div>
    <nav class="sidebar-nav">
      <ul>
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
        <li><a href="guides.php"><i class="fas fa-book"></i> Guides</a></li>
        <li><a href="reviews.php"><i class="fas fa-comments"></i> Reviews</a></li>
        <li><a href="sales.php"><i class="fas fa-chart-line"></i> Sales</a></li>
        <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
        <li><a href="cart_log.php"><i class="fas fa-list"></i> Cart Log</a></li>
        <li><a href="settings.php"><i class="fas fa-gear"></i> Settings</a></li>
        <li><a class="active" href="messages.php"><i class="fas fa-inbox"></i> Messages</a></li>
        <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </nav>
  </div>

  <div class="main-content">
    <div class="top-navbar">
      <div class="flex items-center gap-2">
        <img src="../images/white-logo.png" alt="<?php echo SITE_NAME; ?>" style="height:28px;width:auto;border-radius:6px;">
        <span class="text-xl font-semibold">Messages</span>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="dashboard-card md:col-span-3">
        <h3 class="text-lg font-medium mb-3">Contact Submissions</h3>
        <?php if (empty($messages)): ?>
          <div class="text-gray-400">No messages yet.</div>
        <?php else: ?>
          <table class="w-full text-sm">
            <thead>
              <tr>
                <th class="text-left py-2">When</th>
                <th class="text-left py-2">Name</th>
                <th class="text-left py-2">Email</th>
                <th class="text-left py-2">Phone</th>
                <th class="text-left py-2">Reason</th>
                <th class="text-left py-2">Subject</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($messages as $m): ?>
                <tr>
                  <td class="py-2 text-gray-400"><?php echo htmlspecialchars((string)($m['created_at'] ?? '')); ?></td>
                  <td class="py-2"><?php echo htmlspecialchars((string)($m['name'] ?? '')); ?></td>
                  <td class="py-2"><a href="mailto:<?php echo htmlspecialchars((string)($m['email'] ?? '')); ?>" class="underline"><?php echo htmlspecialchars((string)($m['email'] ?? '')); ?></a></td>
                  <td class="py-2"><a href="tel:<?php echo htmlspecialchars((string)($m['phone'] ?? '')); ?>" class="underline"><?php echo htmlspecialchars((string)($m['phone'] ?? '')); ?></a></td>
                  <td class="py-2"><?php echo htmlspecialchars((string)($m['reason'] ?? '')); ?></td>
                  <td class="py-2"><?php echo htmlspecialchars((string)($m['subject'] ?? '')); ?></td>
                </tr>
                <tr>
                  <td colspan="5" class="py-2 text-gray-300 border-b" style="white-space:pre-wrap;">"<?php echo htmlspecialchars((string)($m['message'] ?? '')); ?>"</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>


