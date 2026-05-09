<?php
/**
 * API: Suggest Photo
 * POST /api/suggest_photo.php
 * Allows logged-in users to upload photo suggestions
 * Images are stored in uploads/images/ and awaiting admin approval
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
    echo json_encode(['success' => false, 'message' => 'You must be logged in to suggest a photo.']);
    exit;
}

$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!verifyCsrf($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

$userId = $_SESSION['user_id'];
$caption = trim($_POST['caption'] ?? '');
$category = strtolower(trim($_POST['category'] ?? 'special'));

// Validate category
$validCats = ['childhood', 'family', 'work', 'celebrations', 'travels', 'special'];
if (!in_array($category, $validCats)) {
    $category = 'special';
}

if (empty($_FILES['photo']['name'])) {
    echo json_encode(['success' => false, 'message' => 'Please select a photo to upload.']);
    exit;
}

$allowedImages = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$uploadResult = handleUpload($_FILES['photo'], 'images', $allowedImages, 5);

if (!$uploadResult['success']) {
    echo json_encode(['success' => false, 'message' => 'Upload failed: ' . $uploadResult['message']]);
    exit;
}

try {
    $db = getDB();
    
    // Get user info
    $stmt = $db->prepare("SELECT full_name, username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // Record the suggestion (optional: create a suggestions table for tracking)
    // For now, just confirm the upload was successful
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your photo has been submitted for review. The admin will add it to the gallery soon.',
        'caption' => htmlspecialchars($caption),
        'category' => $category,
        'user_name' => $user['full_name'] ?? $user['username'],
    ]);

} catch (Exception $e) {
    error_log('suggest_photo error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again.']);
}
