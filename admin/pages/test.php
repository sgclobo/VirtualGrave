<?php
/**
 * Test file to verify admin/pages/ is accessible — DELETE after use
 */
define('ADMIN_PAGE', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Try to verify admin session without including header.php
try {
    requireAdmin();
    echo "✓ Admin session OK\n";
} catch (Exception $e) {
    echo "✗ Admin session error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test that we're in the right directory
echo "✓ Test file loaded from: " . __FILE__ . "\n";
echo "✓ SITE_URL: " . SITE_URL . "\n";
echo "✓ DB accessible: ";

try {
    $db = getDB();
    $result = $db->query("SELECT 1 as test")->fetch();
    if ($result['test'] == 1) {
        echo "YES\n";
    } else {
        echo "NO (unexpected result)\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n✓ admin/pages/ directory is accessible and working!\n";
