<?php
/**
 * Self-contained server diagnostic — no require_once needed.
 * Upload this single file to the server root, visit it, then DELETE it.
 */
header('Content-Type: text/plain; charset=utf-8');

// ── Inline credentials (same as includes/config.php) ──────────────────────
$host    = 'localhost';
$dbName  = 'u149904157_memorial_db';
$dbUser  = 'u149904157_h3rcio';
$dbPass  = 'H3rcio#53125';
$charset = 'utf8mb4';

echo "=== SERVER DIAGNOSTICS ===\n";
echo "PHP : " . PHP_VERSION . "\n";
echo "SAPI: " . PHP_SAPI . "\n";
echo "MEM : " . ini_get('memory_limit') . "\n\n";

// ── DB connection ─────────────────────────────────────────────────────────
try {
    $dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";
    $db  = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "DB connection: OK\n\n";
} catch (PDOException $e) {
    echo "DB connection FAILED: " . $e->getMessage() . "\n";
    exit;
}

// ── PHP error log location ────────────────────────────────────────────────
echo "error_log : " . (ini_get('error_log') ?: '(not set)') . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n\n";

// ── Table check ──────────────────────────────────────────────────────────
$tables = [
    'admin_users', 'users', 'flowers_catalog', 'candles_catalog',
    'deposited_flowers', 'lit_candles', 'prayers', 'testimonies',
    'gallery', 'timeline', 'biography', 'settings', 'visit_log', 'guestbook',
];

echo "=== TABLE CHECK ===\n";
foreach ($tables as $t) {
    try {
        $count = $db->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        echo "  OK  {$t} ({$count} rows)\n";
    } catch (Exception $e) {
        echo "  FAIL {$t} — " . $e->getMessage() . "\n";
    }
}

// ── Exact queries from admin/index.php ───────────────────────────────────
echo "\n=== ADMIN DASHBOARD QUERIES ===\n";
$queries = [
    'total_members'       => "SELECT COUNT(*) FROM users WHERE status = 'approved'",
    'pending_members'     => "SELECT COUNT(*) FROM users WHERE status = 'pending'",
    'total_flowers'       => "SELECT COUNT(*) FROM deposited_flowers",
    'total_candles'       => "SELECT COUNT(*) FROM lit_candles",
    'total_prayers'       => "SELECT COUNT(*) FROM prayers",
    'pending_testimonies' => "SELECT COUNT(*) FROM testimonies WHERE is_approved = 0",
    'total_testimonies'   => "SELECT COUNT(*) FROM testimonies WHERE is_approved = 1",
    'total_visits'        => "SELECT COUNT(*) FROM visit_log",
    'today_visits'        => "SELECT COUNT(*) FROM visit_log WHERE DATE(visited_at) = CURDATE()",
    'guestbook_pending'   => "SELECT COUNT(*) FROM guestbook WHERE is_approved = 0",
    'recent_members'      => "SELECT full_name, username, country, status, registered_at AS created_at FROM users ORDER BY registered_at DESC LIMIT 5",
    'recent_prayers'      => "SELECT p.title, p.category, p.created_at, u.full_name FROM prayers p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5",
    'visit_chart'         => "SELECT DATE(visited_at) as visit_date, COUNT(*) as cnt FROM visit_log WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(visited_at) ORDER BY visit_date ASC",
];
foreach ($queries as $key => $sql) {
    try {
        $db->query($sql)->fetchAll();
        echo "  OK  {$key}\n";
    } catch (Exception $e) {
        echo "  FAIL {$key} — " . $e->getMessage() . "\n";
    }
}

echo "\n!! DELETE THIS FILE FROM THE SERVER IMMEDIATELY AFTER USE !!\n";
