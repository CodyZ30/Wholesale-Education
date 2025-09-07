<?php
session_start();
include_once __DIR__ . '/includes/config.php';

// CSRF
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || !hash_equals($csrf, (string)$_POST['csrf_token'])) {
    $errors[] = 'Invalid form session. Please reload the page and try again.';
  }

  $name = trim((string)($_POST['name'] ?? ''));
  $email = trim((string)($_POST['email'] ?? ''));
  $subject = trim((string)($_POST['subject'] ?? ''));
  $phone = trim((string)($_POST['phone'] ?? ''));
  $message = trim((string)($_POST['message'] ?? ''));
  $reason = trim((string)($_POST['reason'] ?? ''));
  $hp = trim((string)($_POST['website'] ?? '')); // honeypot

  if ($hp !== '') { $errors[] = 'Spam detected.'; }
  if ($name === '') { $errors[] = 'Name is required.'; }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'A valid email is required.'; }
  if ($message === '') { $errors[] = 'Message is required.'; }

  if (!$errors) {
    $record = [
      'id' => sha1(uniqid((string)mt_rand(), true)),
      'name' => $name,
      'email' => $email,
      'subject' => $subject,
      'reason' => $reason,
      'phone' => $phone,
      'message' => $message,
      'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
      'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
      'created_at' => date('c'),
    ];

    $file = __DIR__ . '/contact_messages.json';
    $list = [];
    if (file_exists($file)) {
      $decoded = json_decode((string)file_get_contents($file), true);
      if (is_array($decoded)) { $list = $decoded; }
    }
    $list[] = $record;
    file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $success = true;
  }
}
?>
<?php include 'includes/header.php'; ?>

<main class="container mx-auto px-4 md:px-6 py-10">
  <header class="mb-8 text-center">
    <h1 class="text-4xl md:text-5xl font-extrabold mb-3">Contact <?php echo SITE_NAME; ?></h1>
    <p class="text-gray-700 max-w-2xl mx-auto">Have a question about an order or our products? Send us a message and we’ll reply as soon as possible.</p>
  </header>

  <div class="grid md:grid-cols-3 gap-8">
    <section class="md:col-span-2 bg-white rounded-2xl border shadow p-6">
      <?php if ($success): ?>
        <div class="mb-4 px-4 py-3 rounded" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;">Thanks! Your message has been received.</div>
      <?php endif; ?>
      <?php if ($errors): ?>
        <div class="mb-4 px-4 py-3 rounded" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;">
          <?php foreach ($errors as $e): ?>
            <div><?php echo htmlspecialchars((string)$e); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$csrf); ?>">
        <!-- Honeypot -->
        <input type="text" name="website" value="" style="display:none" tabindex="-1" autocomplete="off">

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input name="name" class="w-full border rounded px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" class="w-full border rounded px-3 py-2" required>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Phone (optional)</label>
          <input type="tel" name="phone" class="w-full border rounded px-3 py-2" placeholder="(123) 456-7890">
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Reason</label>
            <select name="reason" class="w-full border rounded px-3 py-2">
              <option value="General">General</option>
              <option value="Order Support">Order Support</option>
              <option value="Product Question">Product Question</option>
              <option value="Wholesale">Wholesale</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Subject (optional)</label>
            <input name="subject" class="w-full border rounded px-3 py-2">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Message</label>
          <textarea name="message" rows="6" class="w-full border rounded px-3 py-2" required></textarea>
        </div>

        <div class="pt-2">
          <button class="bg-black text-white px-5 py-2.5 rounded-full font-semibold">Send Message</button>
        </div>
      </form>
    </section>

    <aside class="space-y-4">
      <div class="bg-white rounded-2xl border shadow p-6">
        <h2 class="text-lg font-bold mb-2">Support</h2>
        <p class="text-gray-700">Email us: support@<?php echo str_replace(' ', '', strtolower(SITE_NAME)); ?>.com</p>
        <p class="text-gray-700">Hours: Mon–Fri, 9am–5pm ET</p>
      </div>
      <div class="bg-white rounded-2xl border shadow p-6">
        <h2 class="text-lg font-bold mb-2">Where we’re based</h2>
        <p class="text-gray-700">New Jersey, United States</p>
      </div>
    </aside>
  </div>
</main>

<?php include 'footer.php'; ?>


