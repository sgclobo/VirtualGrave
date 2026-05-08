<?php
/**
 * IN LOVING MEMORY — Gallery Page
 */
require_once __DIR__ . '/../includes/functions.php';
logVisit('gallery');

$pdo   = getDB();
$items = $pdo->query('SELECT * FROM gallery WHERE is_active=1 ORDER BY sort_order, uploaded_at DESC')->fetchAll();

$categories = [
  'all'          => 'All',
  'childhood'    => 'Childhood',
  'family'       => 'Family',
  'work'         => 'Work',
  'celebrations' => 'Celebrations',
  'travels'      => 'Travels',
  'special'      => 'Special Moments',
];

$deceasedName = getSetting('deceased_name');
$pageTitle    = 'Gallery';
$activePage   = 'gallery';
include __DIR__ . '/../includes/header.php';
?>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>

<!-- HERO -->
<div class="page-hero">
  <div class="container-memorial">
    <span class="section-eyebrow" style="color:rgba(201,168,76,0.7);">Memories</span>
    <h1 class="page-hero-title">Photo Gallery</h1>
    <p class="page-hero-subtitle">Moments preserved forever in loving memory</p>
  </div>
</div>

<!-- GALLERY -->
<section class="section-pad section-light">
  <div class="container-memorial">

    <!-- Filters -->
    <div class="gallery-filters reveal">
      <?php foreach ($categories as $key => $label): ?>
      <button class="gallery-filter-btn <?= $key === 'all' ? 'active' : '' ?>"
        data-filter="<?= $key ?>">
        <?= e($label) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <?php if (empty($items)): ?>
    <p class="text-center text-muted-memorial fst-italic py-5">Gallery photos coming soon…</p>
    <?php else: ?>
    <!-- Gallery Grid -->
    <div class="gallery-grid">
      <?php foreach ($items as $item): ?>
      <div class="gallery-item reveal" data-category="<?= e($item['category']) ?>"
           data-lightbox
           data-src="<?= SITE_URL ?>/uploads/<?= e($item['file_path']) ?>"
           data-title="<?= e($item['title'] ?? '') ?>">
        <?php if ($item['file_type'] === 'photo'): ?>
        <img src="<?= SITE_URL ?>/uploads/<?= e($item['file_path']) ?>"
             alt="<?= e($item['title'] ?? '') ?>"
             loading="lazy"
             onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.jpg'">
        <?php else: ?>
        <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background:#1a1a1a;">
          <i class="bi bi-play-circle" style="font-size:3rem;color:rgba(201,168,76,0.7);"></i>
        </div>
        <?php endif; ?>

        <div class="gallery-overlay">
          <p class="gallery-item-title"><?= e($item['title'] ?? '') ?></p>
          <?php if ($item['description']): ?>
          <p style="color:rgba(250,248,243,0.7);font-size:0.8rem;margin:0;"><?= e($item['description']) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- LIGHTBOX -->
<div class="lightbox-overlay" id="lightboxOverlay">
  <button class="lightbox-close" aria-label="Close">×</button>
  <div class="lightbox-inner">
    <img class="lightbox-img" id="lightboxImg" src="" alt="">
    <p class="lightbox-caption" id="lightboxCaption"></p>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
