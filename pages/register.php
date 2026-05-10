<?php
/**
 * IN LOVING MEMORY — Registration Page
 */
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) { header('Location: ' . SITE_URL . '/'); exit; }

$errors = [];
$values = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $values = [
        'full_name'   => trim($_POST['full_name'] ?? ''),
        'username'    => trim($_POST['username'] ?? ''),
        'email'       => trim($_POST['email'] ?? ''),
        'country'     => trim($_POST['country'] ?? ''),
        'relationship' => $_POST['relationship'] ?? '',
        'family_detail'=> trim($_POST['family_detail'] ?? ''),
        'personal_message' => trim($_POST['personal_message'] ?? ''),
    ];
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validate
    if (!$values['full_name'])                       $errors[] = 'Full name is required.';
    if (!$values['username'] || strlen($values['username']) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $values['username']))   $errors[] = 'Username may only contain letters, numbers, underscores.';
    if (!validateEmail($values['email']))             $errors[] = 'Please enter a valid email address.';
    if (!validatePassword($password))                 $errors[] = 'Password must be at least 8 characters with letters and numbers.';
    if ($password !== $password2)                     $errors[] = 'Passwords do not match.';
    if (!$values['relationship'])                     $errors[] = 'Please select your relationship.';

    if (empty($errors)) {
        $pdo = getDB();

        // Check uniqueness
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email=? OR username=?');
        $stmt->execute([$values['email'], $values['username']]);
        if ($stmt->fetch()) {
            $errors[] = 'Email or username already taken.';
        } else {
            // Handle profile photo
            $photoPath = null;
            if (!empty($_FILES['profile_photo']['name'])) {
                $photoPath = handleUpload($_FILES['profile_photo'], 'avatars', ['jpg','jpeg','png','gif','webp']);
                if (!$photoPath) $errors[] = 'Invalid profile photo. Use JPG/PNG under 5MB.';
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare('
                    INSERT INTO users
                        (full_name, username, email, password_hash, country, relationship, family_relation_detail, profile_photo, personal_message, status)
                    VALUES (?,?,?,?,?,?,?,?,?,?)
                ');
                $stmt->execute([
                    $values['full_name'],
                    $values['username'],
                    $values['email'],
                    hashPassword($password),
                    $values['country'],
                    $values['relationship'],
                    $values['family_detail'] ?: null,
                    $photoPath,
                    $values['personal_message'] ?: null,
                    getSetting('auto_approve_members','0') ? 'approved' : 'pending',
                ]);

                flashMessage('success', 'Registration submitted! You will be notified once your account is approved.');
                header('Location: ' . SITE_URL . '/pages/login.php');
                exit;
            }
        }
    }
}

$pageTitle  = 'Register';
$pageClass  = 'auth-page';
$activePage = 'register';
include __DIR__ . '/../includes/header.php';
?>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>

<div class="auth-page">
  <div class="auth-card" style="max-width:600px;">
    <div class="auth-logo">
      <span class="auth-cross">✝</span>
      <h1 class="auth-title">Join the Memorial</h1>
      <p class="auth-subtitle">Create an account to light candles, leave flowers and share memories</p>
    </div>

    <?php if ($errors): ?>
    <div class="flash-message flash-error mb-3">
      <span class="flash-icon">✕</span>
      <div>
        <?php foreach ($errors as $e): ?>
        <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="memorial-form" novalidate>
      <?= csrfField() ?>

      <div class="row g-3">
        <div class="col-12">
          <label class="form-label" for="full_name">Full Name *</label>
          <input type="text" class="form-control" id="full_name" name="full_name"
            value="<?= e($values['full_name'] ?? '') ?>" required placeholder="Maria Silva">
        </div>

        <div class="col-md-6">
          <label class="form-label" for="username">Username *</label>
          <input type="text" class="form-control" id="username" name="username"
            value="<?= e($values['username'] ?? '') ?>" required placeholder="mariasilva">
        </div>

        <div class="col-md-6">
          <label class="form-label" for="email">Email *</label>
          <input type="email" class="form-control" id="email" name="email"
            value="<?= e($values['email'] ?? '') ?>" required placeholder="maria@example.com">
        </div>

        <div class="col-md-6">
          <label class="form-label" for="password">Password *</label>
          <input type="password" class="form-control" id="password" name="password" required
            placeholder="At least 8 characters">
        </div>

        <div class="col-md-6">
          <label class="form-label" for="password2">Confirm Password *</label>
          <input type="password" class="form-control" id="password2" name="password2" required
            placeholder="Repeat password">
        </div>

        <div class="col-md-6">
          <label class="form-label" for="country">Country</label>
          <input type="text" class="form-control" id="country" name="country"
            value="<?= e($values['country'] ?? '') ?>" placeholder="Timor-Leste">
        </div>

        <div class="col-md-6">
          <label class="form-label" for="relationship">Your Relationship *</label>
          <select class="form-select" id="relationship" name="relationship">
            <option value="">— Select —</option>
            <?php foreach ([
              'family'       => 'Family Member',
              'relative'     => 'Close Relative',
              'friend'       => 'Friend',
              'acquaintance' => 'Acquaintance',
              'colleague'    => 'Colleague',
              'neighbor'     => 'Neighbor',
              'spiritual'    => 'Spiritual Companion',
            ] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($values['relationship']??'')===$val?'selected':'' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Family Detail — shown conditionally -->
        <div class="col-12" id="familyDetailGroup" style="display:none;">
          <label class="form-label" for="family_detail">Specify Family Relationship</label>
          <select class="form-select" id="family_detail" name="family_detail">
            <option value="">— Select —</option>
            <?php foreach (['Son','Daughter','Brother','Sister','Spouse','Cousin','Uncle','Aunt','Grandchild','In-law','Other'] as $r): ?>
            <option value="<?= $r ?>" <?= ($values['family_detail']??'')===$r?'selected':'' ?>><?= $r ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label" for="profile_photo">Profile Photo (optional)</label>
          <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
          <div class="form-text text-muted">JPG or PNG, max 5MB</div>
        </div>

        <div class="col-12">
          <label class="form-label" for="personal_message">Personal Message (optional)</label>
          <textarea class="form-control" id="personal_message" name="personal_message" rows="3"
            placeholder="Why this person means so much to me…" maxlength="500"><?= e($values['personal_message'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="mt-4">
        <button type="submit" class="btn-auth btn-ripple">Register for Memorial</button>
      </div>

      <p class="text-center mt-3" style="font-size:0.9rem;color:var(--text-second);">
        Already registered? <a href="<?= SITE_URL ?>/pages/login.php" class="text-gold">Sign in</a>
      </p>
      <p class="text-center" style="font-size:0.82rem;color:var(--text-light);margin-top:0.5rem;">
        Your registration will be reviewed and approved by the family.
      </p>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
