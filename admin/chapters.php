<?php
/**
 * StockVerse - Admin Chapters CRUD
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
            $moduleId = (int)($_POST['module_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $orderIndex = (int)($_POST['order_index'] ?? 0);
            if ($moduleId && $title) {
                $stmt = $db->prepare('INSERT INTO chapters (module_id, title, content, order_index) VALUES (?, ?, ?, ?)');
                $stmt->execute([$moduleId, $title, $content, $orderIndex]);
                setFlash('success', 'Chapter created successfully.');
            }
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $moduleId = (int)($_POST['module_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $orderIndex = (int)($_POST['order_index'] ?? 0);
            if ($id && $title) {
                $stmt = $db->prepare('UPDATE chapters SET module_id = ?, title = ?, content = ?, order_index = ? WHERE id = ?');
                $stmt->execute([$moduleId, $title, $content, $orderIndex, $id]);
                setFlash('success', 'Chapter updated successfully.');
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $stmt = $db->prepare('DELETE FROM chapters WHERE id = ?');
                $stmt->execute([$id]);
                setFlash('success', 'Chapter deleted successfully.');
            }
        }
    }
    header('Location: ' . BASE_URL . '/admin/chapters.php' . (isset($_POST['filter_module']) ? '?module_id=' . (int)$_POST['filter_module'] : ''));
    exit;
}

// Fetch modules for filter/dropdown
$modules = $db->query('SELECT * FROM modules ORDER BY id')->fetchAll();
$filterModule = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

// Fetch chapters
if ($filterModule > 0) {
    $stmt = $db->prepare('SELECT c.*, m.title as module_title FROM chapters c JOIN modules m ON m.id = c.module_id WHERE c.module_id = ? ORDER BY c.order_index');
    $stmt->execute([$filterModule]);
} else {
    $stmt = $db->query('SELECT c.*, m.title as module_title FROM chapters c JOIN modules m ON m.id = c.module_id ORDER BY c.module_id, c.order_index');
}
$chapters = $stmt->fetchAll();

$pageTitle = 'Manage Chapters';
$currentPage = 'admin-chapters';
require_once INCLUDES_PATH . '/header.php';
?>

<div class="section-header">
    <h2>Chapters (<?= count($chapters) ?>)</h2>
    <button class="btn btn-primary" data-modal-open="chapterModal" onclick="document.getElementById('chapterForm').reset();document.getElementById('chapterFormAction').value='create';document.getElementById('chapterFormId').value='';document.querySelector('#chapterModal .modal-header h3').textContent='Add Chapter'">
        + Add Chapter
    </button>
</div>

<!-- Filter -->
<div class="card mb-24">
    <form method="GET" class="d-flex align-center gap-12">
        <label style="font-size:0.85rem;font-weight:600;white-space:nowrap">Filter by Module:</label>
        <select name="module_id" class="form-control" style="max-width:300px" onchange="this.form.submit()">
            <option value="0">All Modules</option>
            <?php foreach ($modules as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $filterModule == $m['id'] ? 'selected' : '' ?>><?= e($m['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if (empty($chapters)): ?>
    <div class="empty-state">
        <div class="empty-icon">📄</div>
        <h3>No Chapters</h3>
        <p>Create chapters to add content to your modules.</p>
    </div>
<?php else: ?>
<div class="table-container animate-slide-up">
    <table class="data-table">
        <thead><tr><th>#</th><th>Title</th><th>Module</th><th>Order</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($chapters as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><strong><?= e($c['title']) ?></strong></td>
                <td><span class="badge badge-primary"><?= e($c['module_title']) ?></span></td>
                <td><?= $c['order_index'] ?></td>
                <td>
                    <div class="actions">
                        <button class="btn btn-secondary btn-sm" onclick="openChapterEdit(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>)">Edit</button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this chapter?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <input type="hidden" name="filter_module" value="<?= $filterModule ?>">
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

<!-- Chapter Modal -->
<div class="modal-overlay" id="chapterModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Chapter</h3>
            <button class="modal-close">✕</button>
        </div>
        <form method="POST" id="chapterForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" id="chapterFormAction" value="create">
            <input type="hidden" name="id" id="chapterFormId" value="">
            <input type="hidden" name="filter_module" value="<?= $filterModule ?>">

            <div class="form-group">
                <label>Module</label>
                <select name="module_id" class="form-control" required>
                    <option value="">Select Module</option>
                    <?php foreach ($modules as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= e($m['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" placeholder="Chapter title" required>
            </div>
            <div class="form-group">
                <label>Order Index</label>
                <input type="number" name="order_index" class="form-control" value="0" min="0">
            </div>
            <div class="form-group">
                <label>Content (HTML)</label>
                <textarea name="content" class="form-control" rows="12" placeholder="Chapter content (HTML supported)"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Chapter</button>
            </div>
        </form>
    </div>
</div>

<script>
function openChapterEdit(chapter) {
    document.getElementById('chapterFormAction').value = 'update';
    document.getElementById('chapterFormId').value = chapter.id;
    document.querySelector('#chapterForm [name="module_id"]').value = chapter.module_id;
    document.querySelector('#chapterForm [name="title"]').value = chapter.title;
    document.querySelector('#chapterForm [name="order_index"]').value = chapter.order_index;
    document.querySelector('#chapterForm [name="content"]').value = chapter.content || '';
    document.querySelector('#chapterModal .modal-header h3').textContent = 'Edit Chapter';
    document.getElementById('chapterModal').classList.add('active');
}
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
