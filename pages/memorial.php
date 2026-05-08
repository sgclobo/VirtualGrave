<?php
/**
 * IN LOVING MEMORY — Memorial Page (Full Virtual Grave)
 */
require_once __DIR__ . '/../includes/functions.php';
logVisit('memorial');

$pdo  = getDB();
$stats = getMemorialStats();

$deceasedName = getSetting('deceased_name');
$born = getSetting('deceased_born');
$died = getSetting('deceased_died');
$bornYear = $born ? date('Y', strtotime($born)) : '';
$diedYear = $died ? date('Y', strtotime($died)) : '';

// Recent flowers
$flowers = $pdo->query('
    SELECT df.*, fc.flower_name, fc.color, u.full_name, u.country
    FROM deposited_flowers df
    JOIN flowers_catalog fc ON fc.id = df.flower_id
    JOIN users u ON u.id = df.user_id
    WHERE df.is_approved=1
    ORDER BY df.created_at DESC LIMIT 20
')->fetchAll();

// Recent candles
$candles = $pdo->query('
    SELECT lc.*, cc.candle_name, cc.glow_color, u.full_name
    FROM lit_candles lc
    JOIN candles_catalog cc ON cc.id = lc.candle_id
    JOIN users u ON u.id = lc.user_id
    WHERE lc.is_approved=1
    ORDER BY lc.created_at DESC LIMIT 12
')->fetchAll();

// Public prayers
$prayers = $pdo->query('
    SELECT p.*, u.full_name, u.country
    FROM prayers p
    JOIN users u ON u.id = p.user_id
    WHERE p.is_approved=1 AND p.visibility="public"
    ORDER BY p.created_at DESC LIMIT 9
')->fetchAll();

// Testimonies
$testimonies = $pdo->query('
    SELECT t.*, u.full_name, u.country
    FROM testimonies t
    JOIN users u ON u.id = t.user_id
    WHERE t.is_approved=1
    ORDER BY t.created_at DESC LIMIT 6
')->fetchAll();

$flowerEmojis = ['White Rose'=>'🌹','Red Rose'=>'🌹','Lily'=>'💐','Orchid'=>'🌺','Jasmine'=>'🌸','Sunflower'=>'🌻','Carnation'=>'🌷'];
$categoryLabels = ['peace'=>'Peace','gratitude'=>'Gratitude','healing'=>'Healing','family'=>'Family','eternal_rest'=>'Eternal Rest'];

$flowerCatalog = $pdo->query('SELECT * FROM flowers_catalog WHERE is_active=1')->fetchAll();
$candleCatalog = $pdo->query('SELECT * FROM candles_catalog WHERE is_active=1')->fetchAll();

$pageTitle  = 'Memorial';
$activePage = 'memorial';
include __DIR__ . '/../includes/header.php';
?>
<meta name="csrf" content="<?= e(csrfToken()) ?>">
<script>const SITE_URL = '<?= SITE_URL ?>';</script>

<!-- HERO -->
<div class="page-hero">
  <div class="container-memorial">
    <span class="section-eyebrow" style="color:rgba(201,168,76,0.7);">Sacred Space</span>
    <h1 class="page-hero-title">Virtual Memorial</h1>
    <p class="page-hero-subtitle">Light candles · Leave flowers · Share prayers</p>
  </div>
</div>

<!-- STATS -->
<section class="stats-bar">
  <div class="container-memorial">
    <div class="stats-grid">
      <div class="stat-item">
        <span class="stat-icon">🕯</span>
        <span class="stat-number" data-count="<?= $stats['candles'] ?>" id="candlesCount"><?= number_format($stats['candles']) ?></span>
        <span class="stat-label">Candles Lit</span>
      </div>
      <div class="stat-item">
        <span class="stat-icon">🌹</span>
        <span class="stat-number" data-count="<?= $stats['flowers'] ?>" id="flowersCount"><?= number_format($stats['flowers']) ?></span>
        <span class="stat-label">Flowers Left</span>
      </div>
      <div class="stat-item">
        <span class="stat-icon">🙏</span>
        <span class="stat-number" data-count="<?= $stats['prayers'] ?>" id="prayersCount"><?= number_format($stats['prayers']) ?></span>
        <span class="stat-label">Prayers Said</span>
      </div>
      <div class="stat-item">
        <span class="stat-icon">✍</span>
        <span class="stat-number" data-count="<?= $stats['testimonies'] ?>"><?= number_format($stats['testimonies']) ?></span>
        <span class="stat-label">Testimonies</span>
      </div>
    </div>
  </div>
</section>

<!-- VIRTUAL GRAVE -->
<section class="grave-section">
  <div class="grave-bg-glow"></div>
  <div class="container-memorial w-100">
    <div class="grave-scene">

      <div class="grave-flowers" id="graveFlowersArea">
        <?php foreach ($flowers as $f):
          $emoji = $flowerEmojis[$f['flower_name']] ?? '🌸';
        ?>
        <span class="grave-flower-item">
          <?= $emoji ?>
          <span class="flower-tooltip">
            <?= e($f['full_name']) ?><?= $f['country'] ? ' from ' . e($f['country']) : '' ?>
            left <?= e($f['flower_name']) ?>
          </span>
        </span>
        <?php endforeach; ?>
      </div>

      <div class="memorial-stone reveal">
        <span class="stone-cross">✝</span>
        <div class="stone-name"><?= e($deceasedName) ?></div>
        <div class="stone-dates"><?= e($bornYear) ?> — <?= e($diedYear) ?></div>
        <div class="stone-verse">
          "I am the resurrection and the life.<br>
          Whoever believes in me, though he die, yet shall he live."
          <br><small style="opacity:0.5">John 11:25</small>
        </div>
      </div>
      <div class="grave-base"></div>
      <div class="grave-ground"></div>

      <div class="grave-candles" id="graveCandlesArea">
        <?php foreach ($candles as $c): ?>
        <div class="grave-candle" title="<?= e($c['full_name']) ?>: <?= e($c['dedication'] ?: $c['candle_name']) ?>">
          <div class="grave-candle-flame"></div>
          <div class="grave-candle-body" style="box-shadow:0 0 14px <?= e($c['glow_color']) ?>80;"></div>
          <span class="grave-candle-name"><?= e($c['candle_name']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if (isLoggedIn()): ?>
      <div class="grave-actions">
        <button class="btn-grave-action btn-ripple" data-bs-toggle="modal" data-bs-target="#flowerModal">
          🌹 Deposit Flowers
        </button>
        <button class="btn-grave-action btn-gold btn-ripple" data-bs-toggle="modal" data-bs-target="#candleModal">
          🕯 Light Candle
        </button>
        <button class="btn-grave-action btn-ripple" data-bs-toggle="modal" data-bs-target="#prayerModal">
          🙏 Say a Prayer
        </button>
        <button class="btn-grave-action btn-ripple" data-bs-toggle="modal" data-bs-target="#testimonyModal">
          ✍ Leave a Testimony
        </button>
      </div>
      <?php else: ?>
      <div class="grave-actions">
        <p style="color:rgba(250,248,243,0.5);font-style:italic;">
          <a href="<?= SITE_URL ?>/pages/login.php" style="color:var(--gold-light);">Sign in</a> or
          <a href="<?= SITE_URL ?>/pages/register.php" style="color:var(--gold-light);">register</a> to interact
        </p>
      </div>
      <?php endif; ?>

    </div>
  </div>
</section>

<!-- PRAYERS -->
<?php if (!empty($prayers)): ?>
<section class="section-pad section-cream">
  <div class="container-memorial">
    <div class="section-header">
      <span class="section-eyebrow">Community Prayers</span>
      <h2 class="section-title reveal">Prayers Offered</h2>
      <div class="section-line">🙏</div>
    </div>
    <div class="row g-4">
      <?php foreach ($prayers as $i => $p): ?>
      <div class="col-md-4 reveal reveal-delay-<?= ($i%3)+1 ?>">
        <div class="prayer-card h-100">
          <span class="prayer-category-badge"><?= e($categoryLabels[$p['category']] ?? $p['category']) ?></span>
          <?php if ($p['title']): ?>
          <div class="prayer-title"><?= e($p['title']) ?></div>
          <?php endif; ?>
          <p class="prayer-text"><?= e(mb_substr($p['prayer_text'],0,200)) ?><?= strlen($p['prayer_text'])>200?'…':'' ?></p>
          <div class="prayer-meta">
            <div class="prayer-avatar"><?= e(mb_substr($p['full_name'],0,1)) ?></div>
            <span><?= e($p['full_name']) ?></span>
            <?php if ($p['country']): ?><span>· <?= e($p['country']) ?></span><?php endif; ?>
            <span class="ms-auto"><?= date('M j', strtotime($p['created_at'])) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- TESTIMONIES -->
<?php if (!empty($testimonies)): ?>
<section class="section-pad section-light">
  <div class="container-memorial">
    <div class="section-header">
      <span class="section-eyebrow">Shared Memories</span>
      <h2 class="section-title reveal">Testimonies</h2>
      <div class="section-line">✍</div>
    </div>
    <div class="row g-4">
      <?php foreach ($testimonies as $i => $t): ?>
      <div class="col-md-4 reveal reveal-delay-<?= ($i%3)+1 ?>">
        <div class="testimony-card h-100">
          <?php if ($t['image']): ?>
          <img src="<?= SITE_URL ?>/uploads/<?= e($t['image']) ?>" class="testimony-img" alt="" loading="lazy">
          <?php endif; ?>
          <div class="testimony-body">
            <div class="testimony-title"><?= e($t['title']) ?></div>
            <p class="testimony-text"><?= e($t['testimony_text']) ?></p>
            <div class="prayer-meta mt-auto">
              <div class="prayer-avatar"><?= e(mb_substr($t['full_name'],0,1)) ?></div>
              <span><?= e($t['full_name']) ?></span>
              <span class="ms-auto"><?= date('M j, Y', strtotime($t['created_at'])) ?></span>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- RECENT FLOWER MESSAGES -->
<?php $flowerMsgs = array_filter($flowers, fn($f) => !empty($f['message'])); ?>
<?php if (!empty($flowerMsgs)): ?>
<section class="section-pad" style="background:var(--gold-pale);">
  <div class="container-memorial">
    <div class="section-header">
      <span class="section-eyebrow">Left with Flowers</span>
      <h2 class="section-title reveal">Messages of Love</h2>
    </div>
    <div class="row g-3">
      <?php foreach (array_slice($flowerMsgs,0,6) as $f): ?>
      <div class="col-md-4 reveal">
        <div class="guestbook-entry">
          <div class="guestbook-author"><?= e($f['full_name']) ?> left <?= e($f['flower_name']) ?></div>
          <p class="guestbook-text">"<?= e($f['message']) ?>"</p>
          <div class="guestbook-meta"><?= date('M j, Y', strtotime($f['created_at'])) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ─── MODALS ─────────────────────────────────────────────── -->
<?php if (isLoggedIn()):
  $flowerEmojiMap = ['White Rose'=>'🌹','Red Rose'=>'🌹','Lily'=>'💐','Orchid'=>'🌺','Jasmine'=>'🌸','Sunflower'=>'🌻','Carnation'=>'🌷'];
?>

<!-- FLOWER MODAL -->
<div class="modal fade memorial-modal" id="flowerModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">🌹 Deposit Flowers</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="flowerForm" class="memorial-form">
          <?= csrfField() ?>
          <input type="hidden" id="selectedFlowerId" name="flower_id" value="">
          <div class="flower-grid mb-4">
            <?php foreach ($flowerCatalog as $fl):
              $emoji = $flowerEmojiMap[$fl['flower_name']] ?? '🌸';
            ?>
            <div class="flower-option" data-id="<?= $fl['id'] ?>">
              <span class="flower-emoji"><?= $emoji ?></span>
              <div class="flower-name"><?= e($fl['flower_name']) ?></div>
              <div class="flower-meaning"><?= e($fl['symbolic_meaning']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="mb-3">
            <label class="form-label" for="flowerMessage">Personal Message (optional)</label>
            <textarea class="form-control" id="flowerMessage" name="message" rows="3" maxlength="500" placeholder="With love from…"></textarea>
          </div>
          <button type="submit" class="btn-auth">Deposit Flowers 🌹</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- CANDLE MODAL -->
<div class="modal fade memorial-modal" id="candleModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">🕯 Light a Candle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="candleForm" class="memorial-form">
          <?= csrfField() ?>
          <input type="hidden" id="selectedCandleId" name="candle_id" value="">
          <div class="row g-3 mb-4">
            <?php foreach ($candleCatalog as $c): ?>
            <div class="col-6 col-md-3">
              <div class="candle-option" data-id="<?= $c['id'] ?>">
                <div class="candle-option-flame mx-auto mb-1"></div>
                <div class="candle-option-body mx-auto mb-2" style="box-shadow:0 0 10px <?= e($c['glow_color']) ?>50;"></div>
                <div class="flower-name"><?= e($c['candle_name']) ?></div>
                <div class="flower-meaning"><?= e($c['candle_type']) ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="mb-3">
            <label class="form-label" for="candleDedication">Dedication (optional)</label>
            <textarea class="form-control" id="candleDedication" name="dedication" rows="2" maxlength="300" placeholder="This light is for…"></textarea>
          </div>
          <button type="submit" class="btn-auth">Light Candle 🕯</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- PRAYER MODAL -->
<div class="modal fade memorial-modal" id="prayerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">🙏 Say a Prayer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="prayerForm" class="memorial-form">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Title (optional)</label>
            <input type="text" class="form-control" id="prayerTitle" name="title" maxlength="150">
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select" id="prayerCategory" name="category">
              <option value="peace">Peace</option>
              <option value="gratitude">Gratitude</option>
              <option value="healing">Healing</option>
              <option value="family">Family</option>
              <option value="eternal_rest">Eternal Rest</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Your Prayer *</label>
            <textarea class="form-control" id="prayerText" name="prayer_text" rows="5" required maxlength="2000"></textarea>
          </div>
          <div class="mb-4">
            <label class="form-label">Visibility</label>
            <select class="form-select" id="prayerVisibility" name="visibility">
              <option value="public">Public</option>
              <option value="private">Private</option>
            </select>
          </div>
          <button type="submit" class="btn-auth">Send Prayer 🙏</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- TESTIMONY MODAL -->
<div class="modal fade memorial-modal" id="testimonyModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">✍ Share a Memory</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="testimonyForm" class="memorial-form" enctype="multipart/form-data">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Title *</label>
            <input type="text" class="form-control" id="testimonyTitle" name="title" required maxlength="200">
          </div>
          <div class="mb-3">
            <label class="form-label">Your Memory *</label>
            <textarea class="form-control" id="testimonyText" name="testimony_text" rows="5" required maxlength="3000"></textarea>
          </div>
          <div class="mb-4">
            <label class="form-label">Photo (optional)</label>
            <input type="file" class="form-control" name="image" accept="image/*">
          </div>
          <button type="submit" class="btn-auth" id="testimonySubmitBtn">Share Memory ✍</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Testimony form submits to API
document.getElementById('testimonyForm')?.addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('testimonySubmitBtn');
  btn.disabled = true; btn.textContent = 'Sharing…';
  const fd = new FormData(this);
  try {
    const res = await fetch(SITE_URL + '/api/submit_testimony.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) {
      showAlert('✍ Your memory has been shared. Thank you.', 'success');
      bootstrap.Modal.getInstance(document.getElementById('testimonyModal'))?.hide();
      this.reset();
    } else {
      showAlert(data.message, 'error');
    }
  } catch { showAlert('Something went wrong.', 'error'); }
  btn.disabled = false; btn.textContent = 'Share Memory ✍';
});
</script>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
