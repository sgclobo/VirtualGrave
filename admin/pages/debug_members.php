<?php
/**
 * Try to include members.php and catch any errors — DELETE after use
 */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

define('ADMIN_PAGE', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireAdmin();

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Attempting to include members.php...</h2>";
echo "<hr>";

try {
    ob_start();
    include 'members.php';
    $content = ob_get_clean();
    echo "✓ members.php included successfully\n";
    echo "<pre>$content</pre>";
} catch (Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
