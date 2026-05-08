<?php
/**
 * Admin — Timeline Editor
 */
define('ADMIN_PAGE', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$pageTitle = 'Timeline';
$db = getDB();
$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid token.'; $msgType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            $id       = (int)($_POST['event_id'] ?? 0);
            $year     = trim($_POST['year'] ?? '');
            $title    = trim($_POST['title'] ?? '');
            $desc     = trim($_POST['description'] ?? '');
            $icon     = trim($_POST['icon'] ?? '📍');
            $category = trim($_POST['category'] ?? 'Life');

            if ($id > 0) {
                $db->prepare("UPDATE timeline SET year=?,title=?,description=?,icon=?,category=? WHERE id=?")
                   ->execute([$year,$title,$desc,$icon,$category,$id]);
                $msg = 'Event updated.';
            } else {
                $db->prepare("INSERT INTO timeline (year,title,description,icon,category) VALUES (?,?,?,?,?)")
                   ->execute([$year,$title,$desc,$icon,$category]);
                $msg = 'Event added.';
            }
        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM timeline WHERE id = ?")->execute([(int)$_POST['event_id']]);
            $msg = 'Event deleted.';
        }
    }
}

$events = $db->query("SELECT * FROM timeline ORDER BY year ASC, id ASC")->fetchAll();

$editEvent = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM timeline WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editEvent = $stmt->fetch();
}

$categories = ['Birth','Childhood','Education','Career','Family','Achievement','Travel','Retirement','Passing','Life'];

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
    <div class="col-md-4">
        <div class="admin-card p-4">
            <h6 class="mb-3"><?= $editEvent ? '✏️ Edit Event' : '➕ Add Event' ?></h6>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="event_id" value="<?= $editEvent['id'] ?? 0 ?>">
                <div class="row g-2 mb-3">
                    <div class="col-5">
                        <label class="form-label small">Icon</label>
                        <input type="text" name="icon" class="form-control form-control-sm" maxlength="10"
                               value="<?= htmlspecialchars($editEvent['icon'] ?? '📍') ?>">
                    </div>
                    <div class="col-7">
                        <label class="form-label small">Year/Date <span class="text-danger">*</span></label>
                        <input type="text" name="year" class="form-control form-control-sm" required maxlength="20"
                               placeholder="1945 or 1945–1950"
                               value="<?= htmlspecialchars($editEvent['year'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-sm" required maxlength="150"
                           value="<?= htmlspecialchars($editEvent['title'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Description</label>
                    <textarea name="description" class="form-control form-control-sm" rows="4"
                              placeholder="Additional details…"><?= htmlspecialchars($editEvent['description'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <?php foreach ($categories as $cat): ?>
                        <option <?= ($editEvent['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-memorial flex-grow-1">
                        <?= $editEvent ? 'Save Changes' : 'Add Event' ?>
                    </button>
                    <?php if ($editEvent): ?>
                    <a href="timeline.php" class="btn btn-outline-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Events List -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="admin-card-header"><h6>📅 Timeline Events (<?= count($events) ?>)</h6></div>
            <?php if (empty($events)): ?>
            <div class="p-4 text-center text-muted">No events yet.</div>
            <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($events as $e): ?>
                <li class="list-group-item d-flex align-items-center gap-3 py-3">
                    <span style="font-size:1.3rem;"><?= htmlspecialchars($e['icon']) ?></span>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary rounded-pill" style="font-size:0.7rem;"><?= htmlspecialchars($e['year']) ?></span>
                            <span class="fw-semibold small"><?= htmlspecialchars($e['title']) ?></span>
                        </div>
                        <?php if ($e['description']): ?>
                        <div class="text-muted" style="font-size:0.72rem;"><?= htmlspecialchars(mb_substr($e['description'],0,60)) ?>…</div>
                        <?php endif; ?>
                        <span class="badge text-bg-light" style="font-size:0.65rem;"><?= $e['category'] ?></span>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <a href="timeline.php?edit=<?= $e['id'] ?>" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;">Edit</a>
                        <form method="POST" onsubmit="return confirm('Delete this event?');">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;">Del</button>
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
