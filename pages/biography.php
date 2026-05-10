<?php
/**
 * IN LOVING MEMORY — Biography Page
 */
require_once __DIR__ . '/../includes/functions.php';
logVisit('biography');

$pdo      = getDB();
$sections = $pdo->query('SELECT * FROM biography WHERE is_active=1 ORDER BY section_order ASC')->fetchAll();

$deceasedName = getSetting('deceased_name');
$born = getSetting('deceased_born');
$died = getSetting('deceased_died');
$bornFull = $born ? date('F j, Y', strtotime($born)) : '';
$diedFull = $died ? date('F j, Y', strtotime($died)) : '';

$pageTitle  = 'Biography';
$activePage = 'bio';

// Helper to render icon - emojis directly, or map to Bootstrap Icons
$renderIcon = function($icon) {
    $iconMap = ['heart'=>'bi-heart','book'=>'bi-book','briefcase'=>'bi-briefcase','home'=>'bi-house','church'=>'bi-building','star'=>'bi-star'];
    // If it looks like an emoji (2+ chars or contains special unicode), render directly
    if (strlen($icon) > 2 || preg_match('/[^\x00-\x7F]/', $icon)) {
        return ['type' => 'emoji', 'value' => $icon];
    }
    // Otherwise map it to a Bootstrap Icon class
    $class = $iconMap[$icon] ?? 'bi-star';
    return ['type' => 'bs-icon', 'value' => $class];
};

include __DIR__ . '/../includes/header.php';
?>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>

<!-- PAGE HERO -->
<section class="bio-header">
  <div class="container-memorial text-center">
    <div class="hero-portrait-wrap mx-auto mb-4">
      <img src="/assets/img/hercio1.jpeg" alt="<?= e($deceasedName) ?>" class="hero-portrait">
      <span class="hero-glow-ring" aria-hidden="true"></span>
    </div>
    <span class="section-eyebrow">In Memoriam</span>
    <h1 class="bio-name"><?= e($deceasedName) ?></h1>
    <p class="bio-dates"><?= e($bornFull) ?> &mdash; <?= e($diedFull) ?></p>
    <div class="hero-divider mt-3">✝</div>
    <p class="hero-quote" style="max-width:500px;margin:0 auto;color:rgba(250,248,243,0.65);">
      "A life measured not by its duration, but by its depth of love."
    </p>
  </div>
</section>

<!-- BIOGRAPHY SECTIONS -->
<section class="section-pad section-light">
  <div class="container-memorial">

    <?php if (empty($sections)): ?>
    <p class="text-center text-muted-memorial fst-italic">Biography coming soon…</p>
    <?php else: ?>

    <?php foreach ($sections as $i => $sec):
      $icon = $renderIcon($sec['icon']);
    ?>
    <div class="bio-section reveal reveal-delay-<?= min($i+1,6) ?>">
      <div class="bio-section-icon">
        <?php if ($icon['type'] === 'emoji'): ?>
          <span style="font-size: 2rem; display: block;"><?= htmlspecialchars($icon['value']) ?></span>
        <?php else: ?>
          <i class="bi <?= $icon['value'] ?>"></i>
        <?php endif; ?>
      </div>
      <h2 class="bio-section-title"><?= e($sec['section_title']) ?></h2>
      <div class="bio-section-text">
        <?php foreach (explode("\n", $sec['section_content']) as $para): ?>
          <?php if (trim($para)): ?><p><?= e(trim($para)) ?></p><?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <?php if ($i < count($sections)-1): ?>
    <div class="bio-section-divider"></div>
    <?php endif; ?>
    <?php endforeach; ?>

    <?php endif; ?>

  </div>
</section>

<!-- NAVIGATION LINKS -->
<section style="background:var(--ivory-dark);padding:3rem 0;text-align:center;">
  <div class="container-memorial">
    <p class="fst-italic text-muted-memorial mb-3">Continue exploring the life of <?= e($deceasedName) ?></p>
    <div class="d-flex flex-wrap gap-2 justify-content-center">
      <a href="<?= SITE_URL ?>/pages/timeline.php" class="btn-memorial btn-primary-memorial">
        <i class="bi bi-clock-history"></i> View Timeline
      </a>
      <a href="<?= SITE_URL ?>/pages/gallery.php" class="btn-memorial btn-outline-memorial" style="color:var(--deep-blue);border-color:var(--ivory-deeper);">
        <i class="bi bi-images"></i> View Gallery
      </a>
      <a href="<?= SITE_URL ?>/pages/memorial.php" class="btn-memorial btn-outline-memorial" style="color:var(--deep-blue);border-color:var(--ivory-deeper);">
        <i class="bi bi-flower1"></i> Visit Memorial
      </a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
