<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/translation.php';
include_once __DIR__ . '/layout.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$chatFile = __DIR__ . '/../data/live_chat.json';
$chats = [];
if (file_exists($chatFile)) {
    $chats = json_decode(file_get_contents($chatFile), true) ?: [];
}

$error = '';
$success_message = '';

// Handle AJAX requests for auto-refresh
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'chats' => $chats
    ]);
    exit;
}

// Handle chat actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'send_message') {
            $chat_id = $_POST['chat_id'] ?? '';
            $message = trim($_POST['message'] ?? '');
            
            if (!empty($message) && isset($chats[$chat_id])) {
                $chats[$chat_id]['messages'][] = [
                    'id' => uniqid(),
                    'sender' => 'admin',
                    'admin_name' => $_SESSION['admin_username'] ?? 'Admin',
                    'message' => $message,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'type' => 'text'
                ];
                $chats[$chat_id]['last_activity'] = date('Y-m-d H:i:s');
                $chats[$chat_id]['status'] = 'active';
                
                file_put_contents($chatFile, json_encode($chats, JSON_PRETTY_PRINT));
                $success_message = 'Message sent successfully.';
            }
        } elseif ($action === 'close_chat') {
            $chat_id = $_POST['chat_id'] ?? '';
            if (isset($chats[$chat_id])) {
                $chats[$chat_id]['status'] = 'closed';
                $chats[$chat_id]['closed_at'] = date('Y-m-d H:i:s');
                $chats[$chat_id]['closed_by'] = $_SESSION['admin_username'] ?? 'Admin';
                file_put_contents($chatFile, json_encode($chats, JSON_PRETTY_PRINT));
                $success_message = 'Chat closed successfully.';
            }
        }
    }
}

// Filter chats
$status_filter = $_GET['status'] ?? '';
$filtered_chats = $chats;
if (!empty($status_filter)) {
    $filtered_chats = array_filter($filtered_chats, function($chat) use ($status_filter) {
        return $chat['status'] === $status_filter;
    });
}

// Sort by last activity (most recent first)
uasort($filtered_chats, function($a, $b) {
    return strtotime($b['last_activity']) - strtotime($a['last_activity']);
});

admin_layout_start(__('live_chat'), 'live_chat');
?>

<div class="dashboard-grid">
    <!-- Chat Overview -->
    <div class="dashboard-card">
        <h3 class="text-lg font-medium mb-4"><?php echo __('chat_overview'); ?></h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-500"><?php echo count($chats); ?></div>
                <div class="text-sm text-gray-400"><?php echo __('total_chats'); ?></div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-500"><?php echo count(array_filter($chats, fn($c) => $c['status'] === 'active')); ?></div>
                <div class="text-sm text-gray-400"><?php echo __('active_chats'); ?></div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-500"><?php echo count(array_filter($chats, fn($c) => $c['status'] === 'waiting')); ?></div>
                <div class="text-sm text-gray-400"><?php echo __('waiting'); ?></div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-500"><?php echo count(array_filter($chats, fn($c) => $c['status'] === 'closed')); ?></div>
                <div class="text-sm text-gray-400"><?php echo __('closed'); ?></div>
            </div>
        </div>
    </div>

    <!-- Chat Filters -->
    <div class="dashboard-card">
        <h3 class="text-lg font-medium mb-4"><?php echo __('filters'); ?></h3>
        <form method="GET" class="space-y-3">
            <div>
                <label class="form-label"><?php echo __('status'); ?>:</label>
                <select name="status" class="form-input">
                    <option value=""><?php echo __('all_statuses'); ?></option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>><?php echo __('active'); ?></option>
                    <option value="waiting" <?php echo $status_filter === 'waiting' ? 'selected' : ''; ?>><?php echo __('waiting'); ?></option>
                    <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>><?php echo __('closed'); ?></option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-full"><?php echo __('apply_filters'); ?></button>
            <a href="live_chat.php" class="btn btn-secondary w-full"><?php echo __('clear'); ?></a>
        </form>
    </div>

    <!-- Chats List -->
    <div class="dashboard-card md:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium"><?php echo __('live_chats'); ?></h3>
            <div class="text-sm text-gray-400">
                <?php echo count($filtered_chats); ?> <?php echo __('chats_found'); ?>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error mb-4"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success mb-4"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($filtered_chats)): ?>
            <div class="space-y-4">
                <?php foreach ($filtered_chats as $chat_id => $chat): ?>
                    <div class="bg-gray-800 p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="text-lg font-semibold"><?php echo htmlspecialchars($chat['visitor_name'] ?? 'Anonymous'); ?></h4>
                                    <span class="px-2 py-1 rounded text-xs <?php 
                                        echo match($chat['status']) {
                                            'active' => 'bg-green-100 text-green-800',
                                            'waiting' => 'bg-yellow-100 text-yellow-800',
                                            'closed' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst($chat['status']); ?>
                                    </span>
                                </div>
                                <p class="text-gray-400 mb-2"><?php echo htmlspecialchars($chat['visitor_email'] ?? 'No email provided'); ?></p>
                                <div class="text-sm text-gray-400 mb-2">
                                    <span><i class="fas fa-clock mr-1"></i>Started: <?php echo date('M j, Y H:i', strtotime($chat['started_at'])); ?></span>
                                    <span class="ml-4"><i class="fas fa-comments mr-1"></i><?php echo count($chat['messages'] ?? []); ?> messages</span>
                                </div>
                                <?php if (!empty($chat['messages'])): ?>
                                    <div class="text-sm text-gray-300">
                                        Last message: <?php echo htmlspecialchars(substr(end($chat['messages'])['message'], 0, 100)); ?>...
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <button onclick="openChat('<?php echo $chat_id; ?>')" class="btn btn-primary text-sm">
                                    <i class="fas fa-comments mr-1"></i><?php echo __('open_chat'); ?>
                                </button>
                                <?php if ($chat['status'] !== 'closed'): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('<?php echo __('confirm_close_chat'); ?>');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="action" value="close_chat">
                                        <input type="hidden" name="chat_id" value="<?php echo htmlspecialchars($chat_id); ?>">
                                        <button type="submit" class="btn btn-danger text-sm">
                                            <i class="fas fa-times mr-1"></i><?php echo __('close'); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-comments text-4xl mb-4"></i>
                <p><?php echo __('no_chats_found'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chat Modal -->
<div id="chatModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] flex flex-col">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 class="text-xl font-semibold" id="chatTitle"><?php echo __('live_chat'); ?></h3>
                <button onclick="closeChatModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="flex-1 flex flex-col">
                <div id="chatMessages" class="flex-1 p-4 overflow-y-auto bg-gray-50" style="max-height: 400px;">
                    <!-- Messages will be loaded here -->
                </div>
                <div class="p-4 border-t">
                    <form id="chatForm" method="POST" class="flex gap-2">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="action" value="send_message">
                        <input type="hidden" name="chat_id" id="currentChatId">
                        <input type="text" name="message" id="messageInput" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?php echo __('type_message'); ?>" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let chats = <?php echo json_encode($chats); ?>;
let currentChatId = null;
let pollInterval = null;
let notificationSound = null;

// Initialize notification sound
function initNotificationSound() {
    try {
        // Create a simple notification sound using Web Audio API
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        notificationSound = () => {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        };
    } catch (e) {
        console.log('Audio context not supported');
    }
}

// Show notification
function showNotification(title, message, chatId) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg z-50 max-w-sm';
    notification.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <i class="fas fa-comments text-xl"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-semibold">${title}</h4>
                <p class="text-sm opacity-90 mt-1">${message}</p>
                <div class="flex gap-2 mt-3">
                    <button onclick="openChatFromNotification('${chatId}')" class="bg-white text-blue-600 px-3 py-1 rounded text-sm font-medium hover:bg-gray-100">
                        Open Chat
                    </button>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="bg-transparent border border-white text-white px-3 py-1 rounded text-sm hover:bg-white hover:text-blue-600">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Play notification sound
    if (notificationSound) {
        notificationSound();
    }
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 10000);
}

