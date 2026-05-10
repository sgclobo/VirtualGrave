<?php
/**
 * API: Submit Testimony
 * POST /api/submit_testimony.php
 * Requires authentication — supports optional image upload
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to share a testimony.']);
    exit;
}

$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!verifyCsrf($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

$db            = getDB();
$userId        = $_SESSION['user_id'];
$title         = trim($_POST['title'] ?? '');
$testimonyText = trim($_POST['testimony_text'] ?? '');
$imagePath     = null;

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a title for your testimony.']);
    exit;
}
if (empty($testimonyText)) {
    echo json_encode(['success' => false, 'message' => 'Please write your testimony.']);
    exit;
}
if (mb_strlen($title) > 200) {
    echo json_encode(['success' => false, 'message' => 'Title must be under 200 characters.']);
    exit;
}
if (mb_strlen($testimonyText) > 3000) {
    echo json_encode(['success' => false, 'message' => 'Testimony must be under 3000 characters.']);
    exit;
}

// Handle optional image upload
if (!empty($_FILES['testimony_image']['name'])) {
    $uploadResult = handleUpload($_FILES['testimony_image'], 'testimonies', ['image/jpeg','image/png','image/webp','image/gif'], 5);
    if ($uploadResult['success']) {
        $imagePath = $uploadResult['path'];
    } else {
        echo json_encode(['success' => false, 'message' => 'Image upload failed: ' . $uploadResult['message']]);
        exit;
    }
}

try {
    $stmt = $db->prepare("SELECT full_name, username, avatar FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $stmt = $db->prepare("
        INSERT INTO testimonies (user_id, title, testimony_text, image_path, is_approved, created_at)
        VALUES (?, ?, ?, ?, 0, NOW())
    ");
    $stmt->execute([$userId, $title, $testimonyText, $imagePath]);

    $testimonyId = $db->lastInsertId();

    // Check auto-approve setting
    $autoApprove = getSetting('auto_approve_testimonies', '0');
    if ($autoApprove === '1') {
        $stmt = $db->prepare("UPDATE testimonies SET is_approved = 1 WHERE id = ?");
        $stmt->execute([$testimonyId]);
        $approvedMsg = 'Your testimony is now visible to all visitors.';
    } else {
        $approvedMsg = 'Your testimony is awaiting approval and will appear soon.';
    }

    echo json_encode([
        'success'      => true,
        'message'      => 'Thank you for sharing your memory. ' . $approvedMsg,
        'testimony_id' => $testimonyId,
        'title'        => htmlspecialchars($title),
        'excerpt'      => mb_substr(htmlspecialchars($testimonyText), 0, 150) . '...',
        'user_name'    => $user['full_name'] ?? $user['username'],
        'has_image'    => $imagePath !== null,
    ]);

} catch (PDOException $e) {
    error_log('submit_testimony error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again.']);
}
