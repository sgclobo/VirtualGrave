<?php
/**
 * Admin Header & Sidebar
 * Included at the top of every admin page
 */
if (!defined('ADMIN_PAGE')) {
    header('Location: ../login.php');
    exit;
}
requireAdmin();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');

$navItems = [
    ['page' => 'index',     'icon' => '📊', 'label' => 'Dashboard'],
    ['page' => 'members',   'icon' => '👥', 'label' => 'Members'],
    ['page' => 'moderate',  'icon' => '🛡️',  'label' => 'Moderate Content'],
    ['page' => 'gallery',   'icon' => '🖼️',  'label' => 'Gallery'],
    ['page' => 'flowers',   'icon' => '🌹', 'label' => 'Flowers Catalog'],
    ['page' => 'candles',   'icon' => '🕯️', 'label' => 'Candles Catalog'],
    ['page' => 'biography', 'icon' => '📖', 'label' => 'Biography'],
    ['page' => 'timeline',  'icon' => '📅', 'label' => 'Timeline'],
    ['page' => 'settings',  'icon' => '⚙️', 'label' => 'Settings'],
];

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = function_exists('mb_substr') ? mb_substr($adminName, 0, 1) : substr($adminName, 0, 1);
$adminInitial = strtoupper($adminInitial ?: 'A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> — Memorial Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Crimson+Pro:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <style>
        :root {
            --admin-sidebar-bg: #0f1c0f;
            --admin-sidebar-width: 250px;
        }
        body { background: #f0ede8; }

        /* Sidebar */
        .admin-sidebar {
            width: var(--admin-sidebar-width);
            min-height: 100vh;
            background: var(--admin-sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s ease;
        }
        .admin-sidebar-logo {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            text-align: center;
        }
        .admin-sidebar-logo .candle-icon { font-size: 2rem; }
        .admin-sidebar-logo h6 { color: var(--soft-gold); font-family: 'Cormorant Garamond', serif; font-weight: 600; margin: 0.3rem 0 0; }
        .admin-sidebar-logo small { color: rgba(255,255,255,0.4); font-size: 0.7rem; }

        .admin-nav { flex: 1; padding: 1rem 0; overflow-y: auto; }
        .admin-nav-item a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1.5rem;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .admin-nav-item a:hover,
        .admin-nav-item.active a {
            color: #fff;
            background: rgba(255,255,255,0.06);
            border-left-color: var(--soft-gold);
        }
        .admin-nav-item .nav-icon { font-size: 1.1rem; width: 22px; text-align: center; }

        .admin-sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .admin-sidebar-footer a {
            color: rgba(255,255,255,0.4);
            font-size: 0.8rem;
            text-decoration: none;
            display: block;
            padding: 0.3rem 0;
            transition: color 0.2s;
        }
        .admin-sidebar-footer a:hover { color: rgba(255,255,255,0.8); }

        /* Main content */
        .admin-main {
            margin-left: var(--admin-sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .admin-topbar {
            background: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .admin-topbar .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.4rem;
            cursor: pointer;
            color: #555;
        }
        .admin-topbar h5 { margin: 0; font-family: 'Cormorant Garamond', serif; font-size: 1.15rem; }
        .admin-topbar .ms-auto { display: flex; align-items: center; gap: 0.75rem; }
        .admin-topbar .admin-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--soft-gold);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.8rem; color: var(--deep-blue);
        }

        .admin-content { padding: 2rem 1.5rem; flex: 1; }

        /* Cards */
        .admin-stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid var(--soft-gold);
        }
        .admin-stat-card .stat-value { font-size: 2rem; font-weight: 700; color: var(--deep-blue); }
        .admin-stat-card .stat-label { font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 0.05em; }
        .admin-stat-card .stat-icon { font-size: 2rem; opacity: 0.3; }

        .admin-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .admin-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .admin-card-header h6 { margin: 0; font-weight: 600; }

        .badge-pending  { background: #f59e0b22; color: #b45309; }
        .badge-approved { background: #10b98122; color: #065f46; }
        .badge-rejected { background: #ef444422; color: #991b1b; }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.open { transform: translateX(0); }
            .admin-main { margin-left: 0; }
            .admin-topbar .sidebar-toggle { display: block; }
        }
    </style>
    <script>const SITE_URL = '<?= SITE_URL ?>';</script>
</head>
<body>

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-logo">
        <div class="candle-icon">🕯️</div>
        <h6>In Loving Memory</h6>
        <small>Administration Panel</small>
    </div>

    <nav class="admin-nav">
        <?php foreach ($navItems as $item): ?>
        <div class="admin-nav-item <?= $currentPage === $item['page'] ? 'active' : '' ?>">
            <a href="<?= $item['page'] === 'index' ? '../index.php' : $item['page'] . '.php' ?>">
                <span class="nav-icon"><?= $item['icon'] ?></span>
                <?= $item['label'] ?>
            </a>
        </div>
        <?php endforeach; ?>
    </nav>

    <div class="admin-sidebar-footer">
        <div class="text-white-50 small mb-2"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></div>
        <a href="../../pages/memorial.php" target="_blank">🌐 View Memorial</a>
        <a href="../logout.php">🚪 Sign Out</a>
    </div>
</aside>

<!-- Main -->
<div class="admin-main">
    <div class="admin-topbar">
        <button class="sidebar-toggle" onclick="document.getElementById('adminSidebar').classList.toggle('open')">
            ☰
        </button>
        <h5><?= $pageTitle ?? 'Admin' ?></h5>
        <div class="ms-auto">
            <small class="text-muted d-none d-sm-inline"><?= date('l, F j, Y') ?></small>
            <div class="admin-avatar"><?= $adminInitial ?></div>
        </div>
    </div>

    <div class="admin-content">
<?php // Content follows ?>
