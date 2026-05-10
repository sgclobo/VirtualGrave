<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
$db = getDB();
$sections = $db->query('SELECT id, section_order, section_title, icon FROM biography ORDER BY section_order ASC')->fetchAll();
if ($sections) {
    foreach ($sections as $s) {
        echo sprintf('ID: %d | Order: %d | Title: %s | Icon: %s' . PHP_EOL, $s['id'], $s['section_order'], $s['section_title'], $s['icon']);
    }
} else {
    echo "No sections found.\n";
}
