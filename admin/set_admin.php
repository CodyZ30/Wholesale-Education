<?php
// One-time helper to set the admin password to a chosen value.
// Security: Only allow from localhost and require explicit query param 'pw='

if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'])) {
  http_response_code(403);
  echo 'Forbidden';
  exit;
}

$newPassword = $_GET['pw'] ?? '';
if ($newPassword === '') {
  header('Content-Type: text/plain');
  echo "Usage: /admin/set_admin.php?pw=NEW_PASSWORD\n";
  exit;
}

$usersFile = dirname(__DIR__) . '/users.json';
if (!file_exists($usersFile)) {
  http_response_code(500);
  echo 'users.json not found';
  exit;
}

$data = json_decode((string)file_get_contents($usersFile), true);
if (!is_array($data)) { $data = []; }

$hash = password_hash($newPassword, PASSWORD_BCRYPT);

$updated = false;
foreach ($data as &$u) {
  if (($u['username'] ?? '') === 'admin') {
    $u['password'] = $hash;
    $updated = true;
    break;
  }
}
unset($u);

if (!$updated) {
  $data[] = [
    'id' => 1,
    'username' => 'admin',
    'email' => 'admin@example.com',
    'password' => $hash,
    'role' => 'administrator',
  ];
}

file_put_contents($usersFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

header('Content-Type: text/plain');
echo "Admin password updated. You can now log in.\n";
echo "IMPORTANT: Delete /admin/set_admin.php after use.";
?>


