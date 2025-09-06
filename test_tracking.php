<?php
// Test script to manually trigger traffic tracking
session_start();
require_once __DIR__ . '/includes/config.php';

// Simulate a visit
$now = gmdate('c');
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Test User Agent';

// Get additional server data
$forwarded_for = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
$real_ip = $_SERVER['HTTP_X_REAL_IP'] ?? '';
$accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
$accept_encoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
$connection = $_SERVER['HTTP_CONNECTION'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';

// Parse User Agent for device info
function parseUserAgent($ua) {
  $device = [
    'browser' => 'Unknown',
    'browser_version' => '',
    'os' => 'Unknown',
    'os_version' => '',
    'device_type' => 'desktop',
    'is_mobile' => false,
    'is_tablet' => false,
    'is_bot' => false
  ];
  
  if (empty($ua)) return $device;
  
  // Detect mobile/tablet
  if (preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/i', $ua)) {
    $device['is_mobile'] = true;
    $device['device_type'] = 'mobile';
  }
  if (preg_match('/iPad|Android.*Tablet|Windows.*Touch/i', $ua)) {
    $device['is_tablet'] = true;
    $device['device_type'] = 'tablet';
  }
  
  // Detect bots
  if (preg_match('/bot|crawler|spider|scraper|facebook|twitter|linkedin|pinterest|slack|discord/i', $ua)) {
    $device['is_bot'] = true;
    $device['device_type'] = 'bot';
  }
  
  // Browser detection
  if (preg_match('/Chrome\/([0-9.]+)/i', $ua, $matches)) {
    $device['browser'] = 'Chrome';
    $device['browser_version'] = $matches[1];
  } elseif (preg_match('/Firefox\/([0-9.]+)/i', $ua, $matches)) {
    $device['browser'] = 'Firefox';
    $device['browser_version'] = $matches[1];
  } elseif (preg_match('/Safari\/([0-9.]+)/i', $ua, $matches)) {
    $device['browser'] = 'Safari';
    $device['browser_version'] = $matches[1];
  } elseif (preg_match('/Edge\/([0-9.]+)/i', $ua, $matches)) {
    $device['browser'] = 'Edge';
    $device['browser_version'] = $matches[1];
  } elseif (preg_match('/MSIE ([0-9.]+)/i', $ua, $matches)) {
    $device['browser'] = 'Internet Explorer';
    $device['browser_version'] = $matches[1];
  }
  
  // OS detection
  if (preg_match('/Windows NT ([0-9.]+)/i', $ua, $matches)) {
    $device['os'] = 'Windows';
    $device['os_version'] = $matches[1];
  } elseif (preg_match('/Mac OS X ([0-9_]+)/i', $ua, $matches)) {
    $device['os'] = 'macOS';
    $device['os_version'] = str_replace('_', '.', $matches[1]);
  } elseif (preg_match('/Linux/i', $ua)) {
    $device['os'] = 'Linux';
  } elseif (preg_match('/Android ([0-9.]+)/i', $ua, $matches)) {
    $device['os'] = 'Android';
    $device['os_version'] = $matches[1];
  } elseif (preg_match('/iPhone OS ([0-9_]+)/i', $ua, $matches)) {
    $device['os'] = 'iOS';
    $device['os_version'] = str_replace('_', '.', $matches[1]);
  }
  
  return $device;
}

// Get location data (basic IP geolocation)
function getLocationData($ip) {
  // Skip private/local IPs
  if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
    return ['country' => 'Local', 'region' => 'Local', 'city' => 'Local'];
  }
  
  // Simple IP geolocation (you can replace with a paid service for more accuracy)
  $context = stream_context_create([
    'http' => [
      'timeout' => 2,
      'user_agent' => 'GottaFish/1.0'
    ]
  ]);
  
  try {
    $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,timezone,isp", false, $context);
    if ($response) {
      $data = json_decode($response, true);
      if ($data && $data['status'] === 'success') {
        return [
          'country' => $data['country'] ?? 'Unknown',
          'region' => $data['regionName'] ?? 'Unknown',
          'city' => $data['city'] ?? 'Unknown',
          'timezone' => $data['timezone'] ?? 'Unknown',
          'isp' => $data['isp'] ?? 'Unknown'
        ];
      }
    }
  } catch (Exception $e) {
    // Fallback to unknown
  }
  
  return ['country' => 'Unknown', 'region' => 'Unknown', 'city' => 'Unknown'];
}

$device_info = parseUserAgent($ua);
$location_info = getLocationData($ip);

$record = [
  'ts' => $now,
  'session_id' => session_id(),
  'ip' => $ip,
  'real_ip' => $real_ip,
  'forwarded_for' => $forwarded_for,
  'ua' => $ua,
  'href' => $_SERVER['REQUEST_URI'] ?? '',
  'path' => $_SERVER['REQUEST_URI'] ?? '',
  'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
  'host' => $host,
  'accept_language' => $accept_language,
  'accept_encoding' => $accept_encoding,
  'connection' => $connection,
  'device' => $device_info,
  'location' => $location_info,
  'utm' => [
    'source' => $_GET['utm_source'] ?? '',
    'medium' => $_GET['utm_medium'] ?? '',
    'campaign' => $_GET['utm_campaign'] ?? '',
    'term' => $_GET['utm_term'] ?? '',
    'content' => $_GET['utm_content'] ?? ''
  ]
];

$file = __DIR__ . '/data/traffic.json';
if (!file_exists(dirname($file))) {
  @mkdir(dirname($file), 0775, true);
}

$rows = [];
if (file_exists($file)) {
  $json = file_get_contents($file);
  $rows = json_decode($json, true);
  if (!is_array($rows)) $rows = [];
}

$rows[] = $record;
file_put_contents($file, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "<h1>Traffic Tracking Test</h1>";
echo "<p>Visit logged successfully!</p>";
echo "<p>IP: " . htmlspecialchars($ip) . "</p>";
echo "<p>Browser: " . htmlspecialchars($device_info['browser']) . "</p>";
echo "<p>OS: " . htmlspecialchars($device_info['os']) . "</p>";
echo "<p>Location: " . htmlspecialchars($location_info['city'] . ', ' . $location_info['country']) . "</p>";
echo "<p>Total visits in database: " . count($rows) . "</p>";
echo "<p><a href='/admin/traffic.php'>View Traffic Log</a></p>";
echo "<p><a href='/admin/dashboard.php'>View Dashboard</a></p>";
?>
