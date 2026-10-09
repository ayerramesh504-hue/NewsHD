<?php
$user = current_user();
$siteName = get_setting('site_name', APP_NAME);
$active = $active ?? 'dashboard';
$title = $title ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?> | <?= e($siteName) ?> Author</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Merriweather:wght@400;700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
  <?php if (! empty($extraCss)): foreach ((array) $extraCss as $css): ?>
  <link rel="stylesheet" href="<?= e($css) ?>">
  <?php endforeach; endif; ?>
</head>
<body>
<div class="admin-wrapper">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-logo">
      <i class="fa-solid fa-newspaper"></i> <?= e($siteName) ?>
      <span>Author</span>
    </div>
    <div class="admin-user-card">
      <div class="admin-avatar"><i class="fa-solid fa-user"></i></div>
      <div>
        <strong><?= e($user['name']) ?></strong>
        <small><span class="role-badge role-<?= e($user['role_slug']) ?>"><?= e($user['role_name']) ?></span></small>
      </div>
    </div>
    <nav class="admin-nav">
      <a href="<?= url('author') ?>" class="<?= $active === 'dashboard' ? 'active' : '' ?>">
        <i class="fa-solid fa-gauge"></i> Dashboard</a>
      <a href="<?= url('author/articles') ?>" class="<?= $active === 'articles' ? 'active' : '' ?>">
        <i class="fa-solid fa-newspaper"></i> My Articles</a>
    </nav>
    <div class="admin-nav admin-nav-bottom">
      <a href="<?= url('/') ?>"><i class="fa-solid fa-globe"></i> View Site</a>
      <a href="<?= url('logout') ?>"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-header">
      <div class="admin-header-left">
        <button class="mobile-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h2><?= e($title) ?></h2>
      </div>
      <div class="admin-header-actions">
        <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
          <i class="fa-solid fa-sun icon-light"></i>
          <i class="fa-solid fa-moon icon-dark"></i>
        </button>
      </div>
    </header>

    <?php if (session('error')): ?>
    <div class="admin-content"><div class="alert alert-error"><?= e(session('error')) ?></div></div>
    <?php endif; ?>
    <?php if (session('success')): ?>
    <div class="admin-content"><div class="alert alert-success"><?= e(session('success')) ?></div></div>
    <?php endif; ?>

    <div class="admin-content">
      <?= $content ?>
    </div>
  </div>
</div>

<script>window.APP_URL = <?= json_encode(base_url()) ?>; window.CSRF_TOKEN = <?= json_encode(csrf_hash()) ?>;</script>
<script src="<?= asset('js/theme.js') ?>"></script>
<script src="<?= asset('js/admin/layout.js') ?>"></script>
<?php if (! empty($extraScripts)): foreach ((array) $extraScripts as $script): ?>
  <?php $scriptSrc = filter_var($script, FILTER_VALIDATE_URL) ? $script : asset('js/admin/' . $script); ?>
  <script src="<?= e($scriptSrc) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
