<?php
/**
 * IN LOVING MEMORY — Timeline Page
 */
require_once __DIR__ . '/../includes/functions.php';
logVisit('timeline');

$pdo   = getDB();
$items = $pdo->query('SELECT * FROM timeline WHERE is_active=1 ORDER BY sort_order, year, month')->fetchAll();

$deceasedName = getSetting('deceased_name');

$iconMap = [
  'baby'           => '👶',
  'school'         => '🏫',
  'book'           => '📚',
  'graduation-cap' => '🎓',
  'heart'          => '❤️',
  'briefcase'      => '💼',
  'star'           => '⭐',
  'award'          => '🏅',
  'family'         => '👨‍👩‍👧‍👦',
  'sunset'         => '🌅',
  'dove'           => '🕊️',
  'church'         => '⛪',
];

$categoryColors = [
  'birth'       => '#E8C97B',
  'education'   => '#82B4C8',
  'work'        => '#9BC2A8',
  'family'      => '#E8A4B8',
  'achievement' => '#C9A84C',
  'personal'    => '#B0A8D4',
  'retirement'  => '#D4B896',
  'passing'     => '#8C8478',
];

$pageTitle  = 'Timeline';
$activePage = 'timeline';
include __DIR__ . '/../includes/header.php';
?>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>

<!-- HERO -->
<div class="page-hero">
  <div class="container-memorial">
    <span class="section-eyebrow" style="color:rgba(201,168,76,0.7);">Life Journey</span>
    <h1 class="page-hero-title"><?= e($deceasedName) ?></h1>
    <p class="page-hero-subtitle">A timeline of a beautiful life</p>
  </div>
</div>

<!-- TIMELINE -->
<section class="section-pad section-light">
  <div class="container-memorial">

    <?php if (empty($items)): ?>
    <p class="text-center text-muted-memorial fst-italic">Timeline coming soon…</p>
    <?php else: ?>

    <div class="timeline-wrap">
      <?php foreach ($items as $i => $item):
        $emoji = $iconMap[$item['icon']] ?? '⭐';
        $color = $categoryColors[$item['category']] ?? '#C9A84C';
        $catLabel = ucfirst(str_replace('_', ' ', $item['category']));
        $monthStr = $item['month'] ? date('F', mktime(0,0,0,$item['month'],1)) . ' ' : '';
      ?>
      <div class="timeline-item">

        <?php if ($i % 2 === 0): ?>
        <!-- LEFT content -->
        <div class="timeline-content reveal reveal-left">
          <span class="timeline-category-badge" style="color:<?= e($color) ?>"><?= e($catLabel) ?></span>
          <div class="timeline-title"><?= e($item['title']) ?></div>
          <?php if ($item['description']): ?>
          <p class="timeline-desc"><?= e($item['description']) ?></p>
          <?php endif; ?>
        </div>

        <!-- CENTER dot -->
        <div class="timeline-year-col">
          <div class="timeline-dot" style="background:<?= e($color) ?>"><?= $emoji ?></div>
          <div class="timeline-year"><?= e($monthStr . $item['year']) ?></div>
        </div>

        <!-- RIGHT empty -->
        <div class="timeline-empty"></div>

        <?php else: ?>
        <!-- LEFT empty -->
        <div class="timeline-empty"></div>

        <!-- CENTER dot -->
        <div class="timeline-year-col">
          <div class="timeline-dot" style="background:<?= e($color) ?>"><?= $emoji ?></div>
          <div class="timeline-year"><?= e($monthStr . $item['year']) ?></div>
        </div>

        <!-- RIGHT content -->
        <div class="timeline-content reveal reveal-right">
          <span class="timeline-category-badge" style="color:<?= e($color) ?>"><?= e($catLabel) ?></span>
          <div class="timeline-title"><?= e($item['title']) ?></div>
          <?php if ($item['description']): ?>
          <p class="timeline-desc"><?= e($item['description']) ?></p>
          <?php endif; ?>
        </div>
        <?php endif; ?>

      </div>
      <?php endforeach; ?>
    </div>

    <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
