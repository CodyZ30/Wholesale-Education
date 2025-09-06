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

$supportFile = __DIR__ . '/../data/support_tickets.json';
$tickets = [];
if (file_exists($supportFile)) {
    $tickets = json_decode(file_get_contents($supportFile), true) ?: [];
}

$error = '';
$success_message = '';

// Handle ticket actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_status') {
            $ticket_id = $_POST['ticket_id'] ?? '';
            $new_status = $_POST['status'] ?? '';
            $admin_note = trim($_POST['admin_note'] ?? '');
            
            if (isset($tickets[$ticket_id])) {
                $tickets[$ticket_id]['status'] = $new_status;
                $tickets[$ticket_id]['updated_at'] = date('Y-m-d H:i:s');
                $tickets[$ticket_id]['updated_by'] = $_SESSION['admin_username'] ?? 'Admin';
                
                if (!empty($admin_note)) {
                    $tickets[$ticket_id]['admin_notes'][] = [
                        'note' => $admin_note,
                        'admin' => $_SESSION['admin_username'] ?? 'Admin',
                        'date' => date('Y-m-d H:i:s')
                    ];
                }
                
                file_put_contents($supportFile, json_encode($tickets, JSON_PRETTY_PRINT));
                $success_message = 'Ticket status updated successfully.';
            }
        } elseif ($action === 'delete') {
            $ticket_id = $_POST['ticket_id'] ?? '';
            if (isset($tickets[$ticket_id])) {
                unset($tickets[$ticket_id]);
                file_put_contents($supportFile, json_encode($tickets, JSON_PRETTY_PRINT));
                $success_message = 'Ticket deleted successfully.';
            }
        }
    }
}

// Filter tickets
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$search = $_GET['search'] ?? '';

$filtered_tickets = $tickets;
if (!empty($status_filter)) {
    $filtered_tickets = array_filter($filtered_tickets, function($ticket) use ($status_filter) {
        return $ticket['status'] === $status_filter;
    });
}
if (!empty($priority_filter)) {
    $filtered_tickets = array_filter($filtered_tickets, function($ticket) use ($priority_filter) {
        return $ticket['priority'] === $priority_filter;
    });
}
if (!empty($search)) {
    $filtered_tickets = array_filter($filtered_tickets, function($ticket) use ($search) {
        return stripos($ticket['subject'], $search) !== false ||
               stripos($ticket['message'], $search) !== false ||
               stripos($ticket['email'], $search) !== false;
    });
}

