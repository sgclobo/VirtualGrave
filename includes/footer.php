<?php
/**
 * IN LOVING MEMORY — Site Footer Partial
 */
$footerQuote = getSetting('footer_quote', 'In memory of Hercio Maria da Neves Campos, love continues.');
$deceasedName = getSetting('deceased_name', 'Hercio Maria da Neves Campos');
$born = getSetting('deceased_born');
$died = getSetting('deceased_died');
$bornYear = $born ? date('Y', strtotime($born)) : '';
$diedYear = $died ? date('Y', strtotime($died)) : '';
?>
<!-- ─── FOOTER ──────────────────────────────────────────────── -->
<footer class="site-footer">
  <div class="footer-candles" aria-hidden="true">
    <div class="footer-candle"><div class="footer-flame"></div></div>
    <div class="footer-candle"><div class="footer-flame"></div></div>
    <div class="footer-candle"><div class="footer-flame"></div></div>
  </div>

  <div class="footer-cross" aria-hidden="true">✝</div>

  <p class="footer-name"><?= e($deceasedName) ?></p>
  <?php if ($bornYear && $diedYear): ?>
  <p class="footer-dates"><?= e($bornYear) ?> — <?= e($diedYear) ?></p>
  <?php endif; ?>

  <p class="footer-quote">"<?= e($footerQuote) ?>"</p>

  <div class="footer-links">
    <a href="<?= SITE_URL ?>/">Home</a>
    <span>·</span>
    <a href="<?= SITE_URL ?>/pages/biography.php">Biography</a>
    <span>·</span>
    <a href="<?= SITE_URL ?>/pages/gallery.php">Gallery</a>
    <span>·</span>
    <a href="<?= SITE_URL ?>/pages/memorial.php">Memorial</a>
    <span>·</span>
    <a href="<?= SITE_URL ?>/pages/guestbook.php">Guestbook</a>
  </div>

  <p class="footer-copy">
    &copy; <?= date('Y') ?> In Loving Memory &mdash; A peaceful digital memorial garden
  </p>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Site JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<script src="<?= SITE_URL ?>/assets/js/petals.js"></script>
<?php if (!empty($extraJs)): ?>
  <?= $extraJs ?>
<?php endif; ?>
</body>
</html>
