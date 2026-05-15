<?php
/**
 * Test file in admin root — DELETE after use
 */
define('ADMIN_PAGE', true);
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Skip requireAdmin for now to test includes
echo "✓ Test file in admin/ root loaded\n";
echo "✓ Config included\n";
echo "✓ Functions included\n";
echo "✓ SITE_URL: " . SITE_URL . "\n";
