<?php
/**
 * API: Deposit Flower
 * POST /api/deposit_flower.php
 * Requires authentication
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Must be logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to deposit flowers.']);
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!verifyCsrf($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

$db = getDB();
$userId   = $_SESSION['user_id'];
$flowerId = (int)($_POST['flower_id'] ?? 0);
$message  = trim($_POST['message'] ?? '');

// Validate flower
if ($flowerId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a flower.']);
    exit;
}

// Message length
if (mb_strlen($message) > 500) {
    echo json_encode(['success' => false, 'message' => 'Message must be under 500 characters.']);
    exit;
}

try {
    // Get flower details
    $stmt = $db->prepare("SELECT * FROM flowers_catalog WHERE id = ? AND is_active = 1");
    $stmt->execute([$flowerId]);
    $flower = $stmt->fetch();

    if (!$flower) {
        echo json_encode(['success' => false, 'message' => 'Flower not found.']);
        exit;
    }

    // Get user info
    $stmt = $db->prepare("SELECT full_name, username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // Insert deposited flower
    $stmt = $db->prepare("
        INSERT INTO deposited_flowers (user_id, flower_id, message, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $flowerId, $message ?: null]);

    // Get updated count
    $stmt = $db->prepare("SELECT COUNT(*) FROM deposited_flowers");
    $stmt->execute();
    $totalFlowers = $stmt->fetchColumn();

    echo json_encode([
        'success'      => true,
        'message'      => 'Your flower has been placed with love.',
        'flower_emoji' => $flower['flower_emoji'] ?? '🌹',
        'flower_name'  => $flower['flower_name'],
        'flower_color' => $flower['color'],
        'user_name'    => $user['full_name'] ?? $user['username'],
        'dedication'   => $message,
        'total_flowers'=> (int)$totalFlowers,
    ]);

} catch (PDOException $e) {
    error_log('deposit_flower error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again.']);
}
