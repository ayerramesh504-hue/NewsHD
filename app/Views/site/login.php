<?= view('layout/header') ?>

<div class="form-page">
  <div class="form-card">
    <h1>Welcome Back</h1>
    <p class="form-subtitle">Sign in to your account</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autocomplete="email"
               value="<?= e($email) ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </div>
      <div class="form-group">
        <label class="form-check">
          <input type="checkbox" name="remember" value="1"> Remember me
        </label>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Sign In</button>
    </form>
    <div class="form-footer">
      <p><a href="<?= url('forgot-password') ?>">Forgot password?</a></p>
      <p style="margin-top:6px;">Don't have an account? <a href="<?= url('register') ?>">Register</a></p>
    </div>
  </div>
</div>

<?= view('layout/footer') ?>