function openChatFromNotification(chatId) {
    // Remove notification
    const notifications = document.querySelectorAll('.fixed.top-4.right-4');
    notifications.forEach(n => n.remove());
    
    // Open chat
    openChat(chatId);
}

function openChat(chatId) {
    const chat = chats[chatId];
    if (!chat) return;
    
    currentChatId = chatId;
    document.getElementById('chatTitle').textContent = `Chat with ${chat.visitor_name || 'Anonymous'}`;
    document.getElementById('currentChatId').value = chatId;
    
    // Load messages
    loadChatMessages(chatId);
    
    document.getElementById('chatModal').classList.remove('hidden');
    
    // Start polling for this specific chat
    startChatPolling(chatId);
}

function loadChatMessages(chatId) {
    const messagesContainer = document.getElementById('chatMessages');
    messagesContainer.innerHTML = '';
    
    const chat = chats[chatId];
    if (!chat || !chat.messages || chat.messages.length === 0) {
        messagesContainer.innerHTML = '<p class="text-gray-500 text-center">No messages yet.</p>';
        return;
    }
    
    chat.messages.forEach(message => {
        addMessageToUI(message);
    });
    
    // Scroll to bottom
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function addMessageToUI(message) {
    const messagesContainer = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `mb-3 ${message.sender === 'admin' ? 'text-right' : 'text-left'}`;
    
    const messageContent = document.createElement('div');
    messageContent.className = `inline-block max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
        message.sender === 'admin' 
            ? 'bg-blue-500 text-white' 
            : 'bg-gray-200 text-gray-800'
    }`;
    messageContent.innerHTML = `
        <div class="text-sm">${escapeHtml(message.message)}</div>
        <div class="text-xs mt-1 opacity-75">
            ${message.sender === 'admin' ? (message.admin_name || 'Admin') : 'Visitor'} - 
            ${new Date(message.timestamp).toLocaleTimeString()}
        </div>
    `;
    
    messageDiv.appendChild(messageContent);
    messagesContainer.appendChild(messageDiv);
}

function closeChatModal() {
    document.getElementById('chatModal').classList.add('hidden');
    currentChatId = null;
    
    // Stop polling
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
}

function startChatPolling(chatId) {
    // Clear existing polling
    if (pollInterval) {
        clearInterval(pollInterval);
    }
    
    // Start new polling
    pollInterval = setInterval(() => {
        checkForNewMessages(chatId);
    }, 2000); // Check every 2 seconds
}

async function checkForNewMessages(chatId) {
    try {
        const response = await fetch(`/api/chat/messages?chat_id=${chatId}`);
        const data = await response.json();
        
        if (data.success && data.messages) {
            const chat = chats[chatId];
            if (chat && chat.messages) {
                const currentMessageCount = chat.messages.length;
                const newMessageCount = data.messages.length;
                
                // If there are new messages
                if (newMessageCount > currentMessageCount) {
                    // Update local chats data
                    chats[chatId].messages = data.messages;
                    
                    // If this chat is currently open, update the UI
                    if (currentChatId === chatId) {
                        loadChatMessages(chatId);
                    } else {
                        // Show notification for new messages
                        const newMessages = data.messages.slice(currentMessageCount);
                        newMessages.forEach(msg => {
                            if (msg.sender === 'visitor') {
                                showNotification(
                                    `New message from ${chat.visitor_name || 'Anonymous'}`,
                                    msg.message,
                                    chatId
                                );
                            }
                        });
                    }
                }
            }
        }
    } catch (error) {
        console.error('Failed to check for new messages:', error);
    }
}

// Auto-refresh chat list every 10 seconds
setInterval(() => {
    fetch('/admin/live_chat.php?ajax=1')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                chats = data.chats;
                updateChatList();
            }
        })
        .catch(error => console.error('Failed to refresh chat list:', error));
}, 10000);

