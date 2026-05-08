<?php
/**
 * Guestbook — public memorial book of condolences
 */
require_once '../includes/config.php';
require_once '../includes/functions.php';

logVisit();
$db = getDB();

$success = '';
$error   = '';

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $name    = trim($_POST['guest_name'] ?? '');
        $email   = trim($_POST['guest_email'] ?? '');
        $country = trim($_POST['guest_country'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($message)) {
            $error = 'Please provide your name and a message.';
        } elseif (mb_strlen($message) > 1000) {
            $error = 'Message must be under 1000 characters.';
        } else {
            try {
                $stmt = $db->prepare("
                    INSERT INTO guestbook (guest_name, guest_email, country, message, is_approved, created_at)
                    VALUES (?, ?, ?, ?, 0, NOW())
                ");
                $stmt->execute([$name, $email ?: null, $country ?: null, $message]);

                $autoApprove = getSetting('auto_approve_guestbook', '1');
                if ($autoApprove === '1') {
                    $id = $db->lastInsertId();
                    $db->prepare("UPDATE guestbook SET is_approved = 1 WHERE id = ?")->execute([$id]);
                    $success = 'Your message has been added to the guestbook. Thank you for visiting.';
                } else {
                    $success = 'Your message is awaiting approval and will appear shortly. Thank you.';
                }
            } catch (PDOException $e) {
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}

// Fetch entries
$perPage = 15;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$countStmt = $db->prepare("SELECT COUNT(*) FROM guestbook WHERE is_approved = 1");
$countStmt->execute();
$total      = (int)$countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $db->prepare("SELECT * FROM guestbook WHERE is_approved = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$perPage, $offset]);
$entries = $stmt->fetchAll();

$siteTitle = getSetting('site_title', 'In Loving Memory');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guestbook — <?= htmlspecialchars($siteTitle) ?></title>
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
<section class="page-hero" style="background: linear-gradient(135deg, #1c2c3c 0%, #2d2020 100%); padding:100px 0 60px;">
    <div class="container text-center text-white">
        <div class="mb-3" style="font-size:3rem;">📖</div>
        <h1 class="display-5 fw-light" style="font-family:'Cormorant Garamond',serif;">Memorial Guestbook</h1>
        <p class="lead fw-light opacity-75">Leave a message of love, comfort and remembrance</p>
    </div>
</section>

<div class="container py-5" style="max-width:900px;">

    <!-- Sign the Guestbook -->
    <div class="guestbook-form-card mb-5 p-4 p-md-5 reveal">
        <h3 class="text-center mb-4" style="font-family:'Cormorant Garamond',serif;">Sign the Guestbook</h3>

        <?php if ($success): ?>
        <div class="alert alert-memorial-success mb-4">
            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="guestbook.php">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label memorial-label">Your Name <span class="text-danger">*</span></label>
                    <input type="text" name="guest_name" class="form-control memorial-input"
                           placeholder="Enter your full name" maxlength="150" required
                           value="<?= htmlspecialchars($_POST['guest_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label memorial-label">Email <small class="text-muted">(optional, not displayed)</small></label>
                    <input type="email" name="guest_email" class="form-control memorial-input"
                           placeholder="your@email.com" maxlength="150"
                           value="<?= htmlspecialchars($_POST['guest_email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label memorial-label">Country <small class="text-muted">(optional)</small></label>
                    <input type="text" name="guest_country" class="form-control memorial-input"
                           placeholder="Your country" maxlength="100"
                           value="<?= htmlspecialchars($_POST['guest_country'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label memorial-label">Your Message <span class="text-danger">*</span></label>
                    <textarea name="message" class="form-control memorial-input" rows="5"
                              placeholder="Write your message of remembrance, comfort or love…"
                              maxlength="1000" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    <div class="text-end">
                        <small class="text-muted char-counter" data-textarea="message" data-max="1000">0 / 1000</small>
                    </div>
                </div>
                <div class="col-12 text-center mt-2">
                    <button type="submit" class="btn btn-memorial px-5 py-2">
                        📖 Sign the Guestbook
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Guestbook Entries -->
    <h3 class="text-center mb-4" style="font-family:'Cormorant Garamond',serif;">
        Messages of Love <small class="text-muted fs-6">(<?= $total ?>)</small>
    </h3>

    <?php if (empty($entries)): ?>
    <div class="text-center py-5 opacity-75">
        <div style="font-size:2.5rem;" class="mb-3">🕊️</div>
        <p class="lead fw-light">No messages yet. Be the first to sign.</p>
    </div>
    <?php else: ?>

    <div class="guestbook-entries">
        <?php foreach ($entries as $entry): ?>
        <div class="guestbook-entry reveal">
            <div class="guestbook-entry-header">
                <div class="guestbook-avatar">
                    <?= strtoupper(mb_substr($entry['guest_name'], 0, 1)) ?>
                </div>
                <div class="guestbook-meta">
                    <div class="guestbook-name"><?= htmlspecialchars($entry['guest_name']) ?></div>
                    <div class="guestbook-info text-muted small">
                        <?= $entry['country'] ? htmlspecialchars($entry['country']) . ' · ' : '' ?>
                        <?= date('F j, Y', strtotime($entry['created_at'])) ?>
                    </div>
                </div>
            </div>
            <div class="guestbook-message">
                <?= nl2br(htmlspecialchars($entry['message'])) ?>
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
                <a class="page-link" href="guestbook.php?page=<?= $p ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/petals.js"></script>
<script src="../assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Character counters
    document.querySelectorAll('.char-counter').forEach(counter => {
        const ta = document.querySelector(`[name="${counter.dataset.textarea}"]`);
        const max = parseInt(counter.dataset.max);
        if (!ta) return;
        const update = () => {
            counter.textContent = `${ta.value.length} / ${max}`;
            counter.style.color = ta.value.length > max * 0.9 ? 'var(--candle-orange)' : '';
        };
        ta.addEventListener('input', update);
        update();
    });
    Memorial.initScrollReveal();
});
</script>
</body>
</html>
