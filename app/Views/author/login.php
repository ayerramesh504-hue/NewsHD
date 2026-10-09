<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Author Login | <?= e($siteName ?? APP_NAME) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin-login-page">
  <div class="login-card">
    <div class="login-brand">
      <div class="brand-icon"><i class="fa-solid fa-pen-nib"></i></div>
      <h1><?= e($siteName ?? APP_NAME) ?> Author</h1>
      <p>Sign in to manage your articles</p>
    </div>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= e($email) ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div class="form-group">
        <label class="form-check">
          <input type="checkbox" name="remember" value="1"> Remember me
        </label>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Sign In</button>
    </form>
    <a href="<?= e($siteUrl) ?>" class="back-link">&larr; Back to site</a>
  </div>
  <script src="<?= asset('js/theme.js') ?>"></script>
</body>
</html>
