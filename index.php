<?php
/**
 * IN LOVING MEMORY — Homepage
 */
require_once __DIR__ . '/includes/functions.php';
logVisit('home');

$pageTitle = 'Home';
$activePage = 'home';
$pageClass = 'page-home';

$stats        = getMemorialStats();
$deceasedName = getSetting('deceased_name', 'Hercio Maria da Neves Campos');
$born         = getSetting('deceased_born', '1945-03-15');
$died         = getSetting('deceased_died', '2024-01-08');
$tagline      = getSetting('deceased_tagline', 'A soul of boundless love, wisdom and grace');
$bornDate     = $born ? date('F j, Y', strtotime($born)) : '';
$diedDate     = $died ? date('F j, Y', strtotime($died)) : '';
$bornYear     = $born ? date('Y', strtotime($born)) : '';
$diedYear     = $died ? date('Y', strtotime($died)) : '';

// Recent public prayers
$pdo = getDB();
$prayers = $pdo->query('
  SELECT p.*, u.full_name, u.profile_photo
  FROM prayers p
  JOIN users u ON u.id = p.user_id
  WHERE p.is_approved = 1 AND p.visibility = "public"
  ORDER BY p.created_at DESC LIMIT 3
')->fetchAll();

// Recent testimonies
$testimonies = $pdo->query('
  SELECT t.*, u.full_name
  FROM testimonies t
  JOIN users u ON u.id = t.user_id
  WHERE t.is_approved = 1
  ORDER BY t.created_at DESC LIMIT 3
')->fetchAll();

// Recent candles
$candles = $pdo->query('
  SELECT lc.*, cc.candle_name, cc.glow_color, u.full_name
  FROM lit_candles lc
  JOIN candles_catalog cc ON cc.id = lc.candle_id
  JOIN users u ON u.id = lc.user_id
  WHERE lc.is_approved = 1
  ORDER BY lc.created_at DESC LIMIT 6
')->fetchAll();

// Recent flowers
$flowers = $pdo->query('
  SELECT df.*, fc.flower_name, fc.color, u.full_name
  FROM deposited_flowers df
  JOIN flowers_catalog fc ON fc.id = df.flower_id
  JOIN users u ON u.id = df.user_id
  WHERE df.is_approved = 1
  ORDER BY df.created_at DESC LIMIT 10
')->fetchAll();

$flowerEmojis = ['White Rose'=>'🌹','Red Rose'=>'🌹','Lily'=>'💐','Orchid'=>'🌺','Jasmine'=>'🌸','Sunflower'=>'🌻','Carnation'=>'🌷'];

include __DIR__ . '/includes/header.php';
?>
<meta name="csrf" content="<?= e(csrfToken()) ?>">
<script>
const SITE_URL = '<?= SITE_URL ?>';
</script>

<!-- ═══════════════════════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════════════════════ -->
<section class="hero-section">
    <div class="hero-bg-pattern"></div>

    <div class="hero-content">
        <!-- Cross -->
        <span class="hero-cross">✝</span>

        <!-- Portrait -->
        <div class="hero-portrait-wrap">
            <div class="hero-glow-ring"></div>
            <img src="<?= SITE_URL ?>/assets/img/hercio1.jpeg" class="hero-portrait" alt="<?= e($deceasedName) ?>"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="hero-portrait-placeholder" style="display:none;">
                <i class="bi bi-person"></i>
            </div>
        </div>

        <!-- Name & Dates -->
        <h1 class="hero-name"><?= e($deceasedName) ?></h1>
        <p class="hero-dates"><?= e($bornYear) ?> &mdash; <?= e($diedYear) ?></p>

        <!-- Divider -->
        <div class="hero-divider">✝</div>

        <!-- Quote -->
        <p class="hero-quote"><?= e($tagline) ?></p>

        <!-- Candles -->
        <div class="hero-candles">
            <?php for ($i = 0; $i < 3; $i++): ?>
            <div class="candle-wrap">
                <div class="candle-flame"></div>
                <div class="candle-wick"></div>
                <div class="candle-body"></div>
                <div class="candle-base"></div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Actions -->
        <div class="hero-actions">
            <a href="<?= SITE_URL ?>/pages/memorial.php" class="btn-memorial btn-primary-memorial">
                <i class="bi bi-flower1"></i> Visit Memorial
            </a>
            <a href="<?= SITE_URL ?>/pages/biography.php" class="btn-memorial btn-outline-memorial">
                <i class="bi bi-book"></i> Biography
            </a>
            <a href="<?= SITE_URL ?>/pages/gallery.php" class="btn-memorial btn-outline-memorial">
                <i class="bi bi-images"></i> Gallery
            </a>
            <a href="<?= SITE_URL ?>/pages/timeline.php" class="btn-memorial btn-outline-memorial">
                <i class="bi bi-clock-history"></i> Timeline
            </a>
            <?php if (!isLoggedIn()): ?>
            <a href="<?= SITE_URL ?>/pages/login.php" class="btn-memorial btn-outline-memorial">
                <i class="bi bi-person"></i> Sign In
            </a>
            <a href="<?= SITE_URL ?>/pages/register.php" class="btn-memorial btn-primary-memorial">
                <i class="bi bi-person-plus"></i> Register
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator" aria-hidden="true">
        <span>Scroll</span>
        <div class="scroll-arrow"></div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     STATS BAR
═══════════════════════════════════════════════════════ -->
<section class="stats-bar">
    <div class="container-memorial">
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-icon">🕯</span>
                <span class="stat-number" data-count="<?= $stats['candles'] ?>"
                    id="candlesCount"><?= number_format($stats['candles']) ?></span>
                <span class="stat-label">Candles Lit</span>
            </div>
            <div class="stat-item">
                <span class="stat-icon">🌹</span>
                <span class="stat-number" data-count="<?= $stats['flowers'] ?>"
                    id="flowersCount"><?= number_format($stats['flowers']) ?></span>
                <span class="stat-label">Flowers Left</span>
            </div>
            <div class="stat-item">
                <span class="stat-icon">🙏</span>
                <span class="stat-number" data-count="<?= $stats['prayers'] ?>"
                    id="prayersCount"><?= number_format($stats['prayers']) ?></span>
                <span class="stat-label">Prayers Said</span>
            </div>
            <div class="stat-item">
                <span class="stat-icon">✍</span>
                <span class="stat-number"
                    data-count="<?= $stats['testimonies'] ?>"><?= number_format($stats['testimonies']) ?></span>
                <span class="stat-label">Testimonies</span>
            </div>
            <div class="stat-item">
                <span class="stat-icon">👥</span>
                <span class="stat-number"
                    data-count="<?= $stats['visitors'] ?>"><?= number_format($stats['visitors']) ?></span>
                <span class="stat-label">Visitors</span>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     VIRTUAL GRAVE SECTION
═══════════════════════════════════════════════════════ -->
<section class="grave-section">
    <div class="grave-bg-glow"></div>
    <div class="container-memorial w-100">
        <div class="grave-scene">
            <!-- Section Header -->
            <p class="section-eyebrow reveal" style="color: rgba(201,168,76,0.6);">Sacred Space</p>
            <h2 class="font-display reveal"
                style="color:var(--ivory);font-size:clamp(1.6rem,3vw,2.5rem);font-weight:300;margin-bottom:2.5rem;">
                Virtual Memorial Grave
            </h2>

            <!-- Flowers around grave -->
            <div class="grave-flowers" id="graveFlowersArea">
                <?php foreach ($flowers as $f):
          $emoji = $flowerEmojis[$f['flower_name']] ?? '🌸';
        ?>
                <span class="grave-flower-item">
                    <?= $emoji ?>
                    <span class="flower-tooltip"><?= e($f['full_name']) ?> left <?= e($f['flower_name']) ?></span>
                </span>
                <?php endforeach; ?>
                <?php if (empty($flowers)): ?>
                <span style="color:rgba(250,248,243,0.2);font-style:italic;font-size:0.85rem;">Be the first to leave
                    flowers…</span>
                <?php endif; ?>
            </div>

            <!-- Memorial Stone -->
            <div class="memorial-stone reveal">
                <span class="stone-cross">✝</span>
                <div class="stone-name"><?= e($deceasedName) ?></div>
                <div class="stone-dates"><?= e($bornYear) ?> — <?= e($diedYear) ?></div>
                <div class="stone-verse">
                    "Blessed are those who mourn,<br>for they shall be comforted."
                    <br><small style="opacity:0.6">Matthew 5:4</small>
                </div>
            </div>
            <div class="grave-base"></div>
            <div class="grave-ground"></div>

            <!-- Candles around grave -->
            <div class="grave-candles" id="graveCandlesArea">
                <?php foreach ($candles as $c): ?>
                <div class="grave-candle"
                    title="<?= e($c['full_name']) ?>: <?= e($c['dedication'] ?: $c['candle_name']) ?>">
                    <div class="grave-candle-flame"></div>
                    <div class="grave-candle-body" style="box-shadow: 0 0 12px <?= e($c['glow_color']) ?>80;"></div>
                    <span class="grave-candle-name"><?= e($c['candle_name']) ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($candles)): ?>
                <span style="color:rgba(250,248,243,0.2);font-style:italic;font-size:0.85rem;">Light the first
                    candle…</span>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <?php if (isLoggedIn()): ?>
            <div class="grave-actions">
                <button class="btn-grave-action btn-ripple" data-bs-toggle="modal" data-bs-target="#flowerModal">
                    🌹 Deposit Flowers
                </button>
                <button class="btn-grave-action btn-gold btn-ripple" data-bs-toggle="modal"
                    data-bs-target="#candleModal">
                    🕯 Light Candle
                </button>
                <button class="btn-grave-action btn-ripple" data-bs-toggle="modal" data-bs-target="#prayerModal">
                    🙏 Say a Prayer
                </button>
                <a href="<?= SITE_URL ?>/pages/testimonies.php" class="btn-grave-action btn-ripple">
                    ✍ Leave a Testimony
                </a>
            </div>
            <?php else: ?>
            <div class="grave-actions">
                <p style="color:rgba(250,248,243,0.5);font-style:italic;margin-bottom:1rem;">
                    Join the memorial to interact
                </p>
                <a href="<?= SITE_URL ?>/pages/login.php" class="btn-grave-action btn-gold">
                    <i class="bi bi-person"></i> Sign In
                </a>
                <a href="<?= SITE_URL ?>/pages/register.php" class="btn-grave-action">
                    <i class="bi bi-person-plus"></i> Register
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     PRAYERS SECTION
═══════════════════════════════════════════════════════ -->
<?php if (!empty($prayers)): ?>
<section class="section-pad section-cream">
    <div class="container-memorial">
        <div class="section-header">
            <span class="section-eyebrow">Community</span>
            <h2 class="section-title reveal">Prayers & Reflections</h2>
            <div class="section-line">🙏</div>
            <p class="section-subtitle reveal reveal-delay-1">
                Heartfelt prayers offered for the eternal rest of <?= e($deceasedName) ?>
            </p>
        </div>

        <div class="row g-4">
            <?php foreach ($prayers as $i => $p): ?>
            <div class="col-md-4 reveal reveal-delay-<?= $i+1 ?>">
                <div class="prayer-card h-100">
                    <span class="prayer-category-badge"><?= e(ucfirst(str_replace('_', ' ', $p['category']))) ?></span>
                    <?php if ($p['title']): ?>
                    <div class="prayer-title"><?= e($p['title']) ?></div>
                    <?php endif; ?>
                    <p class="prayer-text"><?= e($p['prayer_text']) ?></p>
                    <div class="prayer-meta">
                        <div class="prayer-avatar"><?= e(mb_substr($p['full_name'],0,1)) ?></div>
                        <span><?= e($p['full_name']) ?></span>
                        <span class="ms-auto"><?= date('M j, Y', strtotime($p['created_at'])) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= SITE_URL ?>/pages/prayers.php" class="btn-memorial btn-primary-memorial d-inline-flex">
                View All Prayers
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     TESTIMONIES SECTION
═══════════════════════════════════════════════════════ -->
<?php if (!empty($testimonies)): ?>
<section class="section-pad section-light">
    <div class="container-memorial">
        <div class="section-header">
            <span class="section-eyebrow">Memories</span>
            <h2 class="section-title reveal">Shared Testimonies</h2>
            <div class="section-line">✍</div>
        </div>

        <div class="row g-4">
            <?php foreach ($testimonies as $i => $t): ?>
            <div class="col-md-4 reveal reveal-delay-<?= $i+1 ?>">
                <div class="testimony-card h-100">
                    <?php if ($t['image']): ?>
                    <img src="<?= SITE_URL ?>/uploads/<?= e($t['image']) ?>" class="testimony-img" alt="">
                    <?php endif; ?>
                    <div class="testimony-body">
                        <div class="testimony-title"><?= e($t['title']) ?></div>
                        <p class="testimony-text"><?= e($t['testimony_text']) ?></p>
                        <div class="prayer-meta">
                            <div class="prayer-avatar"><?= e(mb_substr($t['full_name'],0,1)) ?></div>
                            <span><?= e($t['full_name']) ?></span>
                            <span class="ms-auto"><?= date('M j, Y', strtotime($t['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= SITE_URL ?>/pages/testimonies.php" class="btn-memorial btn-primary-memorial d-inline-flex">
                Read All Testimonies
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     EXPLORE SECTION
═══════════════════════════════════════════════════════ -->
<section class="section-pad section-dark">
    <div class="container-memorial">
        <div class="section-header">
            <span class="section-eyebrow" style="color:rgba(201,168,76,0.7);">Explore</span>
            <h2 class="section-title reveal">A Life Remembered</h2>
            <div class="section-line">✝</div>
            <p class="section-subtitle reveal reveal-delay-1">
                Journey through the chapters of a beautiful life
            </p>
        </div>

        <div class="row g-4 text-center">
            <?php
      $links = [
        ['biography.php', 'bi-book', 'Biography', 'The story of a life full of purpose, faith and love'],
        ['gallery.php',   'bi-images', 'Gallery', 'Photos and memories that will last forever'],
        ['timeline.php',  'bi-clock-history', 'Timeline', 'The milestones of an extraordinary journey'],
        ['guestbook.php', 'bi-journal', 'Guestbook', 'Leave a message for the family'],
      ];
      foreach ($links as $i => [$page, $icon, $label, $desc]):
      ?>
            <div class="col-6 col-md-3 reveal reveal-delay-<?= $i+1 ?>">
                <a href="<?= SITE_URL ?>/pages/<?= $page ?>" class="d-block text-decoration-none hover-lift"
                    style="color:inherit;">
                    <div
                        style="width:70px;height:70px;border-radius:50%;background:rgba(201,168,76,0.1);border:1px solid rgba(201,168,76,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.6rem;color:var(--soft-gold);transition:all 0.3s ease;">
                        <i class="bi <?= $icon ?>"></i>
                    </div>
                    <h3 class="font-display"
                        style="font-size:1.3rem;font-weight:300;color:var(--ivory);margin-bottom:0.5rem;"><?= $label ?>
                    </h3>
                    <p style="font-size:0.9rem;color:rgba(250,248,243,0.5);margin:0;"><?= $desc ?></p>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     MODALS (Flower / Candle / Prayer)
═══════════════════════════════════════════════════════ -->
<?php if (isLoggedIn()):
  // Fetch catalogs
  $flowerCatalog = $pdo->query('SELECT * FROM flowers_catalog WHERE is_active=1 ORDER BY id')->fetchAll();
  $candleCatalog = $pdo->query('SELECT * FROM candles_catalog WHERE is_active=1 ORDER BY id')->fetchAll();
?>

<!-- FLOWER MODAL -->
<div class="modal fade memorial-modal" id="flowerModal" tabindex="-1" aria-label="Deposit Flowers">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">🌹 Deposit Flowers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted-memorial mb-3 font-display fst-italic">Choose a flower to place with love at the
                    memorial…</p>
                <form id="flowerForm" class="memorial-form">
                    <?= csrfField() ?>
                    <input type="hidden" id="selectedFlowerId" name="flower_id" value="">

                    <div class="flower-grid mb-4">
                        <?php
            $flowerEmojiMap = ['White Rose'=>'🌹','Red Rose'=>'🌹','Lily'=>'💐','Orchid'=>'🌺','Jasmine'=>'🌸','Sunflower'=>'🌻','Carnation'=>'🌷'];
            foreach ($flowerCatalog as $fl):
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
                        <textarea class="form-control" id="flowerMessage" name="message" rows="3"
                            placeholder="A few words from your heart…" maxlength="500"></textarea>
                    </div>

                    <button type="submit" class="btn-auth btn-ripple">Deposit Flowers 🌹</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CANDLE MODAL -->
<div class="modal fade memorial-modal" id="candleModal" tabindex="-1" aria-label="Light a Candle">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">🕯 Light a Candle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted-memorial mb-3 font-display fst-italic">Choose a candle and dedicate it in loving
                    memory…</p>
                <form id="candleForm" class="memorial-form">
                    <?= csrfField() ?>
                    <input type="hidden" id="selectedCandleId" name="candle_id" value="">

                    <div class="row g-3 mb-4">
                        <?php foreach ($candleCatalog as $c): ?>
                        <div class="col-6 col-md-3">
                            <div class="candle-option" data-id="<?= $c['id'] ?>">
                                <div class="candle-option-flame mx-auto mb-1"></div>
                                <div class="candle-option-body mx-auto mb-2"
                                    style="box-shadow: 0 0 8px <?= e($c['glow_color']) ?>60;"></div>
                                <div class="flower-name"><?= e($c['candle_name']) ?></div>
                                <div class="flower-meaning"><?= e($c['candle_type']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="candleDedication">Dedication (optional)</label>
                        <textarea class="form-control" id="candleDedication" name="dedication" rows="2"
                            placeholder="Light this candle in memory of…" maxlength="300"></textarea>
                    </div>

                    <button type="submit" class="btn-auth btn-ripple">Light Candle 🕯</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- PRAYER MODAL -->
<div class="modal fade memorial-modal" id="prayerModal" tabindex="-1" aria-label="Say a Prayer">
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
                        <label class="form-label" for="prayerTitle">Title (optional)</label>
                        <input type="text" class="form-control" id="prayerTitle" name="title"
                            placeholder="A prayer for peace…" maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="prayerCategory">Category</label>
                        <select class="form-select" id="prayerCategory" name="category">
                            <option value="peace">Peace</option>
                            <option value="gratitude">Gratitude</option>
                            <option value="healing">Healing</option>
                            <option value="family">Family</option>
                            <option value="eternal_rest">Eternal Rest</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="prayerText">Your Prayer <span class="text-gold">*</span></label>
                        <textarea class="form-control" id="prayerText" name="prayer_text" rows="5" required
                            placeholder="Dear Lord, receive the soul of your servant…" maxlength="2000"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="prayerVisibility">Visibility</label>
                        <select class="form-select" id="prayerVisibility" name="visibility">
                            <option value="public">Public — visible to all visitors</option>
                            <option value="private">Private — only between you and God</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-auth btn-ripple">Send Prayer 🙏</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>