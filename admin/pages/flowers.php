<?php
/**
 * Admin — Flowers Catalog
 */
define('ADMIN_PAGE', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$pageTitle = 'Flowers Catalog';
$db = getDB();
$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id      = (int)$_POST['flower_id'];
        $name    = trim($_POST['flower_name'] ?? '');
        $emoji   = trim($_POST['flower_emoji'] ?? '🌸');
        $meaning = trim($_POST['symbolic_meaning'] ?? '');
        $color   = trim($_POST['color'] ?? '');
        $active  = isset($_POST['is_active']) ? 1 : 0;

        if ($id > 0) {
            $db->prepare("UPDATE flowers_catalog SET flower_name=?,flower_emoji=?,symbolic_meaning=?,color=?,is_active=? WHERE id=?")
               ->execute([$name,$emoji,$meaning,$color,$active,$id]);
            $msg = 'Flower updated.';
        } else {
            $db->prepare("INSERT INTO flowers_catalog (flower_name,flower_emoji,symbolic_meaning,color,is_active) VALUES (?,?,?,?,?)")
               ->execute([$name,$emoji,$meaning,$color,$active]);
            $msg = 'Flower added.';
        }
    } elseif ($action === 'delete') {
        $db->prepare("DELETE FROM flowers_catalog WHERE id=?")->execute([(int)$_POST['flower_id']]);
        $msg = 'Flower deleted.';
    } elseif ($action === 'toggle') {
        $id = (int)$_POST['flower_id'];
        $db->prepare("UPDATE flowers_catalog SET is_active = NOT is_active WHERE id=?")->execute([$id]);
        $msg = 'Status toggled.';
    }
}

$flowers = $db->query("SELECT * FROM flowers_catalog ORDER BY id ASC")->fetchAll();
$editFlower = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM flowers_catalog WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editFlower = $stmt->fetch();
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
    <div class="col-md-4">
        <div class="admin-card p-4">
            <h6 class="mb-3"><?= $editFlower ? '✏️ Edit Flower' : '➕ Add Flower' ?></h6>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="flower_id" value="<?= $editFlower['id'] ?? 0 ?>">
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label small">Emoji</label>
                        <input type="text" name="flower_emoji" class="form-control form-control-sm text-center"
                               style="font-size:1.3rem;" maxlength="10"
                               value="<?= htmlspecialchars($editFlower['flower_emoji'] ?? '🌸') ?>">
                    </div>
                    <div class="col-8">
                        <label class="form-label small">Flower Name <span class="text-danger">*</span></label>
                        <input type="text" name="flower_name" class="form-control form-control-sm" required maxlength="100"
                               value="<?= htmlspecialchars($editFlower['flower_name'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Symbolic Meaning</label>
                    <textarea name="symbolic_meaning" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($editFlower['symbolic_meaning'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Color (hex or name)</label>
                    <div class="input-group input-group-sm">
                        <input type="color" name="color" class="form-control form-control-sm form-control-color"
                               value="<?= htmlspecialchars($editFlower['color'] ?? '#ffffff') ?>" style="max-width:50px;">
                        <input type="text" id="colorText" class="form-control form-control-sm"
                               placeholder="#ffffff" value="<?= htmlspecialchars($editFlower['color'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="fa"
                           <?= ($editFlower['is_active'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="fa">Active (visible to members)</label>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-memorial flex-grow-1"><?= $editFlower ? 'Save' : 'Add Flower' ?></button>
                    <?php if ($editFlower): ?><a href="flowers.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="admin-card">
            <div class="admin-card-header"><h6>🌹 Flowers Catalog (<?= count($flowers) ?>)</h6></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Flower</th><th>Symbolic Meaning</th><th>Color</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($flowers as $f): ?>
                    <tr>
                        <td>
                            <span style="font-size:1.3rem;"><?= htmlspecialchars($f['flower_emoji']) ?></span>
                            <span class="fw-semibold small ms-1"><?= htmlspecialchars($f['flower_name']) ?></span>
                        </td>
                        <td class="small text-muted"><?= htmlspecialchars(mb_substr($f['symbolic_meaning']??'',0,50)) ?></td>
                        <td>
                            <span style="display:inline-block;width:20px;height:20px;border-radius:50%;background:<?= htmlspecialchars($f['color']) ?>;border:1px solid #ddd;vertical-align:middle;"></span>
                        </td>
                        <td>
                            <span class="badge <?= $f['is_active']?'bg-success':'bg-secondary' ?>">
                                <?= $f['is_active']?'Active':'Hidden' ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="flowers.php?edit=<?= $f['id'] ?>" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;">Edit</a>
                                <form method="POST">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="flower_id" value="<?= $f['id'] ?>">
                                    <button class="btn btn-sm btn-outline-secondary" style="font-size:0.75rem;">Toggle</button>
                                </form>
                                <form method="POST" onsubmit="return confirm('Delete?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="flower_id" value="<?= $f['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;">Del</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Sync color input
const colorPicker = document.querySelector('input[type=color]');
const colorText   = document.getElementById('colorText');
if (colorPicker && colorText) {
    colorPicker.addEventListener('input', () => { colorText.value = colorPicker.value; });
    colorText.addEventListener('input',   () => { colorPicker.value = colorText.value; });
}
</script>

<?php include '../includes/footer.php'; ?>
