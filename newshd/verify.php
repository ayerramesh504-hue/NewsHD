<?php
require_once __DIR__ . '/config/config.php';

$token = sanitize($_GET['token'] ?? '');
$verified = $token && verify_email_token($token);

$meta = page_meta('Email Verification');
require APP_ROOT . '/includes/header.php';
?>

<div class="form-page">
  <div class="form-card">
    <?php if ($verified): ?>
    <div class="alert alert-success">Your email has been verified successfully!</div>
    <a href="<?= url('login.php') ?>" class="btn btn-primary btn-block">Login Now</a>
    <?php else: ?>
    <div class="alert alert-error">Invalid or expired verification link.</div>
    <a href="<?= url('') ?>" class="btn btn-primary btn-block">Go Home</a>
    <?php endif; ?>
  </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
