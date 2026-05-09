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
      <?php if (isLoggedIn()): ?>
      <button type="button" class="gallery-filter-btn" data-bs-toggle="modal" data-bs-target="#suggestPhotoModal"
        style="background:rgba(201,168,76,0.2);border-color:rgba(201,168,76,0.5);">
        📸 Suggest Photo
      </button>
      <?php endif; ?>
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

<!-- SUGGEST PHOTO MODAL -->
<div class="modal fade" id="suggestPhotoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:#2a2040;border:1px solid rgba(201,168,76,0.3);">
      <div class="modal-header border-bottom" style="border-color:rgba(201,168,76,0.2)!important;">
        <h5 class="modal-title">📸 Suggest a Photo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="suggestPhotoForm" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label small text-muted-memorial">Photo <span class="text-danger">*</span></label>
            <input type="file" id="photoFile" name="photo" class="form-control form-control-sm" 
                   accept="image/jpeg,image/png,image/webp,image/gif" required
                   style="background:#1a1a2e;border-color:rgba(201,168,76,0.3);color:#faf8f3;">
            <div class="form-text">JPG, PNG, WEBP, or GIF. Max 5MB.</div>
          </div>
          <div class="mb-3">
            <label class="form-label small text-muted-memorial">Caption</label>
            <input type="text" id="photoCaption" name="caption" class="form-control form-control-sm" 
                   maxlength="255" placeholder="A short description…"
                   style="background:#1a1a2e;border-color:rgba(201,168,76,0.3);color:#faf8f3;">
          </div>
          <div class="mb-3">
            <label class="form-label small text-muted-memorial">Category</label>
            <select id="photoCategory" name="category" class="form-select form-select-sm"
                    style="background:#1a1a2e;border-color:rgba(201,168,76,0.3);color:#faf8f3;">
              <option value="childhood">Childhood</option>
              <option value="family">Family</option>
              <option value="work">Work</option>
              <option value="celebrations">Celebrations</option>
              <option value="travels">Travels</option>
              <option value="special" selected>Special Moments</option>
            </select>
          </div>
          <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
          <button type="submit" class="btn btn-memorial w-100">Submit Photo</button>
          <div id="suggestPhotoMessage" class="mt-3 alert d-none" role="alert"></div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('suggestPhotoForm');
  const messageDiv = document.getElementById('suggestPhotoMessage');

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const formData = new FormData(form);
      
      try {
        const response = await fetch(SITE_URL + '/api/suggest_photo.php', {
          method: 'POST',
          body: formData
        });
        
        const data = await response.json();
        
        messageDiv.classList.remove('d-none');
        
        if (data.success) {
          messageDiv.className = 'mt-3 alert alert-success';
          messageDiv.textContent = data.message;
          form.reset();
          setTimeout(() => {
            bootstrap.Modal.getInstance(document.getElementById('suggestPhotoModal')).hide();
            messageDiv.classList.add('d-none');
          }, 2000);
        } else {
          messageDiv.className = 'mt-3 alert alert-danger';
          messageDiv.textContent = data.message;
        }
      } catch (error) {
        messageDiv.className = 'mt-3 alert alert-danger';
        messageDiv.textContent = 'An error occurred. Please try again.';
      }
    });
  }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
