<?php
/**
 * StockVerse - Admin Modules CRUD
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/auth.php';
requireAdmin();

$db = getDB();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (validateCSRFToken($csrf)) {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icon = trim($_POST['icon'] ?? '📊');
            if ($title) {
                $stmt = $db->prepare('INSERT INTO modules (title, description, icon) VALUES (?, ?, ?)');
                $stmt->execute([$title, $description, $icon]);
                setFlash('success', 'Module created successfully.');
            }
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icon = trim($_POST['icon'] ?? '📊');
            if ($id && $title) {
                $stmt = $db->prepare('UPDATE modules SET title = ?, description = ?, icon = ? WHERE id = ?');
                $stmt->execute([$title, $description, $icon, $id]);
                setFlash('success', 'Module updated successfully.');
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $stmt = $db->prepare('DELETE FROM modules WHERE id = ?');
                $stmt->execute([$id]);
                setFlash('success', 'Module deleted successfully.');
            }
        }
    }
    header('Location: ' . BASE_URL . '/admin/modules.php');
    exit;
}

$modules = $db->query('SELECT m.*, COUNT(c.id) as chapter_count FROM modules m LEFT JOIN chapters c ON c.module_id = m.id GROUP BY m.id ORDER BY m.id')->fetchAll();

$pageTitle = 'Manage Modules';
$currentPage = 'admin-modules';
require_once INCLUDES_PATH . '/header.php';
?>

<div class="section-header">
    <h2>Modules (<?= count($modules) ?>)</h2>
    <button class="btn btn-primary" data-modal-open="moduleModal" onclick="document.getElementById('moduleForm').reset();document.getElementById('moduleFormAction').value='create';document.getElementById('moduleFormId').value='';document.querySelector('#moduleModal .modal-header h3').textContent='Add Module'">
        + Add Module
    </button>
</div>

<?php if (empty($modules)): ?>
    <div class="empty-state">
        <div class="empty-icon">📚</div>
        <h3>No Modules Yet</h3>
        <p>Create your first learning module to get started.</p>
    </div>
<?php else: ?>
<div class="table-container animate-slide-up">
    <table class="data-table">
        <thead><tr><th>Icon</th><th>Title</th><th>Description</th><th>Chapters</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($modules as $m): ?>
            <tr>
                <td style="font-size:1.5rem"><?= e($m['icon'] ?? '📊') ?></td>
                <td><strong><?= e($m['title']) ?></strong></td>
                <td style="color:var(--text-secondary);max-width:300px"><?= e(substr($m['description'] ?? '', 0, 80)) ?>...</td>
                <td><span class="badge badge-primary"><?= $m['chapter_count'] ?></span></td>
                <td>
                    <div class="actions">
                        <button class="btn btn-secondary btn-sm" onclick="editItem('moduleModal',{action:'update',id:'<?= $m['id'] ?>',title:'<?= e(addslashes($m['title'])) ?>',description:'<?= e(addslashes($m['description'] ?? '')) ?>',icon:'<?= e($m['icon'] ?? '📊') ?>'});document.querySelector('#moduleModal .modal-header h3').textContent='Edit Module'">Edit</button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this module and all its chapters?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
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

<!-- Module Modal -->
<div class="modal-overlay" id="moduleModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Module</h3>
            <button class="modal-close">✕</button>
        </div>
        <form method="POST" id="moduleForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" id="moduleFormAction" value="create">
            <input type="hidden" name="id" id="moduleFormId" value="">
            
            <div class="form-group">
                <label>Icon (emoji)</label>
                <input type="text" name="icon" class="form-control" placeholder="📊" value="📊">
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" placeholder="Module title" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" placeholder="Brief description of this module"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Module</button>
            </div>
        </form>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
