<?php
/**
 * Admin — Content Moderation
 */
define('ADMIN_PAGE', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireAdmin();

$pageTitle = 'Moderate Content';
$db = getDB();

$msg     = '';
$msgType = 'success';
$type    = $_GET['type'] ?? 'prayers'; // prayers | testimonies | guestbook | flowers

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid security token.'; $msgType = 'danger';
    } else {
        $action   = $_POST['action'] ?? '';
        $itemId   = (int)($_POST['item_id'] ?? 0);
        $itemType = $_POST['item_type'] ?? '';

        if ($itemId > 0 && in_array($itemType, ['testimonies','prayers','guestbook','deposited_flowers'], true)) {
            if ($action === 'approve' && in_array($itemType, ['testimonies','guestbook'])) {
                $db->prepare("UPDATE $itemType SET is_approved = 1 WHERE id = ?")->execute([$itemId]);
                $msg = 'Item approved.';
            } elseif ($action === 'delete') {
                $db->prepare("DELETE FROM $itemType WHERE id = ?")->execute([$itemId]);
                $msg = 'Item deleted.';
            }
        }
    }
}

// Tabs data
$tabTypes = ['prayers','testimonies','guestbook','flowers'];
$counts   = [];
$counts['prayers']     = (int)$db->query("SELECT COUNT(*) FROM prayers")->fetchColumn();
$counts['testimonies'] = (int)$db->query("SELECT COUNT(*) FROM testimonies WHERE is_approved = 0")->fetchColumn();
$counts['guestbook']   = (int)$db->query("SELECT COUNT(*) FROM guestbook WHERE is_approved = 0")->fetchColumn();
$counts['flowers']     = (int)$db->query("SELECT COUNT(*) FROM deposited_flowers")->fetchColumn();

include '../includes/header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
    <?= htmlspecialchars($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4">
    <?php foreach ($tabTypes as $t): ?>
    <li class="nav-item">
        <a class="nav-link <?= $type === $t ? 'active' : '' ?>" href="moderate.php?type=<?= $t ?>">
            <?= ucfirst($t) ?>
            <?php if ($counts[$t] > 0): ?>
            <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;"><?= $counts[$t] ?></span>
            <?php endif; ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<?php if ($type === 'prayers'): ?>
<!-- ===== PRAYERS ===== -->
<?php
$prayers = $db->query("
    SELECT p.*, u.full_name, u.username
    FROM prayers p JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC LIMIT 50
")->fetchAll();
?>
<div class="admin-card">
    <div class="admin-card-header"><h6>🙏 Prayers (<?= count($prayers) ?>)</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Title</th><th>Category</th><th>Visibility</th><th>By</th><th>Date</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($prayers as $p): ?>
            <tr>
                <td>
                    <div class="small fw-semibold"><?= htmlspecialchars($p['title']) ?></div>
                    <div class="text-muted" style="font-size:0.72rem;"><?= htmlspecialchars(mb_substr($p['prayer_text'],0,80)) ?>…</div>
                </td>
                <td class="small"><?= htmlspecialchars($p['category']) ?></td>
                <td><span class="badge <?= $p['visibility']==='public'?'bg-success':'bg-secondary' ?>"><?= $p['visibility'] ?></span></td>
                <td class="small"><?= htmlspecialchars($p['full_name']) ?></td>
                <td class="small text-muted"><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Delete this prayer?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="item_type" value="prayers">
                        <input type="hidden" name="item_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($type === 'testimonies'): ?>
<!-- ===== TESTIMONIES ===== -->
<?php
$testimonies = $db->query("
    SELECT t.*, u.full_name
    FROM testimonies t JOIN users u ON t.user_id = u.id
    ORDER BY t.is_approved ASC, t.created_at DESC LIMIT 50
")->fetchAll();
?>
<div class="admin-card">
    <div class="admin-card-header"><h6>✍️ Testimonies</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Title</th><th>By</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($testimonies as $t): ?>
            <tr>
                <td>
                    <div class="small fw-semibold"><?= htmlspecialchars($t['title']) ?></div>
                    <div class="text-muted" style="font-size:0.72rem;"><?= htmlspecialchars(mb_substr($t['testimony_text'],0,80)) ?>…</div>
                </td>
                <td class="small"><?= htmlspecialchars($t['full_name']) ?></td>
                <td>
                    <span class="badge <?= $t['is_approved'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= $t['is_approved'] ? 'Approved' : 'Pending' ?>
                    </span>
                </td>
                <td class="small text-muted"><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <?php if (!$t['is_approved']): ?>
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="item_type" value="testimonies">
                            <input type="hidden" name="item_id" value="<?= $t['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button class="btn btn-sm btn-success" style="font-size:0.75rem;">Approve</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" onsubmit="return confirm('Delete?');">
                            <?= csrfField() ?>
                            <input type="hidden" name="item_type" value="testimonies">
                            <input type="hidden" name="item_id" value="<?= $t['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($type === 'guestbook'): ?>
<!-- ===== GUESTBOOK ===== -->
<?php
$entries = $db->query("SELECT * FROM guestbook ORDER BY is_approved ASC, created_at DESC LIMIT 50")->fetchAll();
?>
<div class="admin-card">
    <div class="admin-card-header"><h6>📖 Guestbook</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Country</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($entries as $e): ?>
            <tr>
                <td class="small fw-semibold"><?= htmlspecialchars($e['guest_name']) ?></td>
                <td class="small"><?= htmlspecialchars($e['country'] ?? '—') ?></td>
                <td class="small"><?= htmlspecialchars(mb_substr($e['message'],0,80)) ?>…</td>
                <td>
                    <span class="badge <?= $e['is_approved'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= $e['is_approved'] ? 'Approved' : 'Pending' ?>
                    </span>
                </td>
                <td class="small text-muted"><?= date('M j, Y', strtotime($e['created_at'])) ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <?php if (!$e['is_approved']): ?>
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="item_type" value="guestbook">
                            <input type="hidden" name="item_id" value="<?= $e['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button class="btn btn-sm btn-success" style="font-size:0.75rem;">Approve</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" onsubmit="return confirm('Delete?');">
                            <?= csrfField() ?>
                            <input type="hidden" name="item_type" value="guestbook">
                            <input type="hidden" name="item_id" value="<?= $e['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($type === 'flowers'): ?>
<!-- ===== FLOWERS ===== -->
<?php
$flowers = $db->query("
    SELECT df.*, fc.flower_name, u.full_name
    FROM deposited_flowers df
    JOIN flowers_catalog fc ON df.flower_id = fc.id
    JOIN users u ON df.user_id = u.id
    ORDER BY df.created_at DESC LIMIT 50
")->fetchAll();
?>
<div class="admin-card">
    <div class="admin-card-header"><h6>🌹 Deposited Flowers (<?= count($flowers) ?>)</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Flower</th><th>Member</th><th>Message</th><th>Date</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($flowers as $f): ?>
            <tr>
                <td class="small">🌹 <?= htmlspecialchars($f['flower_name']) ?></td>
                <td class="small"><?= htmlspecialchars($f['full_name']) ?></td>
                <td class="small text-muted"><?= htmlspecialchars($f['message'] ?? '—') ?></td>
                <td class="small text-muted"><?= date('M j, Y', strtotime($f['created_at'])) ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Delete?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="item_type" value="deposited_flowers">
                        <input type="hidden" name="item_id" value="<?= $f['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
