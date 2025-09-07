<?php
session_start();
include_once __DIR__ . '/../includes/config.php';
include_once __DIR__ . '/translation.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Check if the admin is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$error = '';
$success_message = '';

// Validate CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
        exit('Invalid CSRF token.');
    }
}

// Path to the users JSON file
$usersFile = __DIR__ . '/../users.json';

// Handle deleting a user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $user_id_to_delete = (int)($_POST['id'] ?? 0);
    if ($user_id_to_delete <= 0) {
        $error = 'Invalid user ID for deletion.';
    } else {
        $users_file_path = $usersFile;
        $current_users = [];
        if (file_exists($users_file_path)) {
            $json = file_get_contents($users_file_path);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) $current_users = $decoded;
        }

        $found_and_deleted = false;
        foreach ($current_users as $key => $user) {
            if (($user['id'] ?? 0) === $user_id_to_delete) {
                unset($current_users[$key]);
                $found_and_deleted = true;
                break;
            }
        }

        if ($found_and_deleted) {
            // Re-index the array to maintain sequential IDs
            $current_users = array_values($current_users);

            // Write the updated users back to the JSON file
            file_put_contents($usersFile, json_encode($current_users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $_SESSION['success_message'] = 'User deleted successfully!';
        } else {
            $error = 'User not found.';
        }

        header('Location: users.php');
        exit;
    }
}

// Handle adding a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $users_file_path = $usersFile;
    $current_users = [];
    if (file_exists($users_file_path)) {
        $json = file_get_contents($users_file_path);
        $decoded = json_decode($json, true);
        if (is_array($decoded)) $current_users = $decoded;
    }

    $new_username = trim($_POST['username'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $new_role = trim($_POST['role'] ?? 'editor');

    // Basic validation
    if (empty($new_username) || empty($new_email) || empty($new_password) || empty($new_role)) {
        $error = __('all_fields_required');
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = __('invalid_email_format');
    } else {
        $username_exists = false;
        foreach ($current_users as $user) {
            if (($user['username'] ?? '') === $new_username) {
                $username_exists = true;
                break;
            }
        }

        if ($username_exists) {
            $error = __('username_already_exists');
        } else {
            $email_exists = false;
            foreach ($current_users as $user) {
                if (($user['email'] ?? '') === $new_email) {
                    $email_exists = true;
                    break;
                }
            }

            if ($email_exists) {
                $error = __('email_already_in_use');
            } else {
                $new_user_id = 1;
                if (!empty($current_users)) {
                    $ids = array_column($current_users, 'id');
                    $new_user_id = max($ids) + 1;
                }

                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Handle profile picture upload
                $profile_picture = '';
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/../uploads/profiles/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0775, true);
                    }
                    
                    $file_info = pathinfo($_FILES['profile_picture']['name']);
                    $extension = strtolower($file_info['extension']);
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($extension, $allowed_extensions) && $_FILES['profile_picture']['size'] <= 2 * 1024 * 1024) {
                        $filename = 'user_' . $new_user_id . '_' . time() . '.' . $extension;
                        $upload_path = $upload_dir . $filename;
                        
                        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                            $profile_picture = $filename;
                        }
                    }
                }

                $new_user = [
                    'id' => $new_user_id,
                    'username' => $new_username,
                    'email' => $new_email,
                    'password' => $hashed_password,
                    'role' => $new_role,
                    'profile_picture' => $profile_picture,
                ];

                $current_users[] = $new_user;

                // Write the updated users back to the JSON file
                file_put_contents($usersFile, json_encode($current_users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                $_SESSION['success_message'] = __('user_added_successfully');
                header('Location: users.php');
                exit;
            }
        }
    }
}

