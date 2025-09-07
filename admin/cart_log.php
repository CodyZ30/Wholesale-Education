<?php
session_start();
include_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: login.php');
  exit;
}

$file = __DIR__ . '/../data/cart_logs.json';
$logs = [];
if (file_exists($file)) {
  $decoded = json_decode((string)file_get_contents($file), true);
  if (is_array($decoded)) $logs = array_reverse($decoded); // newest first
}

// Build unique day list (YYYY-MM-DD) and select the most recent by default
$days = [];
foreach ($logs as $row) {
  $ts = (string)($row['ts'] ?? '');
  $day = $ts !== '' ? substr($ts, 0, 10) : date('Y-m-d');
  $days[$day] = true;
}
$dayList = array_keys($days);
rsort($dayList); // most recent first
$selectedDay = isset($_GET['day']) ? (string)$_GET['day'] : '';
if ($selectedDay === '' || !in_array($selectedDay, $dayList, true)) {
  $selectedDay = $dayList[0] ?? date('Y-m-d');
}
// Filter logs to selected day
$viewLogs = array_values(array_filter($logs, function($r) use ($selectedDay){
  $ts = (string)($r['ts'] ?? '');
  $day = $ts !== '' ? substr($ts, 0, 10) : date('Y-m-d');
  return $day === $selectedDay;
}));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cart Log - <?php echo SITE_NAME; ?></title>
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
        <li><a class="active" href="cart_log.php"><i class="fas fa-list"></i> Cart Log</a></li>
        <li><a href="messages.php"><i class="fas fa-inbox"></i> Messages</a></li>
        <li><a href="settings.php"><i class="fas fa-gear"></i> Settings</a></li>
        <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </nav>
  </div>

  <div class="main-content">
    <div class="top-navbar">
      <div class="flex items-center gap-2">
        <img src="../images/white-logo.png" alt="<?php echo SITE_NAME; ?>" style="height:28px;width:auto;border-radius:6px;">
        <span class="text-xl font-semibold">Cart Log</span>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="dashboard-card md:col-span-3">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-medium">Recent Cart Syncs</h3>
          <div class="flex items-center gap-2">
            <label class="text-sm text-gray-400">Day</label>
            <select class="border rounded px-2 py-1 bg-transparent" onchange="location.href='cart_log.php?day='+this.value">
              <?php foreach ($dayList as $d): ?>
                <option value="<?php echo htmlspecialchars((string)$d); ?>" <?php echo $d===$selectedDay?'selected':''; ?>><?php echo htmlspecialchars((string)$d); ?></option>
              <?php endforeach; ?>
            </select>
            <div class="text-sm text-gray-500">Showing: <?php echo count($viewLogs); ?></div>
          </div>
        </div>
        <div class="rounded-lg border overflow-auto">
          <table class="w-full text-sm">
            <thead>
              <tr>
                <th class="text-left p-2">When</th>
                <th class="text-left p-2">Session</th>
                <th class="text-left p-2">Name</th>
                <th class="text-left p-2">IP</th>
                <th class="text-left p-2">UA</th>
                <th class="text-left p-2">Items</th>
                <th class="text-left p-2">Subtotal</th>
                <th class="text-left p-2">Discount</th>
                <th class="text-left p-2">Shipping</th>
                <th class="text-left p-2">Total</th>
                <th class="text-left p-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($viewLogs)): ?>
                <tr><td colspan="11" class="p-3 text-gray-400">No cart activity for this day.</td></tr>
              <?php else: foreach ($viewLogs as $i => $row): $sum = $row['summary'] ?? []; ?>
                <tr class="border-t align-top">
                  <td class="p-2 whitespace-nowrap text-gray-500"><?php echo htmlspecialchars((string)($row['ts'] ?? '')); ?></td>
                  <td class="p-2"><?php echo htmlspecialchars((string)($row['session'] ?? '')); ?></td>
                  <td class="p-2">
                    <form method="POST" action="cart_log.php" class="flex items-center gap-2">
                      <input type="hidden" name="idx" value="<?php echo htmlspecialchars((string)$i); ?>">
                      <input type="text" name="name" value="<?php echo htmlspecialchars((string)($row['name'] ?? '')); ?>" class="border rounded px-2 py-1 bg-transparent" placeholder="Name">
                    </form>
                  </td>
                  <td class="p-2"><?php echo htmlspecialchars((string)($row['ip'] ?? '')); ?></td>
                  <td class="p-2 max-w-[320px] truncate" title="<?php echo htmlspecialchars((string)($row['ua'] ?? '')); ?>"><?php echo htmlspecialchars((string)($row['ua'] ?? '')); ?></td>
                  <td class="p-2">
                    <?php $items = $row['items'] ?? []; if (empty($items)) { echo '<span class="text-gray-400">—</span>'; } else { ?>
                      <ul class="list-disc ml-5">
                        <?php foreach ($items as $it): ?>
                          <li><?php echo htmlspecialchars((string)($it['qty'] ?? 0)); ?> × <?php echo htmlspecialchars((string)($it['name'] ?? 'Item')); ?> ($<?php echo htmlspecialchars(number_format((float)($it['price'] ?? 0),2)); ?>)</li>
                        <?php endforeach; ?>
                      </ul>
                    <?php } ?>
                  </td>
                  <td class="p-2">$<?php echo htmlspecialchars((string)($sum['subtotal'] ?? '0.00')); ?></td>
                  <td class="p-2">$<?php echo htmlspecialchars((string)($sum['discount'] ?? '0.00')); ?></td>
                  <td class="p-2">$<?php echo htmlspecialchars((string)($sum['shipping'] ?? '0.00')); ?></td>
                  <td class="p-2 font-semibold">$<?php echo htmlspecialchars((string)($sum['total'] ?? '0.00')); ?></td>
                  <td class="p-2 whitespace-nowrap">
                    <?php $itemsB64 = base64_encode(json_encode($row['items'] ?? [])); ?>
                    <button class="btn" onclick="copyRestore('<?php echo $itemsB64; ?>')">Copy Restore URL</button>
                    <a class="btn btn-primary ml-2" href="sales.php?action=add&items=<?php echo urlencode($itemsB64); ?>">Use in New Sale</a>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <script>
    function copyRestore(b64){
      const url = '/restore_cart.php?items='+encodeURIComponent(b64);
      navigator.clipboard.writeText(url).then(()=>{ alert('Restore URL copied'); });
    }
  </script>
</body>
</html>


