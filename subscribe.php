<?php
// Public endpoint to capture newsletter signups
// Accepts POST form fields or JSON: { email, name }

header('Content-Type: application/json');

function read_json_file(string $path): array {
  if (!file_exists($path)) return [];
  $raw = file_get_contents($path);
  $data = json_decode((string)$raw, true);
  return is_array($data) ? $data : [];
}

$raw = file_get_contents('php://input');
$data = [];
if (!empty($raw)) {
  $tmp = json_decode($raw, true);
  if (is_array($tmp)) { $data = $tmp; }
}

// Fallback to form-encoded
if (empty($data)) { $data = $_POST; }

$email = isset($data['email']) ? trim((string)$data['email']) : '';
$name  = isset($data['name'])  ? trim((string)$data['name'])  : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Invalid email']);
  exit;
}

$storePath = __DIR__ . '/data/customers.json';
$list = read_json_file($storePath);

// Normalize as map keyed by lowercased email
$map = [];
foreach ($list as $c) {
  if (!empty($c['email'])) { $map[strtolower((string)$c['email'])] = $c; }
}

$key = strtolower($email);
$now = date('c');
if (!isset($map[$key])) {
  $map[$key] = [
    'id' => sha1($key),
    'email' => $email,
    'name' => $name,
    'joined_at' => $now,
    'purchases' => [],
  ];
} else {
  if ($name !== '' && empty($map[$key]['name'])) { $map[$key]['name'] = $name; }
}

// Persist as array
$out = array_values($map);
file_put_contents($storePath, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(['success' => true]);
exit;
?>


