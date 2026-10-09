<?php
require_once __DIR__ . '/config/config.php';

$error = '';
$success = '';

if (is_logged_in()) {
    redirect(url(''));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if (strlen($name) < 2) {
        $error = 'Name must be at least 2 characters.';
    } elseif (!$email) {
        $error = 'Please enter a valid email.';
    } elseif ($msg = validate_password_strength($password)) {
        $error = $msg;
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = register_user($name, $email, $password);
        if ($result['success']) {
            $verifyUrl = url('verify.php?token=' . $result['verification_token']);
            $success = 'Registration successful! <a href="' . e($verifyUrl) . '">Click here to verify your email</a> (simulated).';
        } else {
            $error = $result['message'];
        }
    }
}

$meta = page_meta('Register');
require APP_ROOT . '/includes/header.php';
?>

<div class="form-page">
  <div class="form-card">
    <h1>Create Account</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if (!$success): ?>
    <form method="post" action="" id="registerForm">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required minlength="2" value="<?= e($_POST['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8"
               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
               title="Min 8 chars, uppercase, lowercase, number">
        <small style="color:var(--text-muted);">Min 8 characters with uppercase, lowercase, and number</small>
      </div>
      <div class="form-group">
        <label for="password_confirm">Confirm Password</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>
    <?php endif; ?>
    <div class="form-footer">
      <p>Already have an account? <a href="<?= url('login.php') ?>">Login</a></p>
    </div>
  </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
