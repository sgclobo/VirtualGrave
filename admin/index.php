<?php
/**
 * Admin Dashboard
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

define('ADMIN_PAGE', true);
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin();

$pageTitle = 'Dashboard';
$db = null;
$dbError = null;

// Gather stats
$stats = array_fill_keys([
    'total_members','pending_members','total_flowers','total_candles',
    'total_prayers','pending_testimonies','total_testimonies',
    'total_visits','today_visits','guestbook_pending',
], 0);
$recentMembers = [];
$recentPrayers = [];
$visitData     = [];

try {
    $db = getDB();

    $queries = [
        'total_members'       => "SELECT COUNT(*) FROM users WHERE status = 'approved'",
        'pending_members'     => "SELECT COUNT(*) FROM users WHERE status = 'pending'",
        'total_flowers'       => "SELECT COUNT(*) FROM deposited_flowers",
        'total_candles'       => "SELECT COUNT(*) FROM lit_candles",
        'total_prayers'       => "SELECT COUNT(*) FROM prayers",
        'pending_testimonies' => "SELECT COUNT(*) FROM testimonies WHERE is_approved = 0",
        'total_testimonies'   => "SELECT COUNT(*) FROM testimonies WHERE is_approved = 1",
        'total_visits'        => "SELECT COUNT(*) FROM visit_log",
        'today_visits'        => "SELECT COUNT(*) FROM visit_log WHERE DATE(visited_at) = CURDATE()",
        'guestbook_pending'   => "SELECT COUNT(*) FROM guestbook WHERE is_approved = 0",
    ];
    foreach ($queries as $key => $sql) {
        $stats[$key] = (int)$db->query($sql)->fetchColumn();
    }

    // Recent members
    $recentMembers = $db->query("SELECT full_name, username, country, status, registered_at AS created_at FROM users ORDER BY registered_at DESC LIMIT 5")->fetchAll();

    // Recent prayers (moderation)
    $recentPrayers = $db->query("SELECT p.title, p.category, p.created_at, u.full_name FROM prayers p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5")->fetchAll();

    // Visits over last 7 days
    $visitData = $db->query("
        SELECT DATE(visited_at) as visit_date, COUNT(*) as cnt
        FROM visit_log
        WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(visited_at)
        ORDER BY visit_date ASC
    ")->fetchAll();
} catch (Throwable $e) {
    error_log('Admin dashboard error: ' . $e->getMessage());
    $dbError = 'A dashboard error occurred. Please retry in a moment.';
}

include 'includes/header.php';
?>

<?php if ($dbError): ?>
<div class="alert alert-danger border-0 rounded-3 mb-4">
    <strong>Dashboard Error:</strong> <?= $dbError ?>
</div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['label'=>'Approved Members',   'value'=>$stats['total_members'],       'icon'=>'👥', 'color'=>'#3b82f6'],
        ['label'=>'Pending Approval',   'value'=>$stats['pending_members'],      'icon'=>'⏳', 'color'=>'#f59e0b'],
        ['label'=>'Flowers Deposited',  'value'=>$stats['total_flowers'],        'icon'=>'🌹', 'color'=>'#ec4899'],
        ['label'=>'Candles Lit',        'value'=>$stats['total_candles'],        'icon'=>'🕯️', 'color'=>'#f97316'],
        ['label'=>'Prayers Offered',    'value'=>$stats['total_prayers'],        'icon'=>'🙏', 'color'=>'#8b5cf6'],
        ['label'=>'Testimonies',        'value'=>$stats['total_testimonies'],    'icon'=>'✍️', 'color'=>'#14b8a6'],
        ['label'=>'Today\'s Visits',    'value'=>$stats['today_visits'],         'icon'=>'👁️', 'color'=>'#22c55e'],
        ['label'=>'Total Visits',       'value'=>$stats['total_visits'],         'icon'=>'📊', 'color'=>'#64748b'],
    ];
    foreach ($statCards as $card): ?>
    <div class="col-6 col-sm-4 col-md-3">
        <div class="admin-stat-card" style="border-left-color: <?= $card['color'] ?>;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value"><?= number_format($card['value']) ?></div>
                    <div class="stat-label"><?= $card['label'] ?></div>
                </div>
                <div class="stat-icon"><?= $card['icon'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Alerts -->
<?php if ($stats['pending_members'] > 0 || $stats['pending_testimonies'] > 0 || $stats['guestbook_pending'] > 0): ?>
<div class="alert alert-warning border-0 rounded-3 mb-4 d-flex align-items-center gap-2">
    <span style="font-size:1.3rem;">⚠️</span>
    <div>
        Items awaiting review:
        <?php if ($stats['pending_members'] > 0): ?>
        <a href="pages/members.php?filter=pending" class="fw-bold"><?= $stats['pending_members'] ?> member(s)</a>
        <?php endif; ?>
        <?php if ($stats['pending_testimonies'] > 0): ?>
        · <a href="pages/moderate.php?type=testimonies" class="fw-bold"><?= $stats['pending_testimonies'] ?> testimon<?= $stats['pending_testimonies'] === 1 ? 'y' : 'ies' ?></a>
        <?php endif; ?>
        <?php if ($stats['guestbook_pending'] > 0): ?>
        · <a href="pages/moderate.php?type=guestbook" class="fw-bold"><?= $stats['guestbook_pending'] ?> guestbook message(s)</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Recent Members -->
    <div class="col-md-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6>👥 Recent Registrations</h6>
                <a href="pages/members.php" class="btn btn-sm btn-outline-secondary btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentMembers as $m): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold small"><?= htmlspecialchars($m['full_name']) ?></div>
                                <div class="text-muted" style="font-size:0.7rem;">@<?= htmlspecialchars($m['username']) ?></div>
                            </td>
                            <td class="small"><?= htmlspecialchars($m['country'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-<?= $m['status'] ?> rounded-pill">
                                    <?= ucfirst($m['status']) ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?= date('M j', strtotime($m['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Prayers -->
    <div class="col-md-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6>🙏 Recent Prayers</h6>
                <a href="pages/moderate.php?type=prayers" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Title</th><th>Category</th><th>By</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPrayers as $p): ?>
                        <?php
                            $prayerTitle = $p['title'] ?? '';
                            $shortTitle = function_exists('mb_substr') ? mb_substr($prayerTitle, 0, 30) : substr($prayerTitle, 0, 30);
                            $titleLength = function_exists('mb_strlen') ? mb_strlen($prayerTitle) : strlen($prayerTitle);
                        ?>
                        <tr>
                            <td class="small"><?= htmlspecialchars($shortTitle) ?><?= $titleLength > 30 ? '…' : '' ?></td>
                            <td class="small"><?= htmlspecialchars($p['category']) ?></td>
                            <td class="small"><?= htmlspecialchars($p['full_name']) ?></td>
                            <td class="small text-muted"><?= date('M j', strtotime($p['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Visit Chart (simple CSS bars) -->
<?php if (!empty($visitData)): ?>
<div class="admin-card mt-4">
    <div class="admin-card-header"><h6>📊 Visits — Last 7 Days</h6></div>
    <div class="p-4">
        <?php
        $maxVisits = max(array_column($visitData, 'cnt'));
        foreach ($visitData as $day):
            $pct = $maxVisits > 0 ? round(($day['cnt'] / $maxVisits) * 100) : 0;
        ?>
        <div class="d-flex align-items-center gap-3 mb-2">
            <div style="width:90px;font-size:0.78rem;color:#888;"><?= date('D, M j', strtotime($day['visit_date'])) ?></div>
            <div class="flex-grow-1 bg-light rounded-pill" style="height:14px;overflow:hidden;">
                <div class="rounded-pill" style="height:100%;width:<?= $pct ?>%;background:linear-gradient(90deg,var(--soft-gold),var(--candle-orange));transition:width 0.6s;"></div>
            </div>
            <div style="width:30px;font-size:0.8rem;font-weight:600;"><?= $day['cnt'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
