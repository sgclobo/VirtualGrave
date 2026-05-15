<?php
/**
 * List files in admin/pages directory — DELETE after use
 */
define('ADMIN_PAGE', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireAdmin();

header('Content-Type: text/plain; charset=utf-8');

$dir = __DIR__;
echo "Files in $dir:\n";
echo str_repeat('=', 60) . "\n";

$files = scandir($dir);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $path = $dir . '/' . $file;
    $size = filesize($path);
    $modified = date('Y-m-d H:i:s', filemtime($path));
    printf("%-30s %10s  %s\n", $file, $size . ' B', $modified);
}

echo "\nTrying to load members.php...\n";
echo str_repeat('=', 60) . "\n";

if (file_exists($dir . '/members.php')) {
    echo "✓ members.php exists\n";
    echo "✓ File size: " . filesize($dir . '/members.php') . " bytes\n";
    echo "✓ Readable: " . (is_readable($dir . '/members.php') ? 'YES' : 'NO') . "\n";
    
    // Try to parse it
    $code = file_get_contents($dir . '/members.php');
    $tokens = @token_get_all($code);
    if ($tokens === false) {
        echo "✗ PHP syntax error in file\n";
    } else {
        echo "✓ PHP syntax OK\n";
    }
} else {
    echo "✗ members.php does NOT exist\n";
}
