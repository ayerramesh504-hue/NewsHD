<?= view('layout/header') ?>

<div class="form-page">
  <div class="form-card">
    <h1><?= $token ? 'Reset Password' : 'Forgot Password' ?></h1>
    <p class="form-subtitle"><?= $token ? 'Choose a new password' : 'We will send you a reset link' ?></p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <?php if ($token && ! $success): ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <input type="hidden" name="reset" value="1">
      <div class="form-group">
        <label for="password">New Password</label>
        <input type="password" id="password" name="password" required minlength="8"
               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
               title="Min 8 chars, uppercase, lowercase, number">
        <small>Min 8 characters with uppercase, lowercase, and number</small>
      </div>
      <div class="form-group">
        <label for="password_confirm">Confirm Password</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
    </form>
    <?php elseif (! $success): ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required autocomplete="email">
      </div>
      <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
    </form>
    <?php endif; ?>
    <div class="form-footer"><a href="<?= url('login') ?>">Back to Login</a></div>
  </div>
</div>

<?= view('layout/footer') ?>
