<?php
/**
 * StockVerse - Register Page
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/user/dashboard.php');
    exit;
}

$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrf)) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        if (empty($name)) $errors[] = 'Name is required.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            $db = getDB();
            
            // Check if email exists
            $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with this email already exists.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, $email, $hashedPassword, 'user']);

                // Auto-login
                $userId = $db->lastInsertId();
                loginUser([
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'user'
                ]);

                header('Location: ' . BASE_URL . '/user/dashboard.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/style.css">
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/components.css">
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/pages.css">
    <script>(function(){var t=localStorage.getItem('theme');if(t)document.documentElement.setAttribute('data-theme',t)})()</script>
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <div class="brand-icon">📈</div>
            <h2>Create Account</h2>
            <p>Start your stock market learning journey</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?= e($errors[0]) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrfField() ?>
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" 
                       placeholder="John Doe" value="<?= e($name) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="you@example.com" value="<?= e($email) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Min 6 characters" required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                       placeholder="Repeat your password" required>
            </div>

            <button type="submit" class="btn btn-primary w-full btn-lg">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="<?= BASE_URL ?>/auth/login.php">Sign in</a>
        </div>
    </div>
</div>
<script src="<?= ASSETS_PATH ?>/js/app.js"></script>
</body>
</html>
