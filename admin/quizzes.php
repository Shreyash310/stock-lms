<?php
/**
 * StockVerse - Admin Quizzes CRUD
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/auth.php';
requireAdmin();

$db = getDB();

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (validateCSRFToken($csrf)) {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $chapterId = (int)($_POST['chapter_id'] ?? 0);
            $question = trim($_POST['question'] ?? '');
            $o1 = trim($_POST['option1'] ?? '');
            $o2 = trim($_POST['option2'] ?? '');
            $o3 = trim($_POST['option3'] ?? '');
            $o4 = trim($_POST['option4'] ?? '');
            $correct = (int)($_POST['correct_answer'] ?? 0);
            if ($chapterId && $question && $o1 && $o2 && $o3 && $o4 && $correct >= 1 && $correct <= 4) {
                $stmt = $db->prepare('INSERT INTO quizzes (chapter_id, question, option1, option2, option3, option4, correct_answer) VALUES (?,?,?,?,?,?,?)');
                $stmt->execute([$chapterId, $question, $o1, $o2, $o3, $o4, $correct]);
                setFlash('success', 'Quiz question added.');
            }
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $chapterId = (int)($_POST['chapter_id'] ?? 0);
            $question = trim($_POST['question'] ?? '');
            $o1 = trim($_POST['option1'] ?? '');
            $o2 = trim($_POST['option2'] ?? '');
            $o3 = trim($_POST['option3'] ?? '');
            $o4 = trim($_POST['option4'] ?? '');
            $correct = (int)($_POST['correct_answer'] ?? 0);
            if ($id) {
                $stmt = $db->prepare('UPDATE quizzes SET chapter_id=?, question=?, option1=?, option2=?, option3=?, option4=?, correct_answer=? WHERE id=?');
                $stmt->execute([$chapterId, $question, $o1, $o2, $o3, $o4, $correct, $id]);
                setFlash('success', 'Quiz question updated.');
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $stmt = $db->prepare('DELETE FROM quizzes WHERE id = ?');
                $stmt->execute([$id]);
                setFlash('success', 'Quiz question deleted.');
            }
        }
    }
    header('Location: ' . BASE_URL . '/admin/quizzes.php' . (isset($_POST['filter_chapter']) ? '?chapter_id=' . (int)$_POST['filter_chapter'] : ''));
    exit;
}

// Chapters for dropdown
$chapters = $db->query('SELECT c.id, c.title, m.title as module_title FROM chapters c JOIN modules m ON m.id = c.module_id ORDER BY m.id, c.order_index')->fetchAll();
$filterChapter = isset($_GET['chapter_id']) ? (int)$_GET['chapter_id'] : 0;

// Fetch quizzes
if ($filterChapter > 0) {
    $stmt = $db->prepare('SELECT q.*, c.title as chapter_title FROM quizzes q JOIN chapters c ON c.id = q.chapter_id WHERE q.chapter_id = ? ORDER BY q.id');
    $stmt->execute([$filterChapter]);
} else {
    $stmt = $db->query('SELECT q.*, c.title as chapter_title FROM quizzes q JOIN chapters c ON c.id = q.chapter_id ORDER BY q.chapter_id, q.id');
}
$quizzes = $stmt->fetchAll();

$pageTitle = 'Manage Quizzes';
$currentPage = 'admin-quizzes';
require_once INCLUDES_PATH . '/header.php';
?>

<div class="section-header">
    <h2>Quiz Questions (<?= count($quizzes) ?>)</h2>
    <button class="btn btn-primary" data-modal-open="quizModal" onclick="document.getElementById('quizForm').reset();document.getElementById('quizFormAction').value='create';document.getElementById('quizFormId').value='';document.querySelector('#quizModal .modal-header h3').textContent='Add Question'">
        + Add Question
    </button>
</div>

<!-- Filter -->
<div class="card mb-24">
    <form method="GET" class="d-flex align-center gap-12">
        <label style="font-size:0.85rem;font-weight:600;white-space:nowrap">Filter by Chapter:</label>
        <select name="chapter_id" class="form-control" style="max-width:400px" onchange="this.form.submit()">
            <option value="0">All Chapters</option>
            <?php foreach ($chapters as $ch): ?>
                <option value="<?= $ch['id'] ?>" <?= $filterChapter == $ch['id'] ? 'selected' : '' ?>><?= e($ch['module_title'] . ' → ' . $ch['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if (empty($quizzes)): ?>
    <div class="empty-state">
        <div class="empty-icon">❓</div>
        <h3>No Quiz Questions</h3>
        <p>Add quiz questions to test your learners.</p>
    </div>
<?php else: ?>
<div class="table-container animate-slide-up">
    <table class="data-table">
        <thead><tr><th>#</th><th>Question</th><th>Chapter</th><th>Correct</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($quizzes as $q): ?>
            <tr>
                <td><?= $q['id'] ?></td>
                <td style="max-width:300px"><strong><?= e(substr($q['question'], 0, 60)) ?><?= strlen($q['question']) > 60 ? '...' : '' ?></strong></td>
                <td><span class="badge badge-primary"><?= e($q['chapter_title']) ?></span></td>
                <td><span class="badge badge-success">Option <?= $q['correct_answer'] ?></span></td>
                <td>
                    <div class="actions">
                        <button class="btn btn-secondary btn-sm" onclick='openQuizEdit(<?= json_encode($q) ?>)'>Edit</button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this question?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                            <input type="hidden" name="filter_chapter" value="<?= $filterChapter ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Quiz Modal -->
<div class="modal-overlay" id="quizModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Question</h3>
            <button class="modal-close">✕</button>
        </div>
        <form method="POST" id="quizForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" id="quizFormAction" value="create">
            <input type="hidden" name="id" id="quizFormId" value="">
            <input type="hidden" name="filter_chapter" value="<?= $filterChapter ?>">

            <div class="form-group">
                <label>Chapter</label>
                <select name="chapter_id" class="form-control" required>
                    <option value="">Select Chapter</option>
                    <?php foreach ($chapters as $ch): ?>
                        <option value="<?= $ch['id'] ?>"><?= e($ch['module_title'] . ' → ' . $ch['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Question</label>
                <textarea name="question" class="form-control" rows="3" required placeholder="Enter the question"></textarea>
            </div>
            <div class="form-group">
                <label>Option 1</label>
                <input type="text" name="option1" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Option 2</label>
                <input type="text" name="option2" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Option 3</label>
                <input type="text" name="option3" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Option 4</label>
                <input type="text" name="option4" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Correct Answer</label>
                <select name="correct_answer" class="form-control" required>
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                    <option value="3">Option 3</option>
                    <option value="4">Option 4</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Question</button>
            </div>
        </form>
    </div>
</div>

<script>
function openQuizEdit(q) {
    document.getElementById('quizFormAction').value = 'update';
    document.getElementById('quizFormId').value = q.id;
    document.querySelector('#quizForm [name="chapter_id"]').value = q.chapter_id;
    document.querySelector('#quizForm [name="question"]').value = q.question;
    document.querySelector('#quizForm [name="option1"]').value = q.option1;
    document.querySelector('#quizForm [name="option2"]').value = q.option2;
    document.querySelector('#quizForm [name="option3"]').value = q.option3;
    document.querySelector('#quizForm [name="option4"]').value = q.option4;
    document.querySelector('#quizForm [name="correct_answer"]').value = q.correct_answer;
    document.querySelector('#quizModal .modal-header h3').textContent = 'Edit Question';
    document.getElementById('quizModal').classList.add('active');
}
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
