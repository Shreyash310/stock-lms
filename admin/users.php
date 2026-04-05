<?php
/**
 * StockVerse - Admin Users & Progress View
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/auth.php';
requireAdmin();

$db = getDB();

// View individual user?
$viewUser = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($viewUser > 0) {
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$viewUser]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: ' . BASE_URL . '/admin/users.php');
        exit;
    }

    // User's module progress
    $modules = $db->query('SELECT m.*, COUNT(c.id) as chapter_count FROM modules m LEFT JOIN chapters c ON c.module_id = m.id GROUP BY m.id ORDER BY m.id')->fetchAll();
    foreach ($modules as &$mod) {
        $stmt = $db->prepare('SELECT COUNT(*) FROM progress p JOIN chapters c ON c.id = p.chapter_id WHERE p.user_id = ? AND c.module_id = ? AND p.status = ?');
        $stmt->execute([$viewUser, $mod['id'], 'completed']);
        $mod['completed'] = $stmt->fetchColumn();
        $mod['progress'] = $mod['chapter_count'] > 0 ? round(($mod['completed'] / $mod['chapter_count']) * 100) : 0;
    }
    unset($mod);

    // Quiz results
    $stmt = $db->prepare('SELECT r.*, c.title as chapter_title FROM results r JOIN chapters c ON c.id = r.chapter_id WHERE r.user_id = ? ORDER BY r.attempted_at DESC');
    $stmt->execute([$viewUser]);
    $quizResults = $stmt->fetchAll();

    $pageTitle = 'User: ' . $user['name'];
    $currentPage = 'admin-users';
    require_once INCLUDES_PATH . '/header.php';
    ?>

    <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-secondary btn-sm mb-24">← Back to Users</a>

    <div class="card mb-24 animate-slide-up">
        <div class="d-flex align-center gap-16">
            <div style="width:56px;height:56px;border-radius:var(--radius-full);background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:#fff">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div>
                <h2 style="font-size:1.3rem;font-weight:700"><?= e($user['name']) ?></h2>
                <p style="color:var(--text-secondary);font-size:0.9rem"><?= e($user['email']) ?> • Joined <?= date('M d, Y', strtotime($user['created_at'])) ?></p>
            </div>
        </div>
    </div>

    <div class="section-header">
        <h2>Module Progress</h2>
    </div>

    <?php foreach ($modules as $mod): ?>
    <div class="card mb-16 stagger-item">
        <div class="d-flex align-center justify-between mb-8">
            <strong><?= e($mod['title']) ?></strong>
            <span class="badge <?= $mod['progress'] >= 100 ? 'badge-success' : 'badge-primary' ?>"><?= $mod['progress'] ?>%</span>
        </div>
        <div class="progress-bar progress-bar-lg">
            <div class="progress-fill" style="width:<?= $mod['progress'] ?>%"></div>
        </div>
        <p style="font-size:0.8rem;color:var(--text-tertiary);margin-top:6px"><?= $mod['completed'] ?>/<?= $mod['chapter_count'] ?> chapters completed</p>
    </div>
    <?php endforeach; ?>

    <?php if (!empty($quizResults)): ?>
    <div class="section-header mt-24">
        <h2>Quiz Results</h2>
    </div>
    <div class="table-container animate-slide-up">
        <table class="data-table">
            <thead><tr><th>Chapter</th><th>Score</th><th>Date</th></tr></thead>
            <tbody>
                <?php foreach ($quizResults as $r):
                    $pct = $r['total'] > 0 ? round(($r['score']/$r['total'])*100) : 0;
                ?>
                <tr>
                    <td><?= e($r['chapter_title']) ?></td>
                    <td><span class="badge <?= $pct >= 70 ? 'badge-success' : ($pct >= 40 ? 'badge-warning' : 'badge-danger') ?>"><?= $r['score'] ?>/<?= $r['total'] ?> (<?= $pct ?>%)</span></td>
                    <td style="color:var(--text-tertiary)"><?= date('M d, Y H:i', strtotime($r['attempted_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

<?php
} else {
    // List all users
    $users = $db->query('SELECT u.*, (SELECT COUNT(*) FROM progress p WHERE p.user_id = u.id AND p.status = "completed") as completed_chapters, (SELECT ROUND(AVG(r.score * 100.0 / r.total)) FROM results r WHERE r.user_id = u.id AND r.total > 0) as avg_score FROM users ORDER BY u.created_at DESC')->fetchAll();

    $pageTitle = 'Users';
    $currentPage = 'admin-users';
    require_once INCLUDES_PATH . '/header.php';
    ?>

    <div class="section-header">
        <h2>All Users (<?= count($users) ?>)</h2>
    </div>

    <div class="table-container animate-slide-up">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Chapters</th><th>Avg Score</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><strong><?= e($u['name']) ?></strong></td>
                    <td style="color:var(--text-secondary)"><?= e($u['email']) ?></td>
                    <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-primary' : 'badge-success' ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= $u['completed_chapters'] ?> completed</td>
                    <td><?= $u['avg_score'] !== null ? $u['avg_score'] . '%' : '—' ?></td>
                    <td style="color:var(--text-tertiary)"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td><a href="<?= BASE_URL ?>/admin/users.php?user_id=<?= $u['id'] ?>" class="btn btn-secondary btn-sm">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php
}
require_once INCLUDES_PATH . '/footer.php';
?>
