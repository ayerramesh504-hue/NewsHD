<?php
require_once __DIR__ . '/config/config.php';

$error = '';
$success = '';
$token = sanitize($_GET['token'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    require_csrf();
    $token = sanitize($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if ($msg = validate_password_strength($password)) {
        $error = $msg;
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (reset_password_with_token($token, $password)) {
        $success = 'Password reset successfully. You can now login.';
        $token = '';
    } else {
        $error = 'Invalid or expired reset link.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $error = 'Please enter a valid email.';
    } else {
        $resetToken = create_password_reset_token($email);
        if ($resetToken) {
            $resetUrl = url('forgot-password.php?token=' . $resetToken);
            $success = 'If that email exists, a reset link was generated. <a href="' . e($resetUrl) . '">Reset password (simulated)</a>';
        } else {
            $success = 'If that email exists, a reset link will be sent.';
        }
    }
}

$meta = page_meta('Forgot Password');
require APP_ROOT . '/includes/header.php';
?>

<div class="form-page">
  <div class="form-card">
    <h1><?= $token ? 'Reset Password' : 'Forgot Password' ?></h1>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <?php if ($token && !$success): ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <input type="hidden" name="reset" value="1">
      <div class="form-group">
        <label for="password">New Password</label>
        <input type="password" id="password" name="password" required minlength="8">
      </div>
      <div class="form-group">
        <label for="password_confirm">Confirm Password</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
    </form>
    <?php elseif (!$success): ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
    </form>
    <?php endif; ?>
    <div class="form-footer"><a href="<?= url('login.php') ?>">Back to Login</a></div>
  </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
