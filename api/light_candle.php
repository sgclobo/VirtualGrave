<?php
/**
 * API: Light Candle
 * POST /api/light_candle.php
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
    echo json_encode(['success' => false, 'message' => 'You must be logged in to light a candle.']);
    exit;
}

$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!verifyCsrf($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

$db         = getDB();
$userId     = $_SESSION['user_id'];
$candleId   = (int)($_POST['candle_id'] ?? 0);
$dedication = trim($_POST['dedication'] ?? '');

if ($candleId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a candle.']);
    exit;
}

if (mb_strlen($dedication) > 300) {
    echo json_encode(['success' => false, 'message' => 'Dedication must be under 300 characters.']);
    exit;
}

try {
    // Get candle details
    $stmt = $db->prepare("SELECT * FROM candles_catalog WHERE id = ? AND is_active = 1");
    $stmt->execute([$candleId]);
    $candle = $stmt->fetch();

    if (!$candle) {
        echo json_encode(['success' => false, 'message' => 'Candle not found.']);
        exit;
    }

    // Get user
    $stmt = $db->prepare("SELECT full_name, username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // Insert lit candle
    $stmt = $db->prepare("
        INSERT INTO lit_candles (user_id, candle_id, dedication, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $candleId, $dedication ?: null]);

    // Total candles
    $stmt = $db->prepare("SELECT COUNT(*) FROM lit_candles");
    $stmt->execute();
    $totalCandles = $stmt->fetchColumn();

    echo json_encode([
        'success'       => true,
        'message'       => 'Your candle burns in love and memory.',
        'candle_name'   => $candle['candle_name'],
        'glow_color'    => $candle['glow_color'],
        'user_name'     => $user['full_name'] ?? $user['username'],
        'dedication'    => $dedication,
        'total_candles' => (int)$totalCandles,
    ]);

} catch (PDOException $e) {
    error_log('light_candle error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again.']);
}
