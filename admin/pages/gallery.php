<?php
/**
 * Admin — Gallery Management
 */
define('ADMIN_PAGE', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$pageTitle = 'Gallery Management';
$db = getDB();
$msg = ''; $msgType = 'success';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $msg = 'Invalid security token.'; $msgType = 'danger';
    } else {
        $action = $_POST['action'] ?? 'upload';

        if ($action === 'delete') {
            $id   = (int)($_POST['item_id'] ?? 0);
            $stmt = $db->prepare("SELECT file_path FROM gallery WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch();
            if ($item) {
                $fullPath = UPLOAD_DIR . 'gallery/' . $item['file_path'];
                if (file_exists($fullPath)) @unlink($fullPath);
                $db->prepare("DELETE FROM gallery WHERE id = ?")->execute([$id]);
                $msg = 'Item deleted from gallery.';
            }
        } else {
            // Upload
            $title    = trim($_POST['caption'] ?? '');  // Map form field to title column
            $category = strtolower(trim($_POST['category'] ?? 'special'));
            $fileType = 'photo';
            $validCats = ['childhood','family','work','celebrations','travels','special'];
            if (!in_array($category, $validCats)) $category = 'special';

            if (empty($_FILES['media_file']['name'])) {
                $msg = 'Please select a file.'; $msgType = 'danger';
            } else {
                $allowedImages = ['image/jpeg','image/png','image/webp','image/gif'];
                $allowedVideos = ['video/mp4','video/webm','video/ogg'];
                $allowedMimes  = array_merge($allowedImages, $allowedVideos);

                $uploadResult = handleUpload($_FILES['media_file'], 'gallery', $allowedMimes, 50);

                if ($uploadResult['success']) {
                    $mime = mime_content_type(UPLOAD_DIR . 'gallery/' . $uploadResult['path']);
                    $fileType = in_array($mime, $allowedVideos) ? 'video' : 'photo';

                    $stmt = $db->prepare("
                        INSERT INTO gallery (file_path, title, category, file_type)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$uploadResult['path'], $title, $category, $fileType]);
                    $msg = 'Media uploaded successfully.';
                } else {
                    $msg = 'Upload failed: ' . $uploadResult['message']; $msgType = 'danger';
                }
            }
        }
    }
}

// Fetch gallery
$filter = strtolower($_GET['category'] ?? '');
$validCats = ['childhood','family','work','celebrations','travels','special'];
if (!in_array($filter, $validCats)) $filter = '';

$where  = '1=1';
$params = [];
if ($filter) { $where .= ' AND category = ?'; $params[] = $filter; }

$stmt = $db->prepare("SELECT * FROM gallery WHERE $where ORDER BY uploaded_at DESC");
$stmt->execute($params);
$items = $stmt->fetchAll();

include '../includes/header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
    <?= htmlspecialchars($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Upload Form -->
    <div class="col-md-4">
        <div class="admin-card p-4">
            <h6 class="mb-3">🖼️ Upload Media</h6>
            <form method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="upload">
                <div class="mb-3">
                    <label class="form-label small">File <span class="text-danger">*</span></label>
                    <input type="file" name="media_file" class="form-control form-control-sm"
                           accept="image/*,video/*" required>
                    <div class="form-text">Images: JPG, PNG, WEBP, GIF. Videos: MP4, WEBM. Max 50MB.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Caption</label>
                    <input type="text" name="caption" class="form-control form-control-sm" maxlength="255"
                           placeholder="A short description…">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <?php foreach ($validCats as $cat): ?>
                        <option><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-memorial w-100">Upload</button>
            </form>
        </div>
    </div>

    <!-- Gallery Grid -->
    <div class="col-md-8">
        <!-- Category filter -->
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="gallery.php" class="btn btn-sm <?= !$filter?'btn-dark':'btn-outline-secondary' ?> rounded-pill">All</a>
            <?php foreach ($validCats as $cat): ?>
            <a href="gallery.php?category=<?= urlencode($cat) ?>"
               class="btn btn-sm <?= $filter===$cat?'btn-dark':'btn-outline-secondary' ?> rounded-pill">
               <?= $cat ?>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($items)): ?>
        <div class="text-center py-5 text-muted">No media yet. Upload some above.</div>
        <?php else: ?>
        <div class="row g-2">
            <?php foreach ($items as $item): ?>
            <div class="col-6 col-sm-4">
                <div class="position-relative overflow-hidden rounded" style="aspect-ratio:1;">
                    <?php if ($item['file_type'] === 'video'): ?>
                    <video src="../../uploads/gallery/<?= htmlspecialchars($item['file_path']) ?>"
                           class="w-100 h-100" style="object-fit:cover;"></video>
                    <div class="position-absolute top-50 start-50 translate-middle" style="font-size:2rem;opacity:0.8;">▶️</div>
                    <?php else: ?>
                    <img src="../../uploads/gallery/<?= htmlspecialchars($item['file_path']) ?>"
                         class="w-100 h-100" style="object-fit:cover;" alt="" loading="lazy">
                    <?php endif; ?>

                    <!-- Overlay -->
                    <div class="position-absolute bottom-0 start-0 end-0 p-2"
                         style="background:linear-gradient(transparent,rgba(0,0,0,0.7));">
                        <?php
                            $title = $item['title'] ?? $item['description'] ?? '';
                            $shortTitle = function_exists('mb_substr') ? mb_substr($title, 0, 30) : substr($title, 0, 30);
                        ?>
                        <div class="text-white small"><?= htmlspecialchars($shortTitle) ?></div>
                        <div class="text-white-50" style="font-size:0.65rem;"><?= htmlspecialchars(ucfirst($item['category'])) ?></div>
                    </div>

                    <!-- Delete -->
                    <form method="POST" class="position-absolute top-0 end-0 p-1"
                          onsubmit="return confirm('Delete this item?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <button class="btn btn-sm btn-danger rounded-circle"
                                style="width:26px;height:26px;padding:0;font-size:0.7rem;line-height:1;">✕</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
