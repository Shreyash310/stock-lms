<?php
/**
 * StockVerse - Modules Listing & Chapter Listing
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];
$moduleId = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

if ($moduleId > 0) {
    // Show chapters for this module
    $stmt = $db->prepare('SELECT * FROM modules WHERE id = ?');
    $stmt->execute([$moduleId]);
    $module = $stmt->fetch();

    if (!$module) {
        header('Location: ' . BASE_URL . '/user/modules.php');
        exit;
    }

    $stmt = $db->prepare('SELECT c.*, IFNULL(p.status, "not_started") as progress_status FROM chapters c LEFT JOIN progress p ON p.chapter_id = c.id AND p.user_id = ? WHERE c.module_id = ? ORDER BY c.order_index ASC');
    $stmt->execute([$userId, $moduleId]);
    $chapters = $stmt->fetchAll();

    // Check quiz availability per chapter
    foreach ($chapters as &$ch) {
        $stmt2 = $db->prepare('SELECT COUNT(*) FROM quizzes WHERE chapter_id = ?');
        $stmt2->execute([$ch['id']]);
        $ch['has_quiz'] = $stmt2->fetchColumn() > 0;
    }
    unset($ch);

    $pageTitle = $module['title'];
    $currentPage = 'modules';
    require_once INCLUDES_PATH . '/header.php';
    ?>

    <a href="<?= BASE_URL ?>/user/modules.php" class="btn btn-secondary btn-sm mb-24">← Back to Modules</a>

    <div class="card mb-24 animate-slide-up">
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:12px">
            <span style="font-size:2.5rem"><?= $module['icon'] ?? '📊' ?></span>
            <div>
                <h2 style="font-size:1.4rem;font-weight:700"><?= e($module['title']) ?></h2>
                <p style="color:var(--text-secondary);font-size:0.9rem"><?= e($module['description'] ?? '') ?></p>
            </div>
        </div>
        <div class="progress-bar progress-bar-lg">
            <?php
            $completedCount = count(array_filter($chapters, fn($c) => $c['progress_status'] === 'completed'));
            $totalCount = count($chapters);
            $pct = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;
            ?>
            <div class="progress-fill" style="width:<?= $pct ?>%"></div>
        </div>
        <p style="font-size:0.85rem;color:var(--text-tertiary);margin-top:8px"><?= $completedCount ?>/<?= $totalCount ?> chapters completed (<?= $pct ?>%)</p>
    </div>

    <div class="section-header">
        <h2>Chapters</h2>
    </div>

    <?php if (empty($chapters)): ?>
        <div class="empty-state">
            <div class="empty-icon">📄</div>
            <h3>No Chapters Yet</h3>
            <p>Chapters for this module haven't been added yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($chapters as $index => $ch): ?>
        <a href="<?= BASE_URL ?>/user/chapter.php?id=<?= $ch['id'] ?>" 
           class="card mb-16 stagger-item d-flex align-center justify-between gap-16" 
           style="text-decoration:none;padding:20px 24px">
            <div class="d-flex align-center gap-16">
                <div style="width:36px;height:36px;border-radius:var(--radius-md);background:var(--bg-tertiary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;color:var(--text-tertiary);flex-shrink:0">
                    <?= $index + 1 ?>
                </div>
                <div>
                    <div style="font-weight:600;color:var(--text-primary);margin-bottom:2px"><?= e($ch['title']) ?></div>
                    <div class="d-flex align-center gap-8">
                        <?php if ($ch['progress_status'] === 'completed'): ?>
                            <span class="badge badge-success">✓ Completed</span>
                        <?php elseif ($ch['progress_status'] === 'in_progress'): ?>
                            <span class="badge badge-warning">In Progress</span>
                        <?php else: ?>
                            <span class="badge badge-primary">Not Started</span>
                        <?php endif; ?>
                        <?php if ($ch['has_quiz']): ?>
                            <span class="badge badge-primary">Quiz</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <span style="color:var(--text-tertiary);font-size:1.2rem">→</span>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php
} else {
    // Show all modules
    $modules = $db->query('SELECT m.*, COUNT(c.id) as chapter_count FROM modules m LEFT JOIN chapters c ON c.module_id = m.id GROUP BY m.id ORDER BY m.id')->fetchAll();

    foreach ($modules as &$mod) {
        $stmt = $db->prepare('SELECT COUNT(*) FROM progress p JOIN chapters c ON c.id = p.chapter_id WHERE p.user_id = ? AND c.module_id = ? AND p.status = ?');
        $stmt->execute([$userId, $mod['id'], 'completed']);
        $mod['completed'] = $stmt->fetchColumn();
        $mod['progress'] = $mod['chapter_count'] > 0 ? round(($mod['completed'] / $mod['chapter_count']) * 100) : 0;
    }
    unset($mod);

    $pageTitle = 'Modules';
    $currentPage = 'modules';
    require_once INCLUDES_PATH . '/header.php';
    ?>

    <div class="section-header">
        <h2>All Modules</h2>
        <span class="text-muted"><?= count($modules) ?> modules</span>
    </div>

    <?php if (empty($modules)): ?>
        <div class="empty-state">
            <div class="empty-icon">📚</div>
            <h3>No Modules Available</h3>
            <p>Learning modules haven't been created yet. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="modules-grid">
            <?php foreach ($modules as $mod): ?>
            <a href="<?= BASE_URL ?>/user/modules.php?module_id=<?= $mod['id'] ?>" 
               class="module-card stagger-item" style="text-decoration:none"
               data-searchable="<?= e($mod['title'] . ' ' . ($mod['description'] ?? '')) ?>">
                <div class="module-icon"><?= $mod['icon'] ?? '📊' ?></div>
                <div class="module-title"><?= e($mod['title']) ?></div>
                <div class="module-desc"><?= e(substr($mod['description'] ?? '', 0, 150)) ?></div>
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
    <?php endif;
}

require_once INCLUDES_PATH . '/footer.php';