// Sort by date (newest first)
uasort($filtered_tickets, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

admin_layout_start(__('support'), 'support');
?>

<div class="dashboard-grid">
    <!-- Support Overview Cards -->
    <div class="dashboard-card">
        <h3 class="text-lg font-medium mb-4"><?php echo __('support_overview'); ?></h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-500"><?php echo count($tickets); ?></div>
                <div class="text-sm text-gray-400"><?php echo __('total_tickets'); ?></div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-500"><?php echo count(array_filter($tickets, fn($t) => $t['status'] === 'open')); ?></div>
                <div class="text-sm text-gray-400"><?php echo __('open_tickets'); ?></div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-500"><?php echo count(array_filter($tickets, fn($t) => $t['status'] === 'resolved')); ?></div>
                <div class="text-sm text-gray-400"><?php echo __('resolved'); ?></div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-500"><?php echo count(array_filter($tickets, fn($t) => $t['priority'] === 'high')); ?></div>
                <div class="text-sm text-gray-400"><?php echo __('high_priority'); ?></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="dashboard-card">
        <h3 class="text-lg font-medium mb-4"><?php echo __('filters'); ?></h3>
        <form method="GET" class="space-y-3">
            <div>
                <label class="form-label"><?php echo __('status'); ?>:</label>
                <select name="status" class="form-input">
                    <option value=""><?php echo __('all_statuses'); ?></option>
                    <option value="open" <?php echo $status_filter === 'open' ? 'selected' : ''; ?>><?php echo __('open'); ?></option>
                    <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>><?php echo __('in_progress'); ?></option>
                    <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>><?php echo __('resolved'); ?></option>
                    <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>><?php echo __('closed'); ?></option>
                </select>
            </div>
            <div>
                <label class="form-label"><?php echo __('priority'); ?>:</label>
                <select name="priority" class="form-input">
                    <option value=""><?php echo __('all_priorities'); ?></option>
                    <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>><?php echo __('low'); ?></option>
                    <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>><?php echo __('medium'); ?></option>
                    <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>><?php echo __('high'); ?></option>
                    <option value="urgent" <?php echo $priority_filter === 'urgent' ? 'selected' : ''; ?>><?php echo __('urgent'); ?></option>
                </select>
            </div>
            <div>
                <label class="form-label"><?php echo __('search'); ?>:</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-input" placeholder="<?php echo __('search_tickets'); ?>">
            </div>
            <button type="submit" class="btn btn-primary w-full"><?php echo __('apply_filters'); ?></button>
            <a href="support.php" class="btn btn-secondary w-full"><?php echo __('clear'); ?></a>
        </form>
    </div>

    <!-- Tickets List -->
    <div class="dashboard-card md:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium"><?php echo __('support_tickets'); ?></h3>
            <div class="text-sm text-gray-400">
                <?php echo count($filtered_tickets); ?> <?php echo __('tickets_found'); ?>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error mb-4"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success mb-4"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($filtered_tickets)): ?>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php echo __('ticket_id'); ?></th>
                            <th><?php echo __('subject'); ?></th>
                            <th><?php echo __('customer'); ?></th>
                            <th><?php echo __('priority'); ?></th>
                            <th><?php echo __('status'); ?></th>
                            <th><?php echo __('created'); ?></th>
                            <th><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filtered_tickets as $ticket_id => $ticket): ?>
                            <tr>
                                <td class="font-mono text-sm">#<?php echo substr($ticket_id, 0, 8); ?></td>
                                <td>
                                    <div class="font-medium"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                    <div class="text-sm text-gray-400 truncate max-w-xs">
                                        <?php echo htmlspecialchars(substr($ticket['message'], 0, 100)); ?>...
                                    </div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($ticket['name']); ?></div>
                                    <div class="text-sm text-gray-400"><?php echo htmlspecialchars($ticket['email']); ?></div>
                                </td>
                                <td>
                                    <span class="px-2 py-1 rounded text-xs <?php 
                                        echo match($ticket['priority']) {
                                            'urgent' => 'bg-red-100 text-red-800',
                                            'high' => 'bg-orange-100 text-orange-800',
                                            'medium' => 'bg-yellow-100 text-yellow-800',
                                            'low' => 'bg-green-100 text-green-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst($ticket['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="px-2 py-1 rounded text-xs <?php 
                                        echo match($ticket['status']) {
                                            'open' => 'bg-blue-100 text-blue-800',
                                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                                            'resolved' => 'bg-green-100 text-green-800',
                                            'closed' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                    </span>
                                </td>
                                <td class="text-sm text-gray-400">
                                    <?php echo date('M j, Y', strtotime($ticket['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <button onclick="viewTicket('<?php echo $ticket_id; ?>')" class="btn btn-primary text-sm">
                                            <i class="fas fa-eye mr-1"></i><?php echo __('view'); ?>
                                        </button>
                                        <button onclick="updateTicket('<?php echo $ticket_id; ?>')" class="btn btn-secondary text-sm">
                                            <i class="fas fa-edit mr-1"></i><?php echo __('update'); ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-ticket-alt text-4xl mb-4"></i>
                <p><?php echo __('no_tickets_found'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Ticket View Modal -->
<div id="ticketModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold"><?php echo __('ticket_details'); ?></h3>
                    <button onclick="closeTicketModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="ticketContent">
                    <!-- Ticket content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Ticket Modal -->
<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold"><?php echo __('update_ticket'); ?></h3>
                    <button onclick="closeUpdateModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="updateForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="ticket_id" id="updateTicketId">
                    
                    <div class="mb-4">
                        <label class="form-label"><?php echo __('status'); ?>:</label>
                        <select name="status" id="updateStatus" class="form-input" required>
                            <option value="open"><?php echo __('open'); ?></option>
                            <option value="in_progress"><?php echo __('in_progress'); ?></option>
                            <option value="resolved"><?php echo __('resolved'); ?></option>
                            <option value="closed"><?php echo __('closed'); ?></option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label"><?php echo __('admin_note'); ?>:</label>
                        <textarea name="admin_note" id="updateNote" class="form-input" rows="4" placeholder="<?php echo __('add_note_optional'); ?>"></textarea>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary"><?php echo __('update_ticket'); ?></button>
                        <button type="button" onclick="closeUpdateModal()" class="btn btn-secondary"><?php echo __('cancel'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const tickets = <?php echo json_encode($tickets); ?>;

function viewTicket(ticketId) {
    const ticket = tickets[ticketId];
    if (!ticket) return;
    
    const content = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="font-semibold"><?php echo __('ticket_id'); ?>:</label>
                    <p class="font-mono">#${ticketId.substring(0, 8)}</p>
                </div>
                <div>
                    <label class="font-semibold"><?php echo __('status'); ?>:</label>
                    <p>${ticket.status}</p>
                </div>
                <div>
                    <label class="font-semibold"><?php echo __('priority'); ?>:</label>
                    <p>${ticket.priority}</p>
                </div>
                <div>
                    <label class="font-semibold"><?php echo __('created'); ?>:</label>
                    <p>${ticket.created_at}</p>
                </div>
            </div>
            
            <div>
                <label class="font-semibold"><?php echo __('customer'); ?>:</label>
                <p>${ticket.name} (${ticket.email})</p>
            </div>
            
            <div>
                <label class="font-semibold"><?php echo __('subject'); ?>:</label>
                <p>${ticket.subject}</p>
            </div>
            
            <div>
                <label class="font-semibold"><?php echo __('message'); ?>:</label>
                <div class="bg-gray-100 p-4 rounded">${ticket.message.replace(/\n/g, '<br>')}</div>
            </div>
            
            ${ticket.admin_notes && ticket.admin_notes.length > 0 ? `
                <div>
                    <label class="font-semibold"><?php echo __('admin_notes'); ?>:</label>
                    <div class="space-y-2">
                        ${ticket.admin_notes.map(note => `
                            <div class="bg-blue-50 p-3 rounded">
                                <div class="text-sm text-gray-600">${note.admin} - ${note.date}</div>
                                <div>${note.note}</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('ticketContent').innerHTML = content;
    document.getElementById('ticketModal').classList.remove('hidden');
}

function updateTicket(ticketId) {
    const ticket = tickets[ticketId];
    if (!ticket) return;
    
    document.getElementById('updateTicketId').value = ticketId;
    document.getElementById('updateStatus').value = ticket.status;
    document.getElementById('updateNote').value = '';
    document.getElementById('updateModal').classList.remove('hidden');
}

function closeTicketModal() {
    document.getElementById('ticketModal').classList.add('hidden');
}

function closeUpdateModal() {
    document.getElementById('updateModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('ticketModal').addEventListener('click', function(e) {
    if (e.target === this) closeTicketModal();
});

document.getElementById('updateModal').addEventListener('click', function(e) {
    if (e.target === this) closeUpdateModal();
});
</script>

<?php admin_layout_end(); ?>
