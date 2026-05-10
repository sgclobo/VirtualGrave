<?php
/**
 * Check current biography icons
 */
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$db = getDB();
$sections = $db->query('SELECT id, section_order, section_title, icon FROM biography ORDER BY section_order ASC')->fetchAll();

echo "Current Biography Sections:\n";
echo str_repeat('=', 80) . "\n";
foreach ($sections as $s) {
    echo sprintf("ID: %2d | Order: %2d | Title: %-40s | Icon: %s\n", 
        $s['id'], 
        $s['section_order'], 
        substr($s['section_title'], 0, 40),
        $s['icon']
    );
}
echo str_repeat('=', 80) . "\n";
