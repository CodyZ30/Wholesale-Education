<?php
session_start();
include_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: login.php');
  exit;
}

function read_json_file(string $path): array {
  if (!file_exists($path)) return [];
  $raw = file_get_contents($path);
  $data = json_decode((string)$raw, true);
  return is_array($data) ? $data : [];
}

$customersPath = __DIR__ . '/../data/customers.json';
$customers = read_json_file($customersPath);

// Export CSV
if (isset($_GET['export'])) {
  $type = $_GET['export'];
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="customers_' . $type . '.csv"');
  $out = fopen('php://output', 'w');
  if ($type === 'emails') {
    fputcsv($out, ['email']);
    foreach ($customers as $c) { fputcsv($out, [$c['email'] ?? '']); }
  } else {
    fputcsv($out, ['id','email','name','joined_at','purchases_json']);
    foreach ($customers as $c) { fputcsv($out, [ $c['id']??'', $c['email']??'', $c['name']??'', $c['joined_at']??'', json_encode($c['purchases']??[]) ]); }
  }
  fclose($out);
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customers - <?php echo SITE_NAME; ?></title>
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
        <li><a class="active" href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
        <li><a href="cart_log.php"><i class="fas fa-list"></i> Cart Log</a></li>
        <li><a href="messages.php"><i class="fas fa-inbox"></i> Messages</a></li>
        <li><a href="settings.php"><i class="fas fa-gear"></i> Settings</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </nav>
  </div>

  <div class="main-content">
    <div class="top-navbar">
      <div class="flex items-center gap-2">
        <img src="../images/white-logo.png" alt="<?php echo SITE_NAME; ?>" style="height:28px;width:auto;border-radius:6px;">
        <span class="text-xl font-semibold">Customers</span>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="dashboard-card md:col-span-3">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
          <h3 class="text-lg font-medium">Email List</h3>
          <div class="flex gap-2">
            <a class="btn" href="customers.php?export=emails">Export Emails CSV</a>
            <a class="btn" href="customers.php?export=full">Export Full CSV</a>
          </div>
        </div>
        <div class="rounded-lg border overflow-auto">
          <table class="w-full text-sm">
            <thead><tr><th class="text-left p-2">Email</th></tr></thead>
            <tbody>
              <?php foreach ($customers as $c): ?>
                <tr class="border-t"><td class="p-2"><?= htmlspecialchars((string)($c['email'] ?? '')) ?></td></tr>
              <?php endforeach; ?>
              <?php if (empty($customers)): ?><tr><td class="p-2 text-gray-400">No subscribers yet.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="dashboard-card md:col-span-3">
        <h3 class="text-lg font-medium mb-3">Customer Details</h3>
        <div class="rounded-lg border overflow-auto">
          <table class="w-full text-sm">
            <thead>
              <tr>
                <th class="text-left p-2">Email</th>
                <th class="text-left p-2">Name</th>
                <th class="text-left p-2">Joined</th>
                <th class="text-left p-2">Purchases</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($customers as $c): $p = $c['purchases'] ?? []; ?>
                <tr class="border-t">
                  <td class="p-2"><?= htmlspecialchars((string)($c['email'] ?? '')) ?></td>
                  <td class="p-2"><?= htmlspecialchars((string)($c['name'] ?? '')) ?></td>
                  <td class="p-2"><?= htmlspecialchars((string)($c['joined_at'] ?? '')) ?></td>
                  <td class="p-2 text-gray-600">
                    <?php if (empty($p)): ?>
                      <span class="text-gray-400">—</span>
                    <?php else: ?>
                      <ul class="list-disc ml-5">
                        <?php foreach ($p as $row): ?>
                          <li><?= htmlspecialchars((string)($row['date'] ?? '')) ?> — <?= htmlspecialchars((string)($row['summary'] ?? '')) ?> ($<?= htmlspecialchars(number_format((float)($row['total'] ?? 0), 2)) ?>)</li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($customers)): ?><tr><td colspan="4" class="p-2 text-gray-400">No customers yet.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>


