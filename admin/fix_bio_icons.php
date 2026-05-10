<?php
/**
 * Fix biography section icons - assign unique icons to each section
 * Access: https://herciocampos.com/admin/fix_bio_icons.php
 */
require_once '../includes/config.php';
require_once '../includes/functions.php';

$db = getDB();
$confirm = isset($_GET['confirm']) && $_GET['confirm'] === '1';

// Map each section title to a unique, appropriate icon
$iconMap = [
    'Personal Life' => '👨‍👩‍👧‍👦',          // Family
    'Involvement in Political Life' => '🏛️',  // Government
    'Personality & Values' => '💎',            // Gem/values
    'Recent Years' => '📅',                     // Calendar
    'Acknowledgements' => '🙏',                 // Gratitude
    'Final Tribute' => '🕯️'                    // Candle/memorial
];

// Get current sections
$sections = $db->query('SELECT id, section_order, section_title, icon FROM biography ORDER BY section_order ASC')->fetchAll();

// Filter to sections we're updating
$toUpdate = [];
foreach ($sections as $s) {
    if (isset($iconMap[$s['section_title']])) {
        $toUpdate[] = array_merge($s, ['newIcon' => $iconMap[$s['section_title']]]);
    }
}

if (!$confirm) {
    // Show preview
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Biography Icons - Preview</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
            h1 { color: #333; margin-top: 0; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background: #f0f0f0; padding: 12px; text-align: left; font-weight: bold; border-bottom: 2px solid #ddd; }
            td { padding: 12px; border-bottom: 1px solid #eee; }
            tr:hover { background: #f9f9f9; }
            .old-icon { color: #999; font-size: 1.2em; }
            .new-icon { font-size: 1.5em; font-weight: bold; color: #d9534f; }
            .warning { background: #f2dede; border: 1px solid #ebccd1; color: #a94442; padding: 15px; border-radius: 4px; margin: 20px 0; }
            .actions { text-align: center; margin: 30px 0; }
            .btn { padding: 12px 24px; margin: 0 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; }
            .btn-primary { background: #5cb85c; color: white; }
            .btn-primary:hover { background: #4cae4c; }
            .btn-secondary { background: #6c757d; color: white; }
            .btn-secondary:hover { background: #5a6268; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>📚 Biography Section Icons - Fix Preview</h1>
            <p>Found <strong><?= count($toUpdate) ?></strong> sections to update with unique icons:</p>
            
            <table>
                <thead>
                    <tr>
                        <th>Section Title</th>
                        <th>Current Icon</th>
                        <th>New Icon</th>
                        <th>Meaning</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($toUpdate as $item): 
                        $meanings = [
                            'Personal Life' => 'Family & personal background',
                            'Involvement in Political Life' => 'Political engagement',
                            'Personality & Values' => 'Character & principles',
                            'Recent Years' => 'Recent timeline',
                            'Acknowledgements' => 'Gratitude & thanks',
                            'Final Tribute' => 'Memorial & remembrance'
                        ];
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($item['section_title']) ?></strong></td>
                        <td><span class="old-icon"><?= htmlspecialchars($item['icon']) ?></span></td>
                        <td><span class="new-icon"><?= htmlspecialchars($item['newIcon']) ?></span></td>
                        <td><?= htmlspecialchars($meanings[$item['section_title']] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="warning">
                <strong>⚠️ Important:</strong> This will update the database. Make sure you have a backup before proceeding.
            </div>
            
            <div class="actions">
                <a href="?confirm=1" class="btn btn-primary">✓ Confirm & Apply Updates</a>
                <a href="/admin/pages/biography.php" class="btn btn-secondary">← Cancel</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Apply updates
$updated = [];
foreach ($toUpdate as $item) {
    $stmt = $db->prepare("UPDATE biography SET icon = ? WHERE id = ?");
    if ($stmt->execute([$item['newIcon'], $item['id']])) {
        $updated[] = [
            'title' => $item['section_title'],
            'icon' => $item['newIcon'],
            'success' => true
        ];
    } else {
        $updated[] = [
            'title' => $item['section_title'],
            'icon' => $item['newIcon'],
            'success' => false
        ];
    }
}

$successCount = array_sum(array_map(fn($u) => $u['success'] ? 1 : 0, $updated));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Biography Icons - Update Complete</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-top: 0; }
        .success-banner { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .update-list { list-style: none; padding: 0; }
        .update-list li { padding: 10px; margin: 5px 0; background: #f8f9fa; border-left: 4px solid #28a745; }
        .update-list .icon { font-size: 1.4em; margin-right: 10px; }
        .actions { text-align: center; margin: 30px 0; }
        a { padding: 12px 24px; background: #0275d8; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        a:hover { background: #0257c4; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Biography Icons Updated</h1>
        <div class="success-banner">
            <strong><?= $successCount ?> of <?= count($updated) ?> sections updated successfully!</strong>
        </div>
        
        <h3>Updates Applied:</h3>
        <ul class="update-list">
            <?php foreach ($updated as $u): ?>
            <li>
                <span class="icon"><?= htmlspecialchars($u['icon']) ?></span>
                <strong><?= htmlspecialchars($u['title']) ?></strong>
                <?= $u['success'] ? '✓' : '✗ Failed' ?>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <div class="actions">
            <a href="/admin/pages/biography.php">← Return to Biography Editor</a>
        </div>
        
        <hr style="margin: 30px 0; opacity: 0.3;">
        <p style="color: #666; font-size: 0.9em;">
            <strong>🔒 Security Note:</strong> For security purposes, you should delete this file after use:
            <code>/admin/fix_bio_icons.php</code>
        </p>
    </div>
</body>
</html>
<?php
