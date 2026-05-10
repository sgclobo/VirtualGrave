<?php
/**
 * Temporary utility: Update biography section icons
 * This file should be deleted after use for security.
 * Access: https://herciocampos.com/admin/update_bio_icons.php?token=confirm
 */
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Simple security token
$validToken = 'confirm';
$token = $_GET['token'] ?? '';

if (!isset($_GET['execute'])) {
    // Show preview only
    $db = getDB();
    $sections = $db->query('SELECT id, section_order, section_title, icon FROM biography ORDER BY section_order ASC')->fetchAll();
    
    $iconUpdates = [
        'Involvement in Political Life' => '🏛️',
        'Personality & Values' => '💎',
        'Recent Years' => '📅',
        'Acknowledgements' => '🙏',
        'Final Tribute' => '🕯️'
    ];
    
    $toUpdate = array_filter($sections, fn($s) => isset($iconUpdates[$s['section_title']]));
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Bio Icon Update Preview</title>
        <style>
            body { font-family: Arial; margin: 20px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #f0f0f0; }
            .old { color: #999; }
            .new { font-size: 1.5em; font-weight: bold; }
            .warning { color: #d9534f; margin: 20px 0; padding: 10px; background: #f2dede; border-radius: 4px; }
            .action { text-align: center; margin: 20px 0; }
            a { padding: 10px 20px; background: #0275d8; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Biography Section Icon Update Preview</h2>
            <p>The following <strong><?= count($toUpdate) ?></strong> sections will be updated:</p>
            
            <table>
                <tr>
                    <th>Section Title</th>
                    <th>Current Icon</th>
                    <th>New Icon</th>
                </tr>
                <?php foreach ($toUpdate as $sec):
                    $newIcon = $iconUpdates[$sec['section_title']];
                ?>
                <tr>
                    <td><?= htmlspecialchars($sec['section_title']) ?></td>
                    <td class="old"><?= htmlspecialchars($sec['icon']) ?></td>
                    <td class="new"><?= htmlspecialchars($newIcon) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <div class="warning">
                ⚠️ This is a one-time utility. After update, this file should be deleted from the server.
            </div>
            
            <div class="action">
                <a href="?execute=1&token=<?= urlencode($validToken) ?>">✓ Confirm & Execute Update</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Execute update
if ($token !== $validToken) {
    die('Invalid token.');
}

$db = getDB();
$iconUpdates = [
    'Involvement in Political Life' => '🏛️',
    'Personality & Values' => '💎',
    'Recent Years' => '📅',
    'Acknowledgements' => '🙏',
    'Final Tribute' => '🕯️'
];

$updated = 0;
foreach ($iconUpdates as $title => $newIcon) {
    $stmt = $db->prepare("UPDATE biography SET icon = ? WHERE section_title = ?");
    if ($stmt->execute([$newIcon, $title])) {
        $updated++;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Complete</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: #31708f; margin: 20px 0; padding: 15px; background: #d1ecf1; border-radius: 4px; border-left: 4px solid #0c5460; }
        .action { text-align: center; margin: 20px 0; }
        a { padding: 10px 20px; background: #0275d8; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <h2>✓ Update Complete</h2>
        <div class="success">
            <strong><?= $updated ?> sections updated successfully.</strong><br>
            Biography icons have been replaced with more appropriate emojis.
        </div>
        <p><strong>Updates applied:</strong></p>
        <ul>
            <li>Involvement in Political Life → 🏛️ (Government building)</li>
            <li>Personality & Values → 💎 (Precious gem/values)</li>
            <li>Recent Years → 📅 (Calendar/time)</li>
            <li>Acknowledgements → 🙏 (Gratitude/folded hands)</li>
            <li>Final Tribute → 🕯️ (Candle/memorial)</li>
        </ul>
        <p><em>⚠️ Important: Delete this file from the server for security:</em> <code>/admin/update_bio_icons.php</code></p>
        <div class="action">
            <a href="/admin/pages/biography.php">← Return to Biography Editor</a>
        </div>
    </div>
</body>
</html>
<?php
