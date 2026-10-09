<?= view('layout/header') ?>

<div class="form-page">
  <div class="form-card">
    <h1>Create Account</h1>
    <p class="form-subtitle">Join <?= e(get_setting('site_name', APP_NAME)) ?> today</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if (! $success): ?>
    <form method="post" action="" id="registerForm">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required minlength="2" value="<?= e($name) ?>">
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= e($email) ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8"
               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
               title="Min 8 chars, uppercase, lowercase, number">
        <small>Min 8 characters with uppercase, lowercase, and number</small>
      </div>
      <div class="form-group">
        <label for="password_confirm">Confirm Password</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Create Account</button>
    </form>
    <?php endif; ?>
    <div class="form-footer">
      <p>Already have an account? <a href="<?= url('login') ?>">Login</a></p>
    </div>
  </div>
</div>

<?= view('layout/footer') ?>
