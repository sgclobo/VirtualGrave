<?php
/**
 * IN LOVING MEMORY — Site Header Partial
 * @var string $pageTitle  (set before including)
 * @var string $pageClass  (optional body class)
 */
require_once __DIR__ . '/functions.php';
$deceasedName = getSetting('deceased_name', 'Hercio Maria da Neves Campos');
$siteTitle     = getSetting('site_title', 'In Loving Memory');
$fullTitle     = ($pageTitle ?? $siteTitle) . ' — ' . $siteTitle;
$user          = isLoggedIn() ? currentUser() : null;
// Prefer new canonical key; fall back to legacy music_file key
$musicUrl = getSetting('music_file', getSetting('ambient_music_url', ''));

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="A sacred digital memorial for <?= e($deceasedName) ?>. Light candles, leave flowers, share memories and prayers.">
    <meta name="theme-color" content="#2C3E50">
    <title><?= e($fullTitle) ?></title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= SITE_URL ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?= SITE_URL ?>/assets/images/icon-192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Crimson+Pro:ital,wght@0,300;0,400;1,300&family=EB+Garamond:ital,wght@0,400;0,500;1,400&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Site CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/animations.css">
</head>

<body class="<?= e($pageClass ?? 'page-default') ?>">

    <!-- Ambient Music (hidden) -->
    <audio id="ambientAudio" loop preload="none">
        <?php if ($musicUrl): ?>
        <source src="<?= e($musicUrl) ?>" type="audio/mpeg">
        <?php endif; ?>
    </audio>

    <!-- Petal Container -->
    <div id="petalContainer" aria-hidden="true"></div>

    <!-- ─── NAVIGATION ─────────────────────────────────────────── -->
    <nav class="memorial-nav" id="mainNav">
        <div class="nav-inner">
            <!-- Logo / Site Name -->
            <a href="<?= SITE_URL ?>/" class="nav-brand">
                <span class="nav-cross">✝</span>
                <span class="nav-title"><?= e($siteTitle) ?></span>
            </a>

            <!-- Hamburger -->
            <button class="nav-hamburger" id="navToggle" aria-label="Menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>

            <!-- Links -->
            <div class="nav-links" id="navLinks">
                <a href="<?= SITE_URL ?>/" class="nav-link <?= ($activePage??'')==='home'?'active':'' ?>">
                    <i class="bi bi-house"></i> Home
                </a>
                <a href="<?= SITE_URL ?>/pages/biography.php"
                    class="nav-link <?= ($activePage??'')==='bio'?'active':'' ?>">
                    <i class="bi bi-book"></i> Biography
                </a>
                <a href="<?= SITE_URL ?>/pages/gallery.php"
                    class="nav-link <?= ($activePage??'')==='gallery'?'active':'' ?>">
                    <i class="bi bi-images"></i> Gallery
                </a>
                <a href="<?= SITE_URL ?>/pages/timeline.php"
                    class="nav-link <?= ($activePage??'')==='timeline'?'active':'' ?>">
                    <i class="bi bi-clock-history"></i> Timeline
                </a>
                <a href="<?= SITE_URL ?>/pages/memorial.php"
                    class="nav-link <?= ($activePage??'')==='memorial'?'active':'' ?>">
                    <i class="bi bi-flower1"></i> Memorial
                </a>

                <?php if ($user): ?>
                <div class="nav-user">
                    <span class="nav-user-name">
                        <?php if (!empty($user['profile_photo'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/<?= e($user['profile_photo']) ?>" class="nav-avatar" alt="">
                        <?php else: ?>
                        <span class="nav-avatar-placeholder"><?= e(mb_substr($user['full_name'],0,1)) ?></span>
                        <?php endif; ?>
                        <?= e($user['full_name']) ?>
                    </span>
                    <a href="<?= SITE_URL ?>/pages/logout.php" class="nav-link nav-logout">
                        <i class="bi bi-box-arrow-right"></i> Leave
                    </a>
                </div>
                <?php else: ?>
                <a href="<?= SITE_URL ?>/pages/login.php" class="nav-link nav-signin">
                    <i class="bi bi-person"></i> Sign In
                </a>
                <a href="<?= SITE_URL ?>/pages/register.php" class="nav-btn-register">
                    Join Memorial
                </a>
                <?php endif; ?>

                <!-- Music Toggle -->
                <button class="nav-music-btn" id="musicToggle" title="Toggle ambient music" aria-label="Toggle music">
                    <i class="bi bi-music-note-beamed"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="flash-container">
        <?= showFlash() ?>
    </div>