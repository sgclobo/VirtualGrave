<?php
/**
 * IN LOVING MEMORY — Login Page
 */
require_once __DIR__ . '/../includes/functions.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL . '/'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifyCsrf()) {
    $error = 'Invalid security token. Please refresh and try again.';
  }

  $ident    = trim($_POST['ident'] ?? '');
  $password = $_POST['password'] ?? '';

  if (!$error && (!$ident || !$password)) {
        $error = 'Please enter your email/username and password.';
  } elseif (!$error) {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE (email=? OR username=?) LIMIT 1');
        $stmt->execute([$ident, $ident]);
        $user = $stmt->fetch();

        if ($user && verifyPassword($password, $user['password_hash'])) {
            if ($user['status'] === 'pending') {
                $error = 'Your registration is pending approval. You will receive an email notification.';
            } elseif ($user['status'] === 'rejected') {
                $error = 'Your registration has not been approved. Please contact the family.';
            } else {
                // Success
                session_regenerate_id(true);
                $_SESSION['user_id']       = $user['id'];
                $_SESSION['user_approved'] = true;
                $_SESSION['user_name']     = $user['full_name'];

                $pdo->prepare('UPDATE users SET last_login=NOW() WHERE id=?')->execute([$user['id']]);

                $redirect = $_SESSION['redirect_after_login'] ?? SITE_URL . '/';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit;
            }
        } else {
            $error = 'Invalid email/username or password.';
        }
    }
}

$pageTitle = 'Sign In';
$pageClass = 'auth-page';
include __DIR__ . '/../includes/header.php';
?>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <span class="auth-cross">✝</span>
      <h1 class="auth-title">Sign In</h1>
      <p class="auth-subtitle">Enter the memorial garden</p>
    </div>

    <?php if ($error): ?>
    <div class="flash-message flash-error mb-3">
      <span class="flash-icon">✕</span>
      <span><?= e($error) ?></span>
    </div>
    <?php endif; ?>

    <?= showFlash() ?>

    <form method="POST" class="memorial-form">
      <?= csrfField() ?>

      <div class="mb-3">
        <label class="form-label" for="ident">Email or Username</label>
        <input type="text" class="form-control" id="ident" name="ident"
          value="<?= e($_POST['ident'] ?? '') ?>" required autofocus placeholder="your@email.com">
      </div>

      <div class="mb-4">
        <label class="form-label" for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
      </div>

      <button type="submit" class="btn-auth btn-ripple">Enter Memorial</button>

      <p class="text-center mt-3" style="font-size:0.9rem;color:var(--text-second);">
        Not yet a member?
        <a href="<?= SITE_URL ?>/pages/register.php" class="text-gold">Register here</a>
      </p>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
