<?php
require_once __DIR__ . '/config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    if (!$email || !$password) {
        $error = 'Please enter email and password.';
    } else {
        $result = authenticate($email, $password);
        if ($result['success']) {
            login_user($result['user'], $remember);
            $redirect = $_SESSION['redirect_after_login'] ?? url('');
            unset($_SESSION['redirect_after_login']);
            if (is_admin() && !empty($_POST['admin_login'])) {
                $redirect = url('admin/');
            }
            redirect($redirect);
        }
        $error = $result['message'];
    }
}

if (is_logged_in()) {
    redirect(url(''));
}

$meta = page_meta('Login');
require APP_ROOT . '/includes/header.php';
?>

<div class="form-page">
  <div class="form-card">
    <h1>Login</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autocomplete="email"
               value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="remember" value="1"> Remember me</label>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Sign In</button>
    </form>
    <div class="form-footer">
      <p><a href="<?= url('forgot-password.php') ?>">Forgot password?</a></p>
      <p>Don't have an account? <a href="<?= url('register.php') ?>">Register</a></p>
      <p style="margin-top:12px;font-size:12px;">Demo: user@newshd.com / password</p>
    </div>
  </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
