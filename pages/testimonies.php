<?php
/**
 * Testimonies — full listing page
 */
require_once '../includes/config.php';
require_once '../includes/functions.php';

logVisit();
$db = getDB();

$perPage = 9;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$countStmt = $db->prepare("SELECT COUNT(*) FROM testimonies WHERE is_approved = 1");
$countStmt->execute();
$total      = (int)$countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $db->prepare("
    SELECT t.*, u.full_name, u.username, u.avatar, u.country, u.relationship
    FROM testimonies t
    JOIN users u ON t.user_id = u.id
    WHERE t.is_approved = 1
    ORDER BY t.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$perPage, $offset]);
$testimonies = $stmt->fetchAll();

$siteTitle = getSetting('site_title', 'In Loving Memory');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memories & Testimonies — <?= htmlspecialchars($siteTitle) ?></title>
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

<!-- Hero -->
<section class="page-hero" style="background: linear-gradient(135deg, #3a2d20 0%, #2a2040 100%); padding:100px 0 60px;">
    <div class="container text-center text-white">
        <div class="mb-3" style="font-size:3rem;">✍️</div>
        <h1 class="display-5 fw-light" style="font-family:'Cormorant Garamond',serif;">Memories & Testimonies</h1>
        <p class="lead fw-light opacity-75">Stories, memories and words shared with love</p>
    </div>
</section>

<div class="container py-5">

    <!-- Stats -->
    <div class="text-center mb-5 opacity-75">
        <small class="text-muted"><?= $total ?> <?= $total === 1 ? 'testimony' : 'testimonies' ?> shared</small>
    </div>

    <?php if (empty($testimonies)): ?>
    <div class="text-center py-5 opacity-75">
        <div style="font-size:3rem;" class="mb-3">📖</div>
        <p class="lead fw-light">No testimonies have been shared yet.</p>
        <?php if (isLoggedIn()): ?>
        <a href="memorial.php" class="btn btn-memorial mt-3">Share Your Memory</a>
        <?php else: ?>
        <a href="login.php" class="btn btn-memorial mt-3">Sign In to Share</a>
        <?php endif; ?>
    </div>
    <?php else: ?>

    <div class="row g-4">
        <?php foreach ($testimonies as $t): ?>
        <div class="col-md-6 col-lg-4 reveal">
            <div class="testimony-card h-100">
                <?php if ($t['image_path']): ?>
                <div class="testimony-image-wrap">
                    <img src="../uploads/testimonies/<?= htmlspecialchars($t['image_path']) ?>"
                         class="testimony-img" alt="" loading="lazy">
                </div>
                <?php endif; ?>

                <div class="testimony-body">
                    <h5 class="testimony-title"><?= htmlspecialchars($t['title']) ?></h5>
                    <p class="testimony-excerpt">
                        <?= nl2br(htmlspecialchars(mb_substr($t['testimony_text'], 0, 220))) ?>
                        <?= mb_strlen($t['testimony_text']) > 220 ? '…' : '' ?>
                    </p>
                    <?php if (mb_strlen($t['testimony_text']) > 220): ?>
                    <button class="btn btn-link btn-sm p-0 testimony-read-more"
                            data-title="<?= htmlspecialchars($t['title']) ?>"
                            data-text="<?= htmlspecialchars($t['testimony_text']) ?>"
                            data-img="<?= $t['image_path'] ? '../uploads/testimonies/'.htmlspecialchars($t['image_path']) : '' ?>">
                        Read full memory
                    </button>
                    <?php endif; ?>
                </div>

                <div class="testimony-footer">
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($t['avatar']): ?>
                        <img src="../uploads/avatars/<?= htmlspecialchars($t['avatar']) ?>"
                             class="rounded-circle" width="32" height="32" style="object-fit:cover;" alt="">
                        <?php else: ?>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:32px;height:32px;background:var(--soft-gold);color:var(--deep-blue);font-weight:700;font-size:0.8rem;">
                            <?= strtoupper(mb_substr($t['full_name'] ?? $t['username'], 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                        <div>
                            <div class="fw-semibold small"><?= htmlspecialchars($t['full_name'] ?? $t['username']) ?></div>
                            <div class="text-muted" style="font-size:0.7rem;">
                                <?= htmlspecialchars(ucfirst($t['relationship'] ?? '')) ?>
                                <?= $t['country'] ? '· ' . htmlspecialchars($t['country']) : '' ?>
                            </div>
                        </div>
                        <div class="ms-auto text-muted small">
                            <?= date('M j, Y', strtotime($t['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-5 d-flex justify-content-center">
        <ul class="pagination pagination-memorial">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="testimonies.php?page=<?= $p ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <?php endif; ?>

    <div class="text-center mt-5 pt-3">
        <p class="text-muted fw-light">Do you have a memory to share?</p>
        <?php if (isLoggedIn()): ?>
        <a href="memorial.php#testimonies" class="btn btn-memorial px-5">✍️ Share Your Memory</a>
        <?php else: ?>
        <a href="login.php" class="btn btn-memorial px-5">Sign In to Share</a>
        <?php endif; ?>
    </div>
</div>

<!-- Testimony Modal -->
<div class="modal fade" id="testimonyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content memorial-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="testimonyModalTitle" style="font-family:'Cormorant Garamond',serif;"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <img id="testimonyModalImg" src="" alt="" class="img-fluid rounded mb-4 d-none">
                <div id="testimonyModalText" style="white-space:pre-line;line-height:1.9;font-family:'Crimson Pro',serif;font-size:1.1rem;"></div>
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
    document.querySelectorAll('.testimony-read-more').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('testimonyModalTitle').textContent = btn.dataset.title;
            document.getElementById('testimonyModalText').textContent  = btn.dataset.text;
            const img = document.getElementById('testimonyModalImg');
            if (btn.dataset.img) {
                img.src = btn.dataset.img;
                img.classList.remove('d-none');
            } else {
                img.classList.add('d-none');
            }
            new bootstrap.Modal(document.getElementById('testimonyModal')).show();
        });
    });
    Memorial.initScrollReveal();
});
</script>
</body>
</html>
