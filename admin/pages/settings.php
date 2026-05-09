<?php
/**
 * Admin — Site Settings
 */
define('ADMIN_PAGE', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$pageTitle = 'Settings';
$db = getDB();
$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $settings = [
        'site_title'              => trim($_POST['site_title'] ?? 'In Loving Memory'),
        'deceased_name'           => trim($_POST['deceased_name'] ?? ''),
        'birth_year'              => trim($_POST['birth_year'] ?? ''),
        'death_year'              => trim($_POST['death_year'] ?? ''),
        'memorial_quote'          => trim($_POST['memorial_quote'] ?? ''),
        'footer_message'          => trim($_POST['footer_message'] ?? ''),
        'auto_approve_members'    => isset($_POST['auto_approve_members']) ? '1' : '0',
        'auto_approve_testimonies'=> isset($_POST['auto_approve_testimonies']) ? '1' : '0',
        'auto_approve_guestbook'  => isset($_POST['auto_approve_guestbook']) ? '1' : '0',
        'music_file'              => trim($_POST['ambient_music_url'] ?? ''),
        'ambient_music_url'       => trim($_POST['ambient_music_url'] ?? ''),  // keep legacy key in sync
        'visit_counter_visible'   => isset($_POST['visit_counter_visible']) ? '1' : '0',
        'petals_enabled'          => isset($_POST['petals_enabled']) ? '1' : '0',
    ];

    $upsert = $db->prepare("
        INSERT INTO settings (setting_key, setting_value) VALUES (?,?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    foreach ($settings as $key => $value) {
        $upsert->execute([$key, $value]);
    }
    $msg = 'Settings saved successfully.';
}

// Load all settings
$rows = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
$s = [];
foreach ($rows as $row) $s[$row['setting_key']] = $row['setting_value'];

$g = fn($key, $default='') => $s[$key] ?? $default;

include '../includes/header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
    <?= htmlspecialchars($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST">
<?= csrfField() ?>
<div class="row g-4">

    <!-- Memorial Info -->
    <div class="col-md-6">
        <div class="admin-card p-4">
            <h6 class="mb-3">🪦 Memorial Information</h6>
            <div class="mb-3">
                <label class="form-label small">Site Title</label>
                <input type="text" name="site_title" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($g('site_title','In Loving Memory')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small">Deceased Full Name</label>
                <input type="text" name="deceased_name" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($g('deceased_name','Hercio Maria da Neves Campos')) ?>">
            </div>
            <div class="row g-2 mb-3">
                <div class="col">
                    <label class="form-label small">Birth Year</label>
                    <input type="text" name="birth_year" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($g('birth_year','1945')) ?>">
                </div>
                <div class="col">
                    <label class="form-label small">Death Year</label>
                    <input type="text" name="death_year" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($g('death_year','2024')) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small">Memorial Quote (hero section)</label>
                <textarea name="memorial_quote" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($g('memorial_quote')) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label small">Footer Message</label>
                <input type="text" name="footer_message" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($g('footer_message','In memory of Hercio Maria da Neves Campos, love continues.')) ?>">
            </div>
        </div>
    </div>

    <!-- Moderation & Features -->
    <div class="col-md-6">
        <div class="admin-card p-4 mb-4">
            <h6 class="mb-3">🛡️ Moderation</h6>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="auto_approve_members" id="aam"
                       <?= $g('auto_approve_members','0')==='1' ? 'checked' : '' ?>>
                <label class="form-check-label small" for="aam">Auto-approve new member registrations</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="auto_approve_testimonies" id="aat"
                       <?= $g('auto_approve_testimonies','0')==='1' ? 'checked' : '' ?>>
                <label class="form-check-label small" for="aat">Auto-approve testimonies</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="auto_approve_guestbook" id="aag"
                       <?= $g('auto_approve_guestbook','1')==='1' ? 'checked' : '' ?>>
                <label class="form-check-label small" for="aag">Auto-approve guestbook entries</label>
            </div>
        </div>

        <div class="admin-card p-4">
            <h6 class="mb-3">✨ Features</h6>
            <div class="mb-3">
                <label class="form-label small">Ambient Music URL</label>
                <input type="url" name="ambient_music_url" class="form-control form-control-sm"
                       placeholder="https://… .mp3"
                       value="<?= htmlspecialchars($g('music_file', $g('ambient_music_url',''))) ?>">
                <div class="form-text">Leave empty to disable ambient music toggle.</div>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="visit_counter_visible" id="vcv"
                       <?= $g('visit_counter_visible','1')==='1' ? 'checked' : '' ?>>
                <label class="form-check-label small" for="vcv">Show visit counter publicly</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="petals_enabled" id="pe"
                       <?= $g('petals_enabled','1')==='1' ? 'checked' : '' ?>>
                <label class="form-check-label small" for="pe">Enable floating petals effect</label>
            </div>
        </div>
    </div>

    <div class="col-12 text-end">
        <button type="submit" class="btn btn-memorial px-5">Save Settings</button>
    </div>
</div>
</form>

<?php include '../includes/footer.php'; ?>
