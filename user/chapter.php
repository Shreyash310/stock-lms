<?php
/**
 * StockVerse - Chapter View
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];
$chapterId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$chapterId) {
    header('Location: ' . BASE_URL . '/user/modules.php');
    exit;
}

// Handle AJAX mark as complete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['chapter_id'])) {
    header('Content-Type: application/json');
    $cid = (int)$_POST['chapter_id'];
    $csrf = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf)) {
        echo json_encode(['success' => false, 'error' => 'Invalid token']);
        exit;
    }
    
    $stmt = $db->prepare('INSERT INTO progress (user_id, chapter_id, status, completed_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE status = ?, completed_at = NOW()');
    $stmt->execute([$userId, $cid, 'completed', 'completed']);
    echo json_encode(['success' => true]);
    exit;
}

// Fetch chapter
$stmt = $db->prepare('SELECT c.*, m.title as module_title, m.id as module_id FROM chapters c JOIN modules m ON m.id = c.module_id WHERE c.id = ?');
$stmt->execute([$chapterId]);
$chapter = $stmt->fetch();

if (!$chapter) {
    header('Location: ' . BASE_URL . '/user/modules.php');
    exit;
}

// Mark as in_progress if not already tracked
$stmt = $db->prepare('INSERT IGNORE INTO progress (user_id, chapter_id, status) VALUES (?, ?, ?)');
$stmt->execute([$userId, $chapterId, 'in_progress']);

// Get all chapters in this module (for sidebar + navigation)
$stmt = $db->prepare('SELECT c.id, c.title, c.order_index, IFNULL(p.status, "not_started") as progress_status FROM chapters c LEFT JOIN progress p ON p.chapter_id = c.id AND p.user_id = ? WHERE c.module_id = ? ORDER BY c.order_index ASC');
$stmt->execute([$userId, $chapter['module_id']]);
$allChapters = $stmt->fetchAll();

// Find prev/next
$currentIndex = -1;
foreach ($allChapters as $i => $ch) {
    if ($ch['id'] == $chapterId) {
        $currentIndex = $i;
        break;
    }
}
$prevChapter = $currentIndex > 0 ? $allChapters[$currentIndex - 1] : null;
$nextChapter = $currentIndex < count($allChapters) - 1 ? $allChapters[$currentIndex + 1] : null;

// Check if completed
$stmt = $db->prepare('SELECT status FROM progress WHERE user_id = ? AND chapter_id = ?');
$stmt->execute([$userId, $chapterId]);
$progressRow = $stmt->fetch();
$isCompleted = $progressRow && $progressRow['status'] === 'completed';

// Check if quiz exists
$stmt = $db->prepare('SELECT COUNT(*) FROM quizzes WHERE chapter_id = ?');
$stmt->execute([$chapterId]);
$hasQuiz = $stmt->fetchColumn() > 0;

$pageTitle = $chapter['title'];
$currentPage = 'modules';
require_once INCLUDES_PATH . '/header.php';
?>

<a href="<?= BASE_URL ?>/user/modules.php?module_id=<?= $chapter['module_id'] ?>" class="btn btn-secondary btn-sm mb-24">
    ← <?= e($chapter['module_title']) ?>
</a>

<div class="chapter-layout">
    <!-- Main Content -->
    <div class="animate-fade-in">
        <div class="chapter-content">
            <?= $chapter['content'] ?>
        </div>

        <!-- Actions -->
        <div style="margin-top:32px;display:flex;gap:12px;flex-wrap:wrap">
            <?php if (!$isCompleted): ?>
                <button id="markCompleteBtn" class="btn btn-success"
                        data-chapter-id="<?= $chapterId ?>"
                        data-url="<?= BASE_URL ?>/user/chapter.php?id=<?= $chapterId ?>"
                        data-csrf="<?= e(generateCSRFToken()) ?>">
                    ✓ Mark as Complete
                </button>
            <?php else: ?>
                <button class="btn btn-secondary" disabled>✅ Completed</button>
            <?php endif; ?>

            <?php if ($hasQuiz): ?>
                <a href="<?= BASE_URL ?>/user/quiz.php?chapter_id=<?= $chapterId ?>" class="btn btn-primary">
                    📝 Take Quiz
                </a>
            <?php endif; ?>
        </div>

        <!-- Navigation -->
        <div class="chapter-nav">
            <?php if ($prevChapter): ?>
                <a href="<?= BASE_URL ?>/user/chapter.php?id=<?= $prevChapter['id'] ?>" class="nav-btn">
                    ← <?= e($prevChapter['title']) ?>
                </a>
            <?php else: ?>
                <span class="nav-btn disabled">← Previous</span>
            <?php endif; ?>

            <?php if ($nextChapter): ?>
                <a href="<?= BASE_URL ?>/user/chapter.php?id=<?= $nextChapter['id'] ?>" class="nav-btn">
                    <?= e($nextChapter['title']) ?> →
                </a>
            <?php else: ?>
                <span class="nav-btn disabled">Next →</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="chapter-sidebar">
        <div class="card">
            <h3 style="font-size:0.9rem;font-weight:700;margin-bottom:16px">📖 Chapters</h3>
            <ul class="chapter-list">
                <?php foreach ($allChapters as $ch): ?>
                <li>
                    <a href="<?= BASE_URL ?>/user/chapter.php?id=<?= $ch['id'] ?>" 
                       class="chapter-list-item <?= $ch['id'] == $chapterId ? 'active' : '' ?> <?= $ch['progress_status'] === 'completed' ? 'completed' : '' ?>"
                       data-chapter="<?= $ch['id'] ?>">
                        <span class="check-mark"><?= $ch['progress_status'] === 'completed' ? '✓' : '○' ?></span>
                        <?= e($ch['title']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
