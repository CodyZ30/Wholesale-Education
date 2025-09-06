<?php
session_start();
include_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: login.php');
  exit;
}

$configFile = __DIR__ . '/../config/payment_settings.json';
if (!is_dir(__DIR__ . '/../config')) { @mkdir(__DIR__ . '/../config', 0755, true); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $payload = [
    'enable_paypal' => isset($_POST['enable_paypal']),
    'paypal_client_id' => trim((string)($_POST['paypal_client_id'] ?? '')),
    'paypal_secret' => trim((string)($_POST['paypal_secret'] ?? '')),
    'enable_stripe' => isset($_POST['enable_stripe']),
    'stripe_pk' => trim((string)($_POST['stripe_pk'] ?? '')),
    'stripe_sk' => trim((string)($_POST['stripe_sk'] ?? '')),
    'enable_square' => isset($_POST['enable_square']),
    'square_token' => trim((string)($_POST['square_token'] ?? '')),
    'enable_cash' => isset($_POST['enable_cash']),
    'enable_zelle' => isset($_POST['enable_zelle']),
    'zelle_instructions' => trim((string)($_POST['zelle_instructions'] ?? '')),
    'enable_cashapp' => isset($_POST['enable_cashapp']),
    'cashapp_handle' => trim((string)($_POST['cashapp_handle'] ?? '')),
    'enable_apple_pay' => isset($_POST['enable_apple_pay']),
    'enable_google_pay' => isset($_POST['enable_google_pay']),
    'tax_rate' => (float)($_POST['tax_rate'] ?? 0),
  ];
  file_put_contents($configFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  $_SESSION['success_message'] = 'Settings saved.';
  header('Location: settings.php');
  exit;
}

$settings = [];
if (file_exists($configFile)) {
  $settings = json_decode((string)file_get_contents($configFile), true);
  if (!is_array($settings)) $settings = [];
}

function checked($k, $s){ return !empty($s[$k]) ? 'checked' : ''; }
function val($k, $s){ return htmlspecialchars((string)($s[$k] ?? ''), ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - <?php echo SITE_NAME; ?></title>
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
        <li><a class="active" href="settings.php"><i class="fas fa-gear"></i> Settings</a></li>
        <li><a href="messages.php"><i class="fas fa-inbox"></i> Messages</a></li>
        <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </nav>
  </div>

  <div class="main-content">
    <div class="top-navbar">
      <div class="flex items-center gap-2">
        <img src="../images/white-logo.png" alt="<?php echo SITE_NAME; ?>" style="height:28px;width:auto;border-radius:6px;">
        <span class="text-xl font-semibold">Settings</span>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="dashboard-card md:col-span-2">
        <h3 class="text-lg font-medium mb-3">Payment Settings</h3>

        <?php if (!empty($_SESSION['success_message'])): ?>
          <div class="mb-4" style="color:#1dd171; font-weight:600;">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
          </div>
        <?php endif; ?>

        <form method="POST" class="space-y-3">
          <section>
            <button type="button" class="w-full text-left font-semibold mb-2" onclick="this.nextElementSibling.classList.toggle('hidden')">PayPal ▾</button>
            <div class="space-y-2 border rounded p-3 hidden">
            <label class="flex items-center gap-2 mb-2"><input type="checkbox" name="enable_paypal" <?php echo checked('enable_paypal',$settings); ?>> Enable PayPal</label>
            <div class="grid md:grid-cols-2 gap-3">
              <input class="border rounded px-3 py-2" placeholder="Client ID" name="paypal_client_id" value="<?php echo val('paypal_client_id',$settings); ?>">
              <input class="border rounded px-3 py-2" placeholder="Secret" name="paypal_secret" value="<?php echo val('paypal_secret',$settings); ?>">
            </div>
            </div>
          </section>

          <section>
            <button type="button" class="w-full text-left font-semibold mb-2" onclick="this.nextElementSibling.classList.toggle('hidden')">Stripe ▾</button>
            <div class="space-y-2 border rounded p-3 hidden">
            <label class="flex items-center gap-2 mb-2"><input type="checkbox" name="enable_stripe" <?php echo checked('enable_stripe',$settings); ?>> Enable Stripe</label>
            <div class="grid md:grid-cols-2 gap-3">
              <input class="border rounded px-3 py-2" placeholder="Publishable Key" name="stripe_pk" value="<?php echo val('stripe_pk',$settings); ?>">
              <input class="border rounded px-3 py-2" placeholder="Secret Key" name="stripe_sk" value="<?php echo val('stripe_sk',$settings); ?>">
            </div>
            </div>
          </section>

          <section>
            <button type="button" class="w-full text-left font-semibold mb-2" onclick="this.nextElementSibling.classList.toggle('hidden')">Square ▾</button>
            <div class="space-y-2 border rounded p-3 hidden">
            <label class="flex items-center gap-2 mb-2"><input type="checkbox" name="enable_square" <?php echo checked('enable_square',$settings); ?>> Enable Square</label>
            <input class="border rounded px-3 py-2 w-full" placeholder="Access Token" name="square_token" value="<?php echo val('square_token',$settings); ?>">
            </div>
          </section>

          <section>
            <button type="button" class="w-full text-left font-semibold mb-2" onclick="this.nextElementSibling.classList.toggle('hidden')">Offline / Alt Methods ▾</button>
            <div class="space-y-2 border rounded p-3 hidden">
            <label class="flex items-center gap-2"><input type="checkbox" name="enable_cash" <?php echo checked('enable_cash',$settings); ?>> Cash</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="enable_zelle" <?php echo checked('enable_zelle',$settings); ?>> Zelle</label>
            <textarea class="border rounded px-3 py-2 w-full mt-2" placeholder="Zelle instructions (email/phone)" name="zelle_instructions"><?php echo val('zelle_instructions',$settings); ?></textarea>
            <label class="flex items-center gap-2 mt-2"><input type="checkbox" name="enable_cashapp" <?php echo checked('enable_cashapp',$settings); ?>> CashApp</label>
            <input class="border rounded px-3 py-2 w-full" placeholder="$cashapphandle" name="cashapp_handle" value="<?php echo val('cashapp_handle',$settings); ?>">
            <label class="flex items-center gap-2 mt-2"><input type="checkbox" name="enable_apple_pay" <?php echo checked('enable_apple_pay',$settings); ?>> Apple Pay</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="enable_google_pay" <?php echo checked('enable_google_pay',$settings); ?>> Google Pay</label>
            </div>
          </section>

          <div class="pt-2"><button class="btn btn-primary">Save Settings</button></div>
        </form>
      </div>

      <div class="dashboard-card">
        <h3 class="text-lg font-medium mb-3">Additional Settings</h3>
        <div class="text-sm text-gray-400 mb-3">Add another settings group here later (shipping, taxes, emails, etc.).</div>
        <div class="rounded border p-3 space-y-2">
          <h4 class="font-semibold">Tax Settings</h4>
          <label class="block text-sm text-gray-400">Site-wide Tax Rate (decimal). Example: 0.07 for 7%</label>
          <form method="POST" class="flex items-center gap-2">
            <input type="number" name="tax_rate" step="0.001" min="0" max="1" value="<?php echo htmlspecialchars((string)($settings['tax_rate'] ?? 0)); ?>" class="border rounded px-3 py-2 bg-transparent" />
            <button class="btn btn-primary">Save</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>


