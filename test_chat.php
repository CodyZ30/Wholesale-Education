<?php
session_start();
include_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/check_blocked_ip.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Live Chat - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-center mb-8">Live Chat Test Page</h1>
            
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-semibold mb-4">Test the Live Chat System</h2>
                <p class="text-gray-600 mb-6">
                    This page demonstrates the live chat functionality. The chat widget should appear in the bottom-right corner.
                    You can test the chat by:
                </p>
                
                <ul class="list-disc list-inside space-y-2 mb-6 text-gray-600">
                    <li>Clicking the chat widget to open it</li>
                    <li>Sending messages as a visitor</li>
                    <li>Checking the admin panel to see messages appear in real-time</li>
                    <li>Testing notifications when new messages arrive</li>
                </ul>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-blue-800 mb-2">Admin Panel Access</h3>
                    <p class="text-blue-700 mb-2">To test the admin side of the chat system:</p>
                    <a href="/admin/live_chat.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Open Admin Live Chat
                    </a>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-green-800 mb-2">Features to Test</h3>
                    <ul class="list-disc list-inside space-y-1 text-green-700">
                        <li>Real-time message delivery</li>
                        <li>Admin notifications for new messages</li>
                        <li>Sound notifications</li>
                        <li>Message history</li>
                        <li>Chat status updates</li>
                        <li>Mobile responsiveness</li>
                    </ul>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h3 class="font-semibold text-yellow-800 mb-2">Test Instructions</h3>
                    <ol class="list-decimal list-inside space-y-1 text-yellow-700">
                        <li>Open this page in one browser tab</li>
                        <li>Open the admin panel in another tab</li>
                        <li>Send a message from this page</li>
                        <li>Check if the message appears in the admin panel</li>
                        <li>Send a reply from the admin panel</li>
                        <li>Check if the reply appears on this page</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Live Chat Widget -->
    <script src="/live_chat_widget.js"></script>
</body>
</html>
