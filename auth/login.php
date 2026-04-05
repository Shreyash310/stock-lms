<?php
/**
 * StockVerse - Login Page
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/user/dashboard.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrf)) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        if (empty($email) || empty($password)) {
            $errors[] = 'Please fill in all fields.';
        } else {
            $db = getDB();
            $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                loginUser($user);
                $redirect = isAdmin() ? BASE_URL . '/admin/dashboard.php' : BASE_URL . '/user/dashboard.php';
                header('Location: ' . $redirect);
                exit;
            } else {
                $errors[] = 'Invalid email or password.';
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
    <title>Sign In — <?= APP_NAME ?></title>
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
            <h2>Welcome Back</h2>
            <p>Sign in to continue learning</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?= e($errors[0]) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrfField() ?>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="you@example.com" value="<?= e($email) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary w-full btn-lg">Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="<?= BASE_URL ?>/auth/register.php">Create one</a>
        </div>
    </div>
</div>
<script src="<?= ASSETS_PATH ?>/js/app.js"></script>
</body>
</html>
