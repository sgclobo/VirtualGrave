<?php
/**
 * IN LOVING MEMORY — Core Functions & Security Helpers
 */

require_once __DIR__ . '/config.php';

// ─── HTTP Security Headers ───────────────────────────────
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (($_SERVER['SERVER_PORT'] ?? '') === '443') ||
        (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    );

    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// ─── Session Security ──────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}


// --- Nav Items Array -----
$navItems = [
    ['page' => 'index',     'icon' => '📊', 'label' => 'Dashboard',        'path' => '../index.php'],
    ['page' => 'members',   'icon' => '👥', 'label' => 'Members',           'path' => 'pages/members.php'],
    ['page' => 'moderate',  'icon' => '🛡️',  'label' => 'Moderate Content', 'path' => 'pages/moderate.php'],
    ['page' => 'gallery',   'icon' => '🖼️',  'label' => 'Gallery',          'path' => 'pages/gallery.php'],
    ['page' => 'flowers',   'icon' => '🌹', 'label' => 'Flowers Catalog',   'path' => 'pages/flowers.php'],
    ['page' => 'candles',   'icon' => '🕯️', 'label' => 'Candles Catalog',   'path' => 'pages/candles.php'],
    ['page' => 'biography', 'icon' => '📖', 'label' => 'Biography',         'path' => 'pages/biography.php'],
    ['page' => 'timeline',  'icon' => '📅', 'label' => 'Timeline',          'path' => 'pages/timeline.php'],
    ['page' => 'settings',  'icon' => '⚙️', 'label' => 'Settings',          'path' => 'pages/settings.php'],
];

// ─── CSRF Protection ───────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf(?string $token = null): bool {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }

    if ($token === '') {
        return false;
    }

    return hash_equals(csrfToken(), $token);
}

// ─── Output Helpers ────────────────────────────────────────
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function jsonSuccess(string $message, array $data = []): string {
    header('Content-Type: application/json');
    return json_encode(['success' => true, 'message' => $message] + $data);
}

function jsonError(string $message, int $code = 400): string {
    header('Content-Type: application/json');
    http_response_code($code);
    return json_encode(['success' => false, 'message' => $message]);
}

// ─── Auth Helpers ──────────────────────────────────────────
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['user_approved']);
}

function isAdmin(): bool {
    return !empty($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/pages/login.php');
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND status = "approved"');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

// ─── Password ──────────────────────────────────────────────
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

// ─── Upload Helpers ────────────────────────────────────────
function handleUpload(array $file, string $subdir, array $allowed = ['jpg','jpeg','png','gif','webp'], int $maxMB = 5): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error.', 'path' => null];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return ['success' => false, 'message' => 'File type not allowed.', 'path' => null];
    }
    
    $maxBytes = $maxMB * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'message' => "File must be under {$maxMB}MB.", 'path' => null];
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $validMimes = ['image/jpeg','image/png','image/gif','image/webp','video/mp4','video/webm','video/ogg'];
    if (!in_array($mime, $validMimes, true)) {
        return ['success' => false, 'message' => 'Invalid file type.', 'path' => null];
    }

    $dir = UPLOAD_DIR . $subdir . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $newName = uniqid('', true) . '.' . $ext;
    $dest    = $dir . $newName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false, 'message' => 'Failed to move upload file.', 'path' => null];
    }

    return ['success' => true, 'path' => $subdir . '/' . $newName];
}

// ─── Validation ────────────────────────────────────────────
function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword(string $pw): bool {
    // Min 8 chars, at least one letter and one number
    return strlen($pw) >= 8 && preg_match('/[A-Za-z]/', $pw) && preg_match('/[0-9]/', $pw);
}

// ─── Stats ─────────────────────────────────────────────────
function getMemorialStats(): array {
    $pdo = getDB();
    $stats = [];
    foreach ([
        'candles'     => 'SELECT COUNT(*) FROM lit_candles WHERE is_approved=1',
        'flowers'     => 'SELECT COUNT(*) FROM deposited_flowers WHERE is_approved=1',
        'prayers'     => 'SELECT COUNT(*) FROM prayers WHERE is_approved=1 AND visibility="public"',
        'testimonies' => 'SELECT COUNT(*) FROM testimonies WHERE is_approved=1',
        'visitors'    => 'SELECT COUNT(DISTINCT ip_address) FROM visit_log',
        'members'     => 'SELECT COUNT(*) FROM users WHERE status="approved"',
    ] as $key => $sql) {
        $stats[$key] = (int)$pdo->query($sql)->fetchColumn();
    }
    return $stats;
}

// ─── Log Visit ─────────────────────────────────────────────
function logVisit(string $page = 'home'): void {
    try {
        $ip   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ip   = substr($ip, 0, 45);
        $pdo  = getDB();
        $stmt = $pdo->prepare('INSERT INTO visit_log (ip_address, page) VALUES (?, ?)');
        $stmt->execute([$ip, $page]);
    } catch (Exception $e) {
        // Non-critical — fail silently
    }
}

// ─── Flash Messages ────────────────────────────────────────
function flashMessage(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function showFlash(): string {
    $flash = getFlash();
    if (!$flash) return '';
    $icons = ['success' => '✓', 'error' => '✕', 'info' => 'ℹ', 'warning' => '⚠'];
    $icon  = $icons[$flash['type']] ?? 'ℹ';
    return '<div class="flash-message flash-' . e($flash['type']) . '" id="flashMsg">
        <span class="flash-icon">' . $icon . '</span>
        <span>' . e($flash['msg']) . '</span>
        <button onclick="this.parentElement.remove()" class="flash-close">×</button>
    </div>';
}