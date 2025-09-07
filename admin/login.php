<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
session_start();
include_once __DIR__ . '/../includes/config.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
        // error_log('CSRF token validation failed for username: ' . ($_POST['username'] ?? 'N/A'));
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Path to the users JSON file
        $usersFile = __DIR__ . '/../users.json';

        // Load users
        $users = [];
        if (file_exists($usersFile)) {
            $json_content = file_get_contents($usersFile);
            $users = json_decode($json_content, true);
            if (!is_array($users)) {
                $users = [];
                // error_log('users.json is empty or malformed.');
            }
        } else {
            // error_log('users.json file not found at: ' . $usersFile);
        }

        $authenticated = false;
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_user_id'] = $user['id'];
                    $authenticated = true;
                    // error_log('Admin login successful for username: ' . $username);
                    break;
                } else {
                    // error_log('Password verification failed for username: ' . $username);
                }
            }
        }

        if ($authenticated) {
            header('Location: dashboard.php'); // Redirect to admin dashboard
            exit;
        } else {
            $error = 'Invalid username or password.';
            // error_log('Admin login failed for username: ' . $username . '. Invalid username or password.');
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <div class="min-h-screen flex items-center justify-center" style="background: radial-gradient(600px 300px at 50% 0%, rgba(29,209,113,0.12), transparent), #0b0d10;">
      <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8 relative">
        <div class="flex items-center justify-center mb-6">
          <img src="../images/white-logo.png" alt="<?php echo SITE_NAME; ?> Logo" style="height:40px;width:auto;background:#0b0d10;border-radius:8px;padding:6px;">
        </div>
        <h2 class="text-xl font-semibold text-center mb-4">Employee Portal</h2>
        <?php if ($error): ?>
          <div class="mb-4 text-center" style="color:#ef4444; font-weight:600;">&nbsp;<?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-3">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$csrf_token); ?>">
          <input type="text" name="username" placeholder="Username" class="w-full border rounded px-3 py-2" required>
          <input type="password" name="password" placeholder="Password" class="w-full border rounded px-3 py-2" required>
          <button type="submit" class="btn btn-primary w-full">Login</button>
        </form>
      </div>
    </div>
</body>
</html>
