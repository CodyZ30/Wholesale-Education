<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

$error = '';

// Hardcoded test credentials
$test_username = 'testadmin';
$test_password_hash = password_hash('testpassword', PASSWORD_BCRYPT); // Hash for 'testpassword'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $test_username && password_verify($password, $test_password_hash)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $test_username;
        header('Location: dashboard.php'); // Redirect to admin dashboard
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 28rem;
        }
        .input-field {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        .input-field:focus {
            outline: none;
            border-color: #00a651;
            box-shadow: 0 0 0 3px rgba(0, 166, 81, 0.2);
        }
        .submit-button {
            width: 100%;
            padding: 0.75rem 1rem;
            background-color: #00a651;
            color: #fff;
            border-radius: 0.375rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .submit-button:hover {
            background-color: #008f46;
        }
        .error-message {
            color: #ef4444;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-2xl font-bold text-center mb-6">Test Admin Login</h2>
        <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" class="input-field" required>
            <input type="password" name="password" placeholder="Password" class="input-field" required>
            <button type="submit" class="submit-button">Login</button>
        </form>
        <p class="text-sm text-gray-600 text-center mt-4">Use username: <code>testadmin</code> and password: <code>testpassword</code></p>
    </div>
</body>
</html>
