<?php
/**
 * Admin — Candles Catalog
 */
define('ADMIN_PAGE', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$pageTitle = 'Candles Catalog';
$db = getDB();
$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id         = (int)$_POST['candle_id'];
        $name       = trim($_POST['candle_name'] ?? '');
        $type       = trim($_POST['candle_type'] ?? '');
        $glowColor  = trim($_POST['glow_color'] ?? '#ffcc66');
        $active     = isset($_POST['is_active']) ? 1 : 0;

        if ($id > 0) {
            $db->prepare("UPDATE candles_catalog SET candle_name=?,candle_type=?,glow_color=?,is_active=? WHERE id=?")
               ->execute([$name,$type,$glowColor,$active,$id]);
            $msg = 'Candle updated.';
        } else {
            $db->prepare("INSERT INTO candles_catalog (candle_name,candle_type,glow_color,is_active) VALUES (?,?,?,?)")
               ->execute([$name,$type,$glowColor,$active]);
            $msg = 'Candle added.';
        }
    } elseif ($action === 'delete') {
        $db->prepare("DELETE FROM candles_catalog WHERE id=?")->execute([(int)$_POST['candle_id']]);
        $msg = 'Candle deleted.';
    } elseif ($action === 'toggle') {
        $db->prepare("UPDATE candles_catalog SET is_active = NOT is_active WHERE id=?")->execute([(int)$_POST['candle_id']]);
        $msg = 'Status toggled.';
    }
}

$candles = $db->query("SELECT * FROM candles_catalog ORDER BY id ASC")->fetchAll();
$editCandle = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM candles_catalog WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editCandle = $stmt->fetch();
}

include '../includes/header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
    <?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="admin-card p-4">
            <h6 class="mb-3"><?= $editCandle ? '✏️ Edit Candle' : '➕ Add Candle' ?></h6>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="candle_id" value="<?= $editCandle['id'] ?? 0 ?>">
                <div class="mb-3">
                    <label class="form-label small">Candle Name <span class="text-danger">*</span></label>
                    <input type="text" name="candle_name" class="form-control form-control-sm" required maxlength="100"
                           value="<?= htmlspecialchars($editCandle['candle_name'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Candle Type</label>
                    <input type="text" name="candle_type" class="form-control form-control-sm" maxlength="100"
                           placeholder="e.g. Memorial, Prayer, Eternal Flame"
                           value="<?= htmlspecialchars($editCandle['candle_type'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Glow Color</label>
                    <div class="input-group input-group-sm">
                        <input type="color" name="glow_color" class="form-control form-control-sm form-control-color"
                               value="<?= htmlspecialchars($editCandle['glow_color'] ?? '#ffcc66') ?>" style="max-width:50px;"
                               id="glowColorPicker">
                        <input type="text" id="glowColorText" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($editCandle['glow_color'] ?? '#ffcc66') ?>">
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="ca"
                           <?= ($editCandle['is_active'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="ca">Active (visible to members)</label>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-memorial flex-grow-1"><?= $editCandle ? 'Save' : 'Add Candle' ?></button>
                    <?php if ($editCandle): ?><a href="candles.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="admin-card">
            <div class="admin-card-header"><h6>🕯️ Candles Catalog (<?= count($candles) ?>)</h6></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Candle</th><th>Type</th><th>Glow</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($candles as $c): ?>
                    <tr>
                        <td>
                            <span style="font-size:1.3rem;">🕯️</span>
                            <span class="fw-semibold small ms-1"><?= htmlspecialchars($c['candle_name']) ?></span>
                        </td>
                        <td class="small text-muted"><?= htmlspecialchars($c['candle_type'] ?? '') ?></td>
                        <td>
                            <span style="display:inline-block;width:24px;height:24px;border-radius:50%;background:<?= htmlspecialchars($c['glow_color']) ?>;box-shadow:0 0 8px <?= htmlspecialchars($c['glow_color']) ?>;border:1px solid #ddd;vertical-align:middle;"></span>
                        </td>
                        <td><span class="badge <?= $c['is_active']?'bg-success':'bg-secondary' ?>"><?= $c['is_active']?'Active':'Hidden' ?></span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="candles.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;">Edit</a>
                                <form method="POST">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="candle_id" value="<?= $c['id'] ?>">
                                    <button class="btn btn-sm btn-outline-secondary" style="font-size:0.75rem;">Toggle</button>
                                </form>
                                <form method="POST" onsubmit="return confirm('Delete?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="candle_id" value="<?= $c['id'] ?>">
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
const cp = document.getElementById('glowColorPicker');
const ct = document.getElementById('glowColorText');
if (cp && ct) {
    cp.addEventListener('input', () => { ct.value = cp.value; });
    ct.addEventListener('input', () => { cp.value = ct.value; });
}
</script>

<?php include '../includes/footer.php'; ?>
