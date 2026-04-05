<?php
/**
 * StockVerse - Admin Dashboard
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/auth.php';
requireAdmin();

$db = getDB();

$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalModules = $db->query('SELECT COUNT(*) FROM modules')->fetchColumn();
$totalChapters = $db->query('SELECT COUNT(*) FROM chapters')->fetchColumn();
$totalQuizzes = $db->query('SELECT COUNT(*) FROM quizzes')->fetchColumn();

// Recent users
$recentUsers = $db->query('SELECT * FROM users ORDER BY created_at DESC LIMIT 5')->fetchAll();

// Completion stats
$totalProgress = $db->query("SELECT COUNT(*) FROM progress WHERE status = 'completed'")->fetchColumn();

$pageTitle = 'Admin Dashboard';
$currentPage = 'admin-dashboard';
require_once INCLUDES_PATH . '/header.php';
?>

<div class="stats-grid">
    <div class="stat-card stagger-item">
        <div class="stat-icon purple">👥</div>
        <div class="stat-info">
            <h3>Total Users</h3>
            <div class="stat-value"><?= $totalUsers ?></div>
        </div>
    </div>
    <div class="stat-card stagger-item">
        <div class="stat-icon cyan">📚</div>
        <div class="stat-info">
            <h3>Modules</h3>
            <div class="stat-value"><?= $totalModules ?></div>
        </div>
    </div>
    <div class="stat-card stagger-item">
        <div class="stat-icon green">📄</div>
        <div class="stat-info">
            <h3>Chapters</h3>
            <div class="stat-value"><?= $totalChapters ?></div>
        </div>
    </div>
    <div class="stat-card stagger-item">
        <div class="stat-icon amber">❓</div>
        <div class="stat-info">
            <h3>Quiz Questions</h3>
            <div class="stat-value"><?= $totalQuizzes ?></div>
        </div>
    </div>
</div>

<div class="section-header">
    <h2>Recent Users</h2>
    <a href="<?= BASE_URL ?>/admin/users.php" class="section-action">View All →</a>
</div>
<div class="table-container animate-slide-up">
    <table class="data-table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
        <tbody>
            <?php foreach ($recentUsers as $u): ?>
            <tr>
                <td><strong><?= e($u['name']) ?></strong></td>
                <td style="color:var(--text-secondary)"><?= e($u['email']) ?></td>
                <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-primary' : 'badge-success' ?>"><?= e(ucfirst($u['role'])) ?></span></td>
                <td style="color:var(--text-tertiary)"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
