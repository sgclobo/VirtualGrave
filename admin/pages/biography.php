<?php
/**
 * Admin — Biography Editor
 */
define('ADMIN_PAGE', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$pageTitle = 'Edit Biography';
$db = getDB();
$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid token.'; $msgType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_section') {
            $id      = (int)($_POST['section_id'] ?? 0);
            $title   = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $icon    = trim($_POST['icon'] ?? '📖');
            $sortOrder = (int)($_POST['sort_order'] ?? 0);

            if ($id > 0) {
                $db->prepare("UPDATE biography SET section_title=?, section_content=?, icon=?, section_order=? WHERE id=?")
                   ->execute([$title, $content, $icon, $sortOrder, $id]);
                $msg = 'Section updated.';
            } else {
                $db->prepare("INSERT INTO biography (section_title, section_content, icon, section_order) VALUES (?,?,?,?)")
                   ->execute([$title, $content, $icon, $sortOrder]);
                $msg = 'Section added.';
            }
        } elseif ($action === 'delete_section') {
            $id = (int)($_POST['section_id'] ?? 0);
            $db->prepare("DELETE FROM biography WHERE id = ?")->execute([$id]);
            $msg = 'Section deleted.';
        }
    }
}

$sections = $db->query("SELECT * FROM biography ORDER BY section_order ASC, id ASC")->fetchAll();
$editSection = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM biography WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editSection = $stmt->fetch();
}

include '../includes/header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
    <?= htmlspecialchars($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Form -->
    <div class="col-md-5">
        <div class="admin-card p-4">
            <h6 class="mb-3"><?= $editSection ? '✏️ Edit Section' : '➕ Add Section' ?></h6>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save_section">
                <input type="hidden" name="section_id" value="<?= $editSection['id'] ?? 0 ?>">
                <div class="mb-3">
                    <label class="form-label small">Icon (emoji)</label>
                    <input type="text" name="icon" class="form-control form-control-sm" maxlength="10"
                           value="<?= htmlspecialchars($editSection['icon'] ?? '📖') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Section Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-sm" required maxlength="100"
                           value="<?= htmlspecialchars($editSection['section_title'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Content <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control form-control-sm" rows="8" required
                              placeholder="Write the biography section content here…"><?= htmlspecialchars($editSection['section_content'] ?? '') ?></textarea>
                    <div class="form-text">Basic HTML allowed (p, strong, em, br, ul, li).</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control form-control-sm"
                           value="<?= $editSection['section_order'] ?? count($sections) + 1 ?>">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-memorial flex-grow-1">
                        <?= $editSection ? 'Save Changes' : 'Add Section' ?>
                    </button>
                    <?php if ($editSection): ?>
                    <a href="biography.php" class="btn btn-outline-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Sections List -->
    <div class="col-md-7">
        <div class="admin-card">
            <div class="admin-card-header"><h6>📖 Biography Sections (<?= count($sections) ?>)</h6></div>
            <?php if (empty($sections)): ?>
            <div class="p-4 text-center text-muted">No sections yet. Add one above.</div>
            <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($sections as $s): ?>
                <li class="list-group-item d-flex align-items-start gap-3 py-3">
                    <span style="font-size:1.4rem;"><?= htmlspecialchars($s['icon']) ?></span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold small"><?= htmlspecialchars($s['section_title']) ?></div>
                        <div class="text-muted" style="font-size:0.72rem;">
                            <?= htmlspecialchars(mb_substr(strip_tags($s['section_content']),0,80)) ?>…
                        </div>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <a href="biography.php?edit=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;">Edit</a>
                        <form method="POST" onsubmit="return confirm('Delete this section?');">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete_section">
                            <input type="hidden" name="section_id" value="<?= $s['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;">Delete</button>
                        </form>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