function updateChatList() {
    // Update the chat list UI with new data
    const chatList = document.querySelector('.space-y-4');
    if (!chatList) return;
    
    chatList.innerHTML = '';
    
    Object.entries(chats).forEach(([chatId, chat]) => {
        const chatDiv = document.createElement('div');
        chatDiv.className = 'bg-gray-800 p-4 rounded-lg';
        chatDiv.innerHTML = `
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h4 class="text-lg font-semibold">${escapeHtml(chat.visitor_name || 'Anonymous')}</h4>
                        <span class="px-2 py-1 rounded text-xs ${getStatusClass(chat.status)}">
                            ${chat.status}
                        </span>
                    </div>
                    <p class="text-gray-400 mb-2">${escapeHtml(chat.visitor_email || 'No email provided')}</p>
                    <div class="text-sm text-gray-400 mb-2">
                        <span><i class="fas fa-clock mr-1"></i>Started: ${new Date(chat.started_at).toLocaleString()}</span>
                        <span class="ml-4"><i class="fas fa-comments mr-1"></i>${chat.messages ? chat.messages.length : 0} messages</span>
                    </div>
                    ${chat.messages && chat.messages.length > 0 ? `
                        <div class="text-sm text-gray-300">
                            Last message: ${escapeHtml(chat.messages[chat.messages.length - 1].message.substring(0, 100))}...
                        </div>
                    ` : ''}
                </div>
                <div class="flex gap-2 ml-4">
                    <button onclick="openChat('${chatId}')" class="btn btn-primary text-sm">
                        <i class="fas fa-comments mr-1"></i>Open Chat
                    </button>
                    ${chat.status !== 'closed' ? `
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to close this chat?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="action" value="close_chat">
                            <input type="hidden" name="chat_id" value="${chatId}">
                            <button type="submit" class="btn btn-danger text-sm">
                                <i class="fas fa-times mr-1"></i>Close
                            </button>
                        </form>
                    ` : ''}
                </div>
            </div>
        `;
        chatList.appendChild(chatDiv);
    });
}

function getStatusClass(status) {
    switch (status) {
        case 'active': return 'bg-green-100 text-green-800';
        case 'waiting': return 'bg-yellow-100 text-yellow-800';
        case 'closed': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize notification sound
initNotificationSound();

// Close modal when clicking outside
document.getElementById('chatModal').addEventListener('click', function(e) {
    if (e.target === this) closeChatModal();
});

// Handle form submission
document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const chatId = document.getElementById('currentChatId').value;
    const message = document.getElementById('messageInput').value.trim();
    
    if (!message || !chatId) return;
    
    // Add message to UI immediately
    const tempMessage = {
        id: 'temp_' + Date.now(),
        sender: 'admin',
        admin_name: 'You',
        message: message,
        timestamp: new Date().toISOString()
    };
    
    addMessageToUI(tempMessage);
    document.getElementById('messageInput').value = '';
    
    // Send to server
    fetch('/admin/live_chat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'csrf_token': '<?php echo htmlspecialchars($csrf_token); ?>',
            'action': 'send_message',
            'chat_id': chatId,
            'message': message
        })
    })
    .then(response => response.text())
    .then(() => {
        // Message sent successfully
    })
    .catch(error => {
        console.error('Failed to send message:', error);
        // Remove the temporary message
        const messagesContainer = document.getElementById('chatMessages');
        const lastMessage = messagesContainer.lastElementChild;
        if (lastMessage) {
            lastMessage.remove();
        }
    });
});
</script>

<?php admin_layout_end(); ?>
