<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$db = getDB();

// Map section titles to new appropriate icons
$iconUpdates = [
    'Involvement in Political Life' => '🏛️',  // Government building
    'Personality & Values' => '💎',           // Gem/precious values
    'Recent Years' => '📅',                    // Calendar
    'Acknowledgements' => '🙏',                // Folded hands/gratitude
    'Final Tribute' => '🕯️'                   // Candle/memorial
];

$results = [];
foreach ($iconUpdates as $title => $newIcon) {
    $stmt = $db->prepare("UPDATE biography SET icon = ? WHERE section_title = ?");
    $stmt->execute([$newIcon, $title]);
    $results[] = sprintf('✓ %s → %s', $title, $newIcon);
}

echo "Biography section icons updated:\n";
foreach ($results as $r) {
    echo $r . "\n";
}
echo "\nDone.\n";
