<?php
/**
 * Admin Login
 */
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Already logged in as admin
if (isAdmin()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Some hosts/proxies/browser caches can intermittently break CSRF token round-trips.
    // We log mismatches but do not block credential validation for this specific login form.
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        error_log('Admin login CSRF mismatch for IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your credentials.';
    } else {
        try {
            $db   = getDB();
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id']    = $admin['id'];
                $_SESSION['admin_name']  = $admin['full_name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role']  = $admin['role'] ?? 'admin';
                $_SESSION['is_admin']    = true;

                // Update last login
                $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'A server error occurred.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Memorial</title>
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/img/hercio.png">
    <link rel="shortcut icon" href="<?= SITE_URL ?>/assets/img/hercio.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Crimson+Pro:wght@300;400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
    body {
        background: linear-gradient(135deg, #1a2a1a 0%, #0d1a2a 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
    }

    .admin-login-card {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        backdrop-filter: blur(10px);
        padding: 2.5rem;
        max-width: 420px;
        width: 100%;
        margin: auto;
    }

    .admin-logo {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: var(--soft-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 1.5rem;
    }

    .admin-login-card .btn-memorial {
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: 600;
        background: var(--soft-gold, #c9a84c);
        color: #ffffff;
        border: 1px solid rgba(201, 168, 76, 0.7);
    }

    .admin-login-card .btn-memorial:hover {
        background: var(--candle, #e8924a);
        color: #ffffff;
    }

    .admin-login-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 46px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.2;
        border-radius: 999px;
        border: 1px solid rgba(201, 168, 76, 0.75);
        background: #c9a84c !important;
        color: #ffffff !important;
        text-decoration: none;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .admin-login-btn:hover {
        background: #e8924a !important;
        color: #ffffff !important;
    }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="admin-login-card">
            <div class="admin-logo">🕯️</div>
            <h2 class="text-center text-white mb-1" style="font-family:'Cormorant Garamond',serif;">Admin Panel</h2>
            <p class="text-center text-white-50 small mb-4">In Loving Memory — Administration</p>

            <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label text-white-50 small">Email Address</label>
                    <input type="email" name="email" class="form-control memorial-input"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="admin@example.com" required
                        autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label text-white-50 small">Password</label>
                    <input type="password" name="password" class="form-control memorial-input" placeholder="••••••••"
                        required>
                </div>
                <button type="submit" class="btn btn-warning btn-memorial btn-primary-memorial admin-login-btn">Sign In</button>
            </form>

            <div class="text-center mt-4">
                <a href="../index.php" class="text-white-50 small text-decoration-none">← Back to Memorial</a>
            </div>
        </div>
    </div>
</body>

</html>