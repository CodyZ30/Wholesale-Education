<?php
session_start();
include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/../check_blocked_ip.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Gotta.Fish</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <h1>Support Page Test</h1>
    <p>If you can see this, the basic support page is working.</p>
    <p><a href="/support/knowledge-base/all">Go to Knowledge Base</a></p>
</body>
</html>
