<?php
/**
 * Prayers — full listing page
 */
require_once '../includes/config.php';
require_once '../includes/functions.php';

logVisit();
$db = getDB();

// Filter by category
$category = trim($_GET['category'] ?? '');
$validCategories = ['Peace', 'Gratitude', 'Healing', 'Family', 'Eternal Rest'];
if (!in_array($category, $validCategories)) $category = '';

// Pagination
$perPage = 12;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$where  = "p.visibility = 'public'";
$params = [];
if ($category) {
    $where   .= " AND p.category = ?";
    $params[] = $category;
}

$countStmt = $db->prepare("SELECT COUNT(*) FROM prayers p WHERE $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT p.*, u.full_name, u.username, u.avatar, u.country
    FROM prayers p
    JOIN users u ON p.user_id = u.id
    WHERE $where
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$prayers = $stmt->fetchAll();

$categoryIcons = [
    'Peace'        => ['icon' => '☮️', 'color' => 'var(--muted-green)'],
    'Gratitude'    => ['icon' => '🙏', 'color' => 'var(--soft-gold)'],
    'Healing'      => ['icon' => '💛', 'color' => 'var(--candle-orange)'],
    'Family'       => ['icon' => '❤️', 'color' => '#c0697a'],
    'Eternal Rest' => ['icon' => '✨', 'color' => 'var(--deep-blue)'],
];

$siteTitle = getSetting('site_title', 'In Loving Memory');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prayers & Intentions — <?= htmlspecialchars($siteTitle) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=Crimson+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    <script>const SITE_URL = '<?= SITE_URL ?>';</script>
</head>
<body>
<?php include '../includes/header.php'; ?>

<!-- Page Hero -->
<section class="page-hero" style="background: linear-gradient(135deg, #2d3f2d 0%, #1a2a3a 100%); padding: 100px 0 60px;">
    <div class="container text-center text-white">
        <div class="mb-3" style="font-size: 3rem;">🙏</div>
        <h1 class="display-5 fw-light" style="font-family:'Cormorant Garamond',serif;">Prayers & Intentions</h1>
        <p class="lead fw-light opacity-75">Words of love carried on wings of prayer</p>
    </div>
</section>

<div class="container py-5">

    <!-- Category Filters -->
    <div class="text-center mb-5">
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="prayers.php" class="btn <?= !$category ? 'btn-memorial' : 'btn-outline-secondary' ?> btn-sm rounded-pill px-4">
                All Prayers
            </a>
            <?php foreach ($validCategories as $cat): ?>
            <?php $ci = $categoryIcons[$cat]; ?>
            <a href="prayers.php?category=<?= urlencode($cat) ?>"
               class="btn <?= $category === $cat ? 'btn-memorial' : 'btn-outline-secondary' ?> btn-sm rounded-pill px-4">
                <?= $ci['icon'] ?> <?= $cat ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Stats -->
    <div class="text-center mb-4 opacity-75">
        <small class="text-muted">
            <?= $total ?> <?= $total === 1 ? 'prayer' : 'prayers' ?> offered
            <?= $category ? "in <strong>$category</strong>" : '' ?>
        </small>
    </div>

    <?php if (empty($prayers)): ?>
    <div class="text-center py-5 opacity-75">
        <div style="font-size:3rem;" class="mb-3">🕊️</div>
        <p class="lead fw-light">No prayers yet in this category.</p>
        <?php if (isLoggedIn()): ?>
        <a href="../pages/memorial.php" class="btn btn-memorial mt-3">Be the First to Pray</a>
        <?php else: ?>
        <a href="login.php" class="btn btn-memorial mt-3">Sign In to Pray</a>
        <?php endif; ?>
    </div>
    <?php else: ?>

    <!-- Prayer Cards Grid -->
    <div class="row g-4">
        <?php foreach ($prayers as $prayer): ?>
        <?php $ci = $categoryIcons[$prayer['category']] ?? ['icon'=>'🙏','color'=>'var(--soft-gold)']; ?>
        <div class="col-md-6 col-lg-4 reveal">
            <div class="prayer-card h-100" style="--card-accent: <?= $ci['color'] ?>;">
                <div class="prayer-card-header">
                    <span class="prayer-category-badge">
                        <?= $ci['icon'] ?> <?= htmlspecialchars($prayer['category']) ?>
                    </span>
                    <span class="prayer-date text-muted small">
                        <?= date('M j, Y', strtotime($prayer['created_at'])) ?>
                    </span>
                </div>
                <h5 class="prayer-title"><?= htmlspecialchars($prayer['title']) ?></h5>
                <p class="prayer-excerpt">
                    <?= nl2br(htmlspecialchars(mb_substr($prayer['prayer_text'], 0, 200))) ?>
                    <?= mb_strlen($prayer['prayer_text']) > 200 ? '…' : '' ?>
                </p>
                <?php if (mb_strlen($prayer['prayer_text']) > 200): ?>
                <button class="btn btn-link btn-sm p-0 read-more-btn"
                        data-prayer="<?= htmlspecialchars($prayer['prayer_text']) ?>"
                        data-title="<?= htmlspecialchars($prayer['title']) ?>">
                    Read full prayer
                </button>
                <?php endif; ?>
                <div class="prayer-author mt-3 pt-3 border-top">
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($prayer['avatar']): ?>
                        <img src="../uploads/avatars/<?= htmlspecialchars($prayer['avatar']) ?>" class="rounded-circle" width="28" height="28" style="object-fit:cover;" alt="">
                        <?php else: ?>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:28px;height:28px;background:var(--soft-gold);font-size:0.75rem;color:var(--deep-blue);font-weight:600;">
                            <?= strtoupper(mb_substr($prayer['full_name'] ?? $prayer['username'], 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                        <div>
                            <div class="fw-semibold small"><?= htmlspecialchars($prayer['full_name'] ?? $prayer['username']) ?></div>
                            <?php if ($prayer['country']): ?>
                            <div class="text-muted" style="font-size:0.7rem;"><?= htmlspecialchars($prayer['country']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-5 d-flex justify-content-center" aria-label="Prayers pagination">
        <ul class="pagination pagination-memorial">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="prayers.php?category=<?= urlencode($category) ?>&page=<?= $p ?>">
                    <?= $p ?>
                </a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <?php endif; ?>

    <!-- CTA -->
    <div class="text-center mt-5 pt-3">
        <p class="text-muted fw-light">Would you like to offer a prayer?</p>
        <?php if (isLoggedIn()): ?>
        <a href="../pages/memorial.php#prayers" class="btn btn-memorial px-5">🙏 Say a Prayer</a>
        <?php else: ?>
        <a href="login.php" class="btn btn-memorial px-5">Sign In to Pray</a>
        <?php endif; ?>
    </div>
</div>

<!-- Prayer Full-Text Modal -->
<div class="modal fade" id="prayerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content memorial-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="prayerModalTitle" style="font-family:'Cormorant Garamond',serif;"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div id="prayerModalText" class="prayer-full-text" style="white-space:pre-line; line-height:1.9; font-family:'Crimson Pro',serif; font-size:1.1rem;"></div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/petals.js"></script>
<script src="../assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Read-more buttons → open modal
    document.querySelectorAll('.read-more-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('prayerModalTitle').textContent = btn.dataset.title;
            document.getElementById('prayerModalText').textContent  = btn.dataset.prayer;
            new bootstrap.Modal(document.getElementById('prayerModal')).show();
        });
    });

    // Scroll reveal
    Memorial.initScrollReveal();
});
</script>
</body>
</html>