// Handle updating a user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $user_id_to_update = (int)($_POST['id'] ?? 0);
    $updated_username = trim($_POST['username'] ?? '');
    $updated_email = trim($_POST['email'] ?? '');
    $updated_password = $_POST['password'] ?? ''; // New password, if provided
    $updated_role = trim($_POST['role'] ?? '');

    if ($user_id_to_update <= 0 || empty($updated_username) || empty($updated_email) || empty($updated_role)) {
        $error = 'All fields are required and user ID must be valid.';
    } elseif (!filter_var($updated_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        $users_file_path = $usersFile;
        $current_users = [];
        if (file_exists($users_file_path)) {
            $json = file_get_contents($users_file_path);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) $current_users = $decoded;
        }

        $target_user_key = null;
        foreach ($current_users as $key => $user) {
            if (($user['id'] ?? 0) === $user_id_to_update) {
                $target_user_key = $key;
                break;
            }
        }

        if ($target_user_key === null) {
            $error = 'User not found for update.';
        } else {
            // Check for unique username (excluding current user)
            $username_exists = false;
            foreach ($current_users as $key => $user) {
                if (($user['username'] ?? '') === $updated_username && $key !== $target_user_key) {
                    $username_exists = true;
                    break;
                }
            }

            if ($username_exists) {
                $error = 'Username already exists.';
            } else {
                // Check for unique email (excluding current user)
                $email_exists = false;
                foreach ($current_users as $key => $user) {
                    if (($user['email'] ?? '') === $updated_email && $key !== $target_user_key) {
                        $email_exists = true;
                        break;
                    }
                }

                if ($email_exists) {
                    $error = 'Email already in use.';
                } else {
                    // Update user details
                    $current_users[$target_user_key]['username'] = $updated_username;
                    $current_users[$target_user_key]['email'] = $updated_email;
                    $current_users[$target_user_key]['role'] = $updated_role;

                    // Update password if a new one is provided
                    if ($updated_password !== '') {
                        $current_users[$target_user_key]['password'] = password_hash($updated_password, PASSWORD_DEFAULT);
                    }

                    // Handle profile picture upload
                    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = __DIR__ . '/../uploads/profiles/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0775, true);
                        }
                        
                        $file_info = pathinfo($_FILES['profile_picture']['name']);
                        $extension = strtolower($file_info['extension']);
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($extension, $allowed_extensions) && $_FILES['profile_picture']['size'] <= 2 * 1024 * 1024) {
                            // Delete old profile picture if it exists
                            $old_picture = $current_users[$target_user_key]['profile_picture'] ?? '';
                            if (!empty($old_picture) && file_exists($upload_dir . $old_picture)) {
                                unlink($upload_dir . $old_picture);
                            }
                            
                            $filename = 'user_' . $user_id_to_update . '_' . time() . '.' . $extension;
                            $upload_path = $upload_dir . $filename;
                            
                            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                                $current_users[$target_user_key]['profile_picture'] = $filename;
                            }
                        }
                    }

                    // Write the updated users back to the JSON file
                    file_put_contents($usersFile, json_encode($current_users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                    $_SESSION['success_message'] = 'User updated successfully!';
                    header('Location: users.php');
                    exit;
                }
            }
        }
    }
}

// Load users
$users = [];
if (file_exists($usersFile)) {
    $json_content = file_get_contents($usersFile);
    $users = json_decode($json_content, true);
    if (!is_array($users)) {
        $users = [];
    }
}

// Handle messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="admin_styles.css">
    <script src="/admin/ui.js" defer></script>
