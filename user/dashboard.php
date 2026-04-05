<?php
/**
 * StockVerse - User Dashboard
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];

// Total modules
$totalModules = $db->query('SELECT COUNT(*) FROM modules')->fetchColumn();

// Total chapters
$totalChapters = $db->query('SELECT COUNT(*) FROM chapters')->fetchColumn();

// Completed chapters by user
$stmt = $db->prepare('SELECT COUNT(*) FROM progress WHERE user_id = ? AND status = ?');
$stmt->execute([$userId, 'completed']);
$completedChapters = $stmt->fetchColumn();

// Average quiz score
$stmt = $db->prepare('SELECT AVG(score * 100.0 / total) FROM results WHERE user_id = ? AND total > 0');
$stmt->execute([$userId]);
$avgScore = round($stmt->fetchColumn() ?? 0);

// Module progress
$modules = $db->query('SELECT m.*, COUNT(c.id) as chapter_count FROM modules m LEFT JOIN chapters c ON c.module_id = m.id GROUP BY m.id ORDER BY m.id')->fetchAll();

foreach ($modules as &$mod) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM progress p JOIN chapters c ON c.id = p.chapter_id WHERE p.user_id = ? AND c.module_id = ? AND p.status = ?');
    $stmt->execute([$userId, $mod['id'], 'completed']);
    $mod['completed'] = $stmt->fetchColumn();
    $mod['progress'] = $mod['chapter_count'] > 0 ? round(($mod['completed'] / $mod['chapter_count']) * 100) : 0;
}
unset($mod);

// Last accessed chapter (most recent progress entry)
$stmt = $db->prepare('SELECT c.id, c.title, c.module_id, m.title as module_title FROM progress p JOIN chapters c ON c.id = p.chapter_id JOIN modules m ON m.id = c.module_id WHERE p.user_id = ? ORDER BY p.id DESC LIMIT 1');
$stmt->execute([$userId]);
$lastChapter = $stmt->fetch();

// Recent quiz results
$stmt = $db->prepare('SELECT r.*, c.title as chapter_title FROM results r JOIN chapters c ON c.id = r.chapter_id WHERE r.user_id = ? ORDER BY r.attempted_at DESC LIMIT 5');
$stmt->execute([$userId]);
$recentQuizzes = $stmt->fetchAll();

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require_once INCLUDES_PATH . '/header.php';
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card stagger-item">
        <div class="stat-icon purple">📚</div>
        <div class="stat-info">
            <h3>Total Modules</h3>
            <div class="stat-value"><?= $totalModules ?></div>
            <div class="stat-desc">Available to learn</div>
        </div>
    </div>
    <div class="stat-card stagger-item">
        <div class="stat-icon cyan">✅</div>
        <div class="stat-info">
            <h3>Chapters Completed</h3>
            <div class="stat-value"><?= $completedChapters ?>/<?= $totalChapters ?></div>
            <div class="stat-desc"><?= $totalChapters > 0 ? round(($completedChapters/$totalChapters)*100) : 0 ?>% complete</div>
        </div>
    </div>
    <div class="stat-card stagger-item">
        <div class="stat-icon green">🎯</div>
        <div class="stat-info">
            <h3>Avg Quiz Score</h3>
            <div class="stat-value"><?= $avgScore ?>%</div>
            <div class="stat-desc">Across all quizzes</div>
        </div>
    </div>
    <div class="stat-card stagger-item">
        <div class="stat-icon amber">🔥</div>
        <div class="stat-info">
            <h3>Quizzes Taken</h3>
            <div class="stat-value"><?= count($recentQuizzes) ?></div>
            <div class="stat-desc">Keep it going!</div>
        </div>
    </div>
</div>

<!-- Continue Learning -->
<?php if ($lastChapter): ?>
<div class="card mb-32 animate-slide-up">
    <div class="card-header">
        <h3>📖 Continue Learning</h3>
    </div>
    <p style="color:var(--text-secondary);margin-bottom:16px">
        You were reading <strong><?= e($lastChapter['title']) ?></strong> 
        in <em><?= e($lastChapter['module_title']) ?></em>
    </p>
    <a href="<?= BASE_URL ?>/user/chapter.php?id=<?= $lastChapter['id'] ?>" class="btn btn-primary">
        Continue Reading →
    </a>
</div>
<?php endif; ?>

<!-- Module Progress -->
<div class="section-header">
    <h2>Your Progress</h2>
    <a href="<?= BASE_URL ?>/user/modules.php" class="section-action">View All Modules →</a>
</div>

<div class="modules-grid mb-32">
    <?php foreach ($modules as $mod): ?>
    <a href="<?= BASE_URL ?>/user/modules.php?module_id=<?= $mod['id'] ?>" 
       class="module-card stagger-item" style="text-decoration:none">
        <div class="module-icon"><?= $mod['icon'] ?? '📊' ?></div>
        <div class="module-title"><?= e($mod['title']) ?></div>
        <div class="module-desc"><?= e(substr($mod['description'] ?? '', 0, 120)) ?>...</div>
        <div class="module-meta">
            <span class="chapter-count"><?= $mod['chapter_count'] ?> chapters</span>
            <div class="module-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width:<?= $mod['progress'] ?>%"></div>
                </div>
                <span><?= $mod['progress'] ?>%</span>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<!-- Recent Quiz Results -->
<?php if (!empty($recentQuizzes)): ?>
<div class="section-header">
    <h2>Recent Quiz Results</h2>
</div>
<div class="table-container animate-slide-up">
    <table class="data-table">
        <thead>
            <tr>
                <th>Chapter</th>
                <th>Score</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentQuizzes as $quiz): 
                $pct = $quiz['total'] > 0 ? round(($quiz['score'] / $quiz['total']) * 100) : 0;
            ?>
            <tr>
                <td><?= e($quiz['chapter_title']) ?></td>
                <td>
                    <span class="badge <?= $pct >= 70 ? 'badge-success' : ($pct >= 40 ? 'badge-warning' : 'badge-danger') ?>">
                        <?= $quiz['score'] ?>/<?= $quiz['total'] ?> (<?= $pct ?>%)
                    </span>
                </td>
                <td style="color:var(--text-tertiary)"><?= date('M d, Y', strtotime($quiz['attempted_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
