<?php
// admin/set_language.php — Language switcher endpoint

session_start();
include_once __DIR__ . '/translation.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Get language from POST data
$language = $_POST['language'] ?? '';

// Validate language
if (empty($language) || !array_key_exists($language, Translation::getAvailableLanguages())) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid language']);
    exit;
}

// Set the language
if (Translation::setLanguage($language)) {
    // Redirect to dashboard instead of returning JSON
    header('Location: dashboard.php');
    exit;
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update language']);
}
?>