</head>
<body>
    <div class="sidebar">
      <div class="sidebar-header">
        <img src="../images/white-logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="sidebar-logo mx-auto">
        <div class="text-sm text-gray-400 mt-2">Employee Portal</div>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
          <li><a href="guides.php"><i class="fas fa-book"></i> Guides</a></li>
          <li><a href="reviews.php"><i class="fas fa-comments"></i> Reviews</a></li>
          <li><a href="sales.php"><i class="fas fa-chart-line"></i> Sales</a></li>
          <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
          <li><a href="messages.php"><i class="fas fa-inbox"></i> Messages</a></li>
          <li><a href="settings.php"><i class="fas fa-gear"></i> Settings</a></li>
          <li><a class="active" href="users.php"><i class="fas fa-users"></i> Users</a></li>
          <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
      </nav>
    </div>

    <div class="main-content">
      <div class="top-navbar">
        <div class="flex items-center gap-2">
          <img src="../images/white-logo.png" alt="<?php echo SITE_NAME; ?>" style="height:28px;width:auto;border-radius:6px;">
          <span class="text-xl font-semibold">Users</span>
        </div>
      </div>

      <div class="dashboard-grid">
        <div class="dashboard-card md:col-span-3">
          <h2 class="text-lg font-medium mb-4">Manage Users</h2>
          <a href="users.php?action=add" class="btn btn-primary mb-4 inline-block"><?php echo __('add_new_user'); ?></a>
          <?php if ($error): ?>
            <div class="mb-4" style="color:#ef4444; font-weight:600;"><?php echo $error; ?></div>
          <?php endif; ?>
          <?php if ($success_message): ?>
            <div class="mb-4" style="color:#1dd171; font-weight:600;">&nbsp;<?php echo $success_message; ?></div>
          <?php endif; ?>
        

        <?php if (!isset($_GET['action'])): ?>
        <div class="dashboard-card md:col-span-3">
            <h2 class="text-xl font-semibold mb-4"><?php echo __('existing_users'); ?></h2>
            <div class="rounded-lg border overflow-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr>
                    <th class="text-left p-2">ID</th>
                    <th class="text-left p-2"><?php echo __('avatar'); ?></th>
                    <th class="text-left p-2"><?php echo __('username'); ?></th>
                    <th class="text-left p-2"><?php echo __('email'); ?></th>
                    <th class="text-left p-2"><?php echo __('role'); ?></th>
                    <th class="text-left p-2"><?php echo __('actions'); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($users)): ?>
                    <tr><td colspan="6" class="p-2 text-gray-400"><?php echo __('no_users_yet'); ?></td></tr>
                  <?php else: foreach ($users as $u): ?>
                    <tr class="border-t">
                      <td class="p-2"><?php echo htmlspecialchars((string)($u['id'] ?? '')); ?></td>
                      <td class="p-2">
                        <?php if (!empty($u['profile_picture'])): ?>
                          <img src="../uploads/profiles/<?php echo htmlspecialchars($u['profile_picture']); ?>" alt="Profile Picture" class="w-8 h-8 rounded-full object-cover border border-gray-300">
                        <?php else: ?>
                          <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-xs font-semibold">
                            <?php echo strtoupper(substr($u['username'] ?? 'U', 0, 1)); ?>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td class="p-2"><?php echo htmlspecialchars((string)($u['username'] ?? '')); ?></td>
                      <td class="p-2"><?php echo htmlspecialchars((string)($u['email'] ?? '')); ?></td>
                      <td class="p-2"><?php echo htmlspecialchars((string)($u['role'] ?? '')); ?></td>
                      <td class="p-2">
                        <a class="btn btn-primary" href="users.php?action=edit&id=<?php echo htmlspecialchars((string)($u['id'] ?? '')); ?>"><?php echo __('edit'); ?></a>
                        <form method="POST" action="users.php" style="display:inline-block;" onsubmit="return confirm('<?php echo __('delete_this_user'); ?>');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)($u['id'] ?? '')); ?>">
                          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$csrf_token); ?>">
                          <button type="submit" class="btn ml-2"><?php echo __('delete'); ?></button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
        <div class="dashboard-card md:col-span-3">
            <h2 class="text-xl font-semibold mb-4"><?php echo __('add_new_user'); ?></h2>
            <form method="POST" action="users.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$csrf_token); ?>">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-semibold mb-2"><?php echo __('username'); ?>:</label>
                    <input type="text" name="username" id="username" class="border rounded w-full px-3 py-2 bg-transparent" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-semibold mb-2"><?php echo __('email'); ?>:</label>
                    <input type="email" name="email" id="email" class="border rounded w-full px-3 py-2 bg-transparent" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-semibold mb-2"><?php echo __('password'); ?>:</label>
                    <input type="password" name="password" id="password" class="border rounded w-full px-3 py-2 bg-transparent" required>
                </div>
                <div class="mb-4">
                    <label for="role" class="block text-sm font-semibold mb-2"><?php echo __('role'); ?>:</label>
                    <select name="role" id="role" class="border rounded w-full px-3 py-2 bg-transparent">
                        <option value="administrator"><?php echo __('administrator'); ?></option>
                        <option value="editor"><?php echo __('editor'); ?></option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="profile_picture" class="block text-sm font-semibold mb-2"><?php echo __('profile_picture'); ?>:</label>
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="border rounded w-full px-3 py-2 bg-transparent">
                    <p class="text-xs text-gray-400 mt-1"><?php echo __('upload_profile_picture'); ?></p>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo __('add_user'); ?></button>
                <a href="users.php" class="btn ml-2"><?php echo __('cancel'); ?></a>
            </form>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])): ?>
        <?php
            $edit_user_id = (int)$_GET['id'];
            $user_to_edit = null;
            foreach ($users as $user) {
                if (($user['id'] ?? 0) === $edit_user_id) {
                    $user_to_edit = $user;
                    break;
                }
            }
            if (!$user_to_edit) {
                header('Location: users.php');
                exit;
            }
        ?>
        <div class="dashboard-card md:col-span-3">
            <h2 class="text-xl font-semibold mb-4">Edit User: <?php echo htmlspecialchars((string)$user_to_edit['username']); ?></h2>
            <form method="POST" action="users.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$user_to_edit['id']); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$csrf_token); ?>">
                <div class="mb-4">
                    <label for="edit_username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                    <input type="text" name="username" id="edit_username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars((string)$user_to_edit['username']); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="edit_email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                    <input type="email" name="email" id="edit_email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars((string)$user_to_edit['email']); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="edit_password" class="block text-gray-700 text-sm font-bold mb-2">New Password (leave blank to keep current):</label>
                    <input type="password" name="password" id="edit_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label for="edit_role" class="block text-gray-700 text-sm font-bold mb-2">Role:</label>
                    <select name="role" id="edit_role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="administrator" <?php echo (($user_to_edit['role'] ?? '') === 'administrator') ? 'selected' : ''; ?>>Administrator</option>
                        <option value="editor" <?php echo (($user_to_edit['role'] ?? '') === 'editor') ? 'selected' : ''; ?>>Editor</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="edit_profile_picture" class="block text-gray-700 text-sm font-bold mb-2">Profile Picture:</label>
                    <?php if (!empty($user_to_edit['profile_picture'])): ?>
                        <div class="mb-2">
                            <img src="../uploads/profiles/<?php echo htmlspecialchars($user_to_edit['profile_picture']); ?>" alt="Current Profile Picture" class="w-16 h-16 rounded-full object-cover border-2 border-gray-300">
                            <p class="text-xs text-gray-500 mt-1">Current picture</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="profile_picture" id="edit_profile_picture" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-xs text-gray-500 mt-1">Upload a new profile picture (JPG, PNG, GIF - max 2MB)</p>
                </div>
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="users.php" class="btn ml-2">Cancel</a>
            </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
</body>
</html>
