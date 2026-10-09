<?php
require_once dirname(__DIR__) . '/config/config.php';

if (is_admin()) {
    redirect(url('admin/'));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $result = authenticate($email, $password);
    if ($result['success'] && in_array($result['user']['role_slug'], ['admin', 'editor', 'author'], true)) {
        login_user($result['user'], !empty($_POST['remember']));
        redirect(url('admin/'));
    }
    $error = $result['success'] ? 'You do not have admin access.' : ($result['message'] ?? 'Login failed.');
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | NEWSHD</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin-login-page">
  <div class="login-card">
    <h1>NEWSHD Admin</h1>
    <p>Sign in to manage the news portal</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <label><input type="checkbox" name="remember" value="1"> Remember me</label>
      <button type="submit" class="btn btn-primary btn-block">Sign In</button>
    </form>
    <p class="login-hint">Demo: admin@newshd.com / password</p>
    <a href="<?= url('') ?>" class="back-link">&larr; Back to site</a>
  </div>
  <script src="<?= asset('js/theme.js') ?>"></script>
</body>
</html>
