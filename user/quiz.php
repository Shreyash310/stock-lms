<?php
/**
 * StockVerse - Quiz Page
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];
$chapterId = isset($_GET['chapter_id']) ? (int)$_GET['chapter_id'] : 0;

if (!$chapterId) {
    header('Location: ' . BASE_URL . '/user/modules.php');
    exit;
}

// Fetch chapter info
$stmt = $db->prepare('SELECT c.*, m.title as module_title FROM chapters c JOIN modules m ON m.id = c.module_id WHERE c.id = ?');
$stmt->execute([$chapterId]);
$chapter = $stmt->fetch();

if (!$chapter) {
    header('Location: ' . BASE_URL . '/user/modules.php');
    exit;
}

// Fetch quiz questions
$stmt = $db->prepare('SELECT * FROM quizzes WHERE chapter_id = ? ORDER BY id');
$stmt->execute([$chapterId]);
$questions = $stmt->fetchAll();

if (empty($questions)) {
    setFlash('warning', 'No quiz available for this chapter.');
    header('Location: ' . BASE_URL . '/user/chapter.php?id=' . $chapterId);
    exit;
}

$submitted = false;
$score = 0;
$total = count($questions);
$results = [];

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    
    if (validateCSRFToken($csrf)) {
        $submitted = true;
        
        foreach ($questions as $q) {
            $answer = isset($_POST['q_' . $q['id']]) ? (int)$_POST['q_' . $q['id']] : 0;
            $correct = $answer === (int)$q['correct_answer'];
            if ($correct) $score++;
            $results[$q['id']] = [
                'selected' => $answer,
                'correct' => (int)$q['correct_answer'],
                'is_correct' => $correct
            ];
        }
        
        // Save result
        $stmt = $db->prepare('INSERT INTO results (user_id, chapter_id, score, total) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $chapterId, $score, $total]);
    }
}

$pageTitle = 'Quiz: ' . $chapter['title'];
$currentPage = 'modules';
require_once INCLUDES_PATH . '/header.php';
?>

<a href="<?= BASE_URL ?>/user/chapter.php?id=<?= $chapterId ?>" class="btn btn-secondary btn-sm mb-24">
    ← Back to Chapter
</a>

<div class="quiz-container animate-slide-up">
    <div class="card mb-24">
        <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:4px">📝 Quiz: <?= e($chapter['title']) ?></h2>
        <p style="color:var(--text-secondary);font-size:0.9rem"><?= e($chapter['module_title']) ?> • <?= $total ?> questions</p>
    </div>

    <?php if ($submitted): ?>
        <!-- Results -->
        <div class="quiz-results animate-slide-up">
            <div class="score-circle">
                <span><?= $score ?>/<?= $total ?></span>
            </div>
            <h3>
                <?php
                $pct = $total > 0 ? round(($score / $total) * 100) : 0;
                if ($pct >= 80) echo '🎉 Excellent!';
                elseif ($pct >= 60) echo '👍 Good Job!';
                elseif ($pct >= 40) echo '📚 Keep Learning!';
                else echo '💪 Try Again!';
                ?>
            </h3>
            <p>You scored <?= $score ?> out of <?= $total ?> (<?= $pct ?>%)</p>
            <div style="display:flex;gap:12px;justify-content:center">
                <a href="<?= BASE_URL ?>/user/quiz.php?chapter_id=<?= $chapterId ?>" class="btn btn-primary">Retry Quiz</a>
                <a href="<?= BASE_URL ?>/user/chapter.php?id=<?= $chapterId ?>" class="btn btn-secondary">Back to Chapter</a>
            </div>
        </div>

        <!-- Show correct/incorrect answers -->
        <div style="margin-top:32px">
            <?php foreach ($questions as $index => $q): 
                $r = $results[$q['id']];
            ?>
            <div class="quiz-question">
                <div class="q-number">Question <?= $index + 1 ?></div>
                <div class="q-text"><?= e($q['question']) ?></div>
                <div class="quiz-options">
                    <?php for ($i = 1; $i <= 4; $i++): 
                        $optionKey = 'option' . $i;
                        $classes = 'quiz-option';
                        if ($r['selected'] == $i && $r['is_correct']) $classes .= ' correct';
                        elseif ($r['selected'] == $i && !$r['is_correct']) $classes .= ' incorrect';
                        elseif ($i == $r['correct']) $classes .= ' correct';
                    ?>
                    <label class="<?= $classes ?>">
                        <span class="radio-custom"></span>
                        <?= e($q[$optionKey]) ?>
                    </label>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Quiz Form -->
        <form method="POST" action="" id="quizForm">
            <?= csrfField() ?>
            
            <?php foreach ($questions as $index => $q): ?>
            <div class="quiz-question">
                <div class="q-number">Question <?= $index + 1 ?> of <?= $total ?></div>
                <div class="q-text"><?= e($q['question']) ?></div>
                <div class="quiz-options">
                    <?php for ($i = 1; $i <= 4; $i++): 
                        $optionKey = 'option' . $i;
                    ?>
                    <label class="quiz-option">
                        <input type="radio" name="q_<?= $q['id'] ?>" value="<?= $i ?>" required>
                        <span class="radio-custom"></span>
                        <?= e($q[$optionKey]) ?>
                    </label>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary btn-lg w-full mt-24">
                Submit Quiz →
            </button>
        </form>
    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
