<?php
/**
 * API: Submit Prayer
 * POST /api/submit_prayer.php
 * Requires authentication
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
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a prayer.']);
    exit;
}

$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!verifyCsrf($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

$db          = getDB();
$userId      = $_SESSION['user_id'];
$title       = trim($_POST['title'] ?? '');
$prayerText  = trim($_POST['prayer_text'] ?? '');
$category    = trim($_POST['category'] ?? 'Peace');
$visibility  = ($_POST['visibility'] ?? 'public') === 'private' ? 'private' : 'public';

$validCategories = ['Peace', 'Gratitude', 'Healing', 'Family', 'Eternal Rest'];

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a prayer title.']);
    exit;
}
if (empty($prayerText)) {
    echo json_encode(['success' => false, 'message' => 'Please write your prayer.']);
    exit;
}
if (mb_strlen($title) > 150) {
    echo json_encode(['success' => false, 'message' => 'Title must be under 150 characters.']);
    exit;
}
if (mb_strlen($prayerText) > 2000) {
    echo json_encode(['success' => false, 'message' => 'Prayer must be under 2000 characters.']);
    exit;
}
if (!in_array($category, $validCategories)) {
    $category = 'Peace';
}

try {
    $stmt = $db->prepare("SELECT full_name, username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $stmt = $db->prepare("
        INSERT INTO prayers (user_id, title, prayer_text, category, visibility, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $title, $prayerText, $category, $visibility]);

    $prayerId = $db->lastInsertId();

    // Total prayers
    $stmt = $db->prepare("SELECT COUNT(*) FROM prayers WHERE visibility = 'public'");
    $stmt->execute();
    $totalPrayers = $stmt->fetchColumn();

    // Category icon map
    $icons = [
        'Peace'        => '☮️',
        'Gratitude'    => '🙏',
        'Healing'      => '💛',
        'Family'       => '👨‍👩‍👧',
        'Eternal Rest' => '✨',
    ];

    echo json_encode([
        'success'       => true,
        'message'       => 'Your prayer has been received with love.',
        'prayer_id'     => $prayerId,
        'title'         => htmlspecialchars($title),
        'excerpt'       => mb_substr(htmlspecialchars($prayerText), 0, 120) . '...',
        'category'      => $category,
        'category_icon' => $icons[$category] ?? '🙏',
        'visibility'    => $visibility,
        'user_name'     => $user['full_name'] ?? $user['username'],
        'total_prayers' => (int)$totalPrayers,
    ]);

} catch (PDOException $e) {
    error_log('submit_prayer error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again.']);
}
