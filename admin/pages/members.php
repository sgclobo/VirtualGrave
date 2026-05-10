<?php
/**
 * Admin — Members Management
 */
define('ADMIN_PAGE', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$pageTitle = 'Members';
$db = getDB();

$msg = '';
$msgType = 'success';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid security token.'; $msgType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';
        $userId = (int)($_POST['user_id'] ?? 0);

        if (in_array($action, ['approve', 'reject', 'delete']) && $userId > 0) {
            if ($action === 'approve') {
                $db->prepare("UPDATE users SET status = 'approved' WHERE id = ?")->execute([$userId]);
                $msg = 'Member approved.';
            } elseif ($action === 'reject') {
                $db->prepare("UPDATE users SET status = 'rejected' WHERE id = ?")->execute([$userId]);
                $msg = 'Member rejected.';
            } elseif ($action === 'delete') {
                $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
                $msg = 'Member deleted.';
            }
        }
    }
}

// Filter
$filter   = $_GET['filter'] ?? 'all';
$search   = trim($_GET['search'] ?? '');
$perPage  = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $perPage;

$where  = '1=1';
$params = [];

if ($filter !== 'all' && in_array($filter, ['pending','approved','rejected'])) {
    $where   .= ' AND status = ?';
    $params[] = $filter;
}
if ($search) {
    $where   .= ' AND (full_name LIKE ? OR email LIKE ? OR username LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$countStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("SELECT * FROM users WHERE $where ORDER BY registered_at DESC LIMIT ? OFFSET ?");
$stmt->execute($params);
$members = $stmt->fetchAll();

include '../includes/header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="d-flex flex-wrap gap-2 align-items-center mb-4">
    <div class="d-flex gap-2">
        <?php foreach (['all','pending','approved','rejected'] as $f): ?>
        <a href="members.php?filter=<?= $f ?>&search=<?= urlencode($search) ?>"
           class="btn btn-sm <?= $filter === $f ? 'btn-dark' : 'btn-outline-secondary' ?> rounded-pill">
            <?= ucfirst($f) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <form class="ms-auto d-flex gap-2" method="GET" action="members.php">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search members…" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-sm btn-outline-secondary">Search</button>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h6>👥 Members <span class="text-muted fw-normal fs-6">(<?= $total ?>)</span></h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Member</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Relationship</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($members)): ?>
            <tr><td colspan="7" class="text-center py-4 text-muted">No members found.</td></tr>
            <?php endif; ?>
            <?php foreach ($members as $member): ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($member['profile_photo']): ?>
                        <?php $photoPath = ltrim($member['profile_photo'], '/'); ?>
                        <?php if (strpos($photoPath, '/') === false) $photoPath = 'avatars/' . $photoPath; ?>
                        <img src="<?= SITE_URL ?>/uploads/<?= htmlspecialchars($photoPath) ?>"
                             class="rounded-circle" width="32" height="32" style="object-fit:cover;" alt="">
                        <?php else: ?>
                        <?php $initial = function_exists('mb_substr') ? strtoupper(mb_substr($member['full_name'], 0, 1)) : strtoupper(substr($member['full_name'], 0, 1)); ?>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:32px;height:32px;background:var(--soft-gold);font-weight:700;font-size:0.8rem;color:var(--deep-blue);">
                            <?= $initial ?>
                        </div>
                        <?php endif; ?>
                        <div>
                            <div class="fw-semibold small"><?= htmlspecialchars($member['full_name']) ?></div>
                            <div class="text-muted" style="font-size:0.7rem;">@<?= htmlspecialchars($member['username']) ?></div>
                        </div>
                    </div>
                </td>
                <td class="small"><?= htmlspecialchars($member['email']) ?></td>
                <td class="small"><?= htmlspecialchars($member['country'] ?? '—') ?></td>
                <td class="small"><?= htmlspecialchars(ucfirst(str_replace('_',' ',$member['relationship'] ?? ''))) ?></td>
                <td>
                    <span class="badge badge-<?= $member['status'] ?> rounded-pill px-2">
                        <?= ucfirst($member['status']) ?>
                    </span>
                </td>
                <td class="small text-muted"><?= date('M j, Y', strtotime($member['registered_at'])) ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <?php if ($member['status'] !== 'approved'): ?>
                        <form method="POST" action="members.php" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button class="btn btn-xs btn-success" title="Approve" style="font-size:0.75rem;padding:2px 8px;">✓</button>
                        </form>
                        <?php endif; ?>
                        <?php if ($member['status'] !== 'rejected'): ?>
                        <form method="POST" action="members.php" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button class="btn btn-xs btn-warning" title="Reject" style="font-size:0.75rem;padding:2px 8px;">✕</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" action="members.php" class="d-inline"
                              onsubmit="return confirm('Delete this member? This cannot be undone.');">
                            <?= csrfField() ?>
                            <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button class="btn btn-xs btn-danger" title="Delete" style="font-size:0.75rem;padding:2px 8px;">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="p-3 d-flex justify-content-center">
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="members.php?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&page=<?= $p ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
