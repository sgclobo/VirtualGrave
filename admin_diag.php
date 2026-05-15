<?php
/**
 * Admin diagnostics — check DB tables and PHP state.
 * DELETE this file immediately after use.
 */
require_once 'includes/config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== SERVER DIAGNOSTICS ===\n\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "SAPI: " . PHP_SAPI . "\n";
echo "Memory limit: " . ini_get('memory_limit') . "\n";
echo "Max execution time: " . ini_get('max_execution_time') . "\n\n";

// Check DB connection
try {
    $db = getDB();
    echo "DB connection: OK\n\n";
} catch (Exception $e) {
    echo "DB connection FAILED: " . $e->getMessage() . "\n";
    exit;
}

// Expected tables
$tables = [
    'admin_users', 'users', 'flowers_catalog', 'candles_catalog',
    'deposited_flowers', 'lit_candles', 'prayers', 'testimonies',
    'gallery', 'timeline', 'biography', 'settings', 'visit_log', 'guestbook',
];

echo "=== TABLE CHECK ===\n";
foreach ($tables as $t) {
    try {
        $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "  ✓ $t ($count rows)\n";
    } catch (Exception $e) {
        echo "  ✗ $t — " . $e->getMessage() . "\n";
    }
}

echo "\n=== COLUMN CHECKS ===\n";

// Columns used specifically by admin/index.php
$colChecks = [
    'users'           => ['full_name', 'username', 'country', 'status', 'registered_at'],
    'prayers'         => ['title', 'category', 'created_at', 'user_id', 'is_approved', 'visibility'],
    'testimonies'     => ['is_approved'],
    'guestbook'       => ['is_approved'],
    'visit_log'       => ['visited_at', 'ip_address', 'page'],
];

foreach ($colChecks as $tbl => $cols) {
    try {
        $row = $db->query("SELECT " . implode(',', $cols) . " FROM `$tbl` LIMIT 1")->fetch();
        echo "  ✓ $tbl columns OK\n";
    } catch (Exception $e) {
        echo "  ✗ $tbl — " . $e->getMessage() . "\n";
    }
}

echo "\n=== ADMIN/INDEX QUERIES ===\n";
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
        echo "  ✓ $key\n";
    } catch (Exception $e) {
        echo "  ✗ $key — " . $e->getMessage() . "\n";
    }
}

echo "\nDone. DELETE this file now.\n";
