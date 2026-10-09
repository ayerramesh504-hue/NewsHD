<?php
$meta = $meta ?? page_meta(get_setting('site_name', APP_NAME));
$activeNav = $activeNav ?? '';
$user = current_user();
$categories = get_categories();
$siteName = get_setting('site_name', APP_NAME);
$tagline = get_setting('site_tagline', '');
$breaking = get_breaking_news();
$extraScripts = $extraScripts ?? [];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($meta['title']) ?></title>
  <meta name="description" content="<?= e($meta['description']) ?>">
  <meta name="keywords" content="<?= e(get_setting('meta_keywords', 'news')) ?>">
  <meta property="og:title" content="<?= e($meta['title']) ?>">
  <meta property="og:description" content="<?= e($meta['description']) ?>">
  <meta property="og:image" content="<?= e($meta['image']) ?>">
  <meta property="og:url" content="<?= e($meta['url']) ?>">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
  <?php if (! empty($extraCss)): foreach ((array) $extraCss as $css): ?>
  <link rel="stylesheet" href="<?= e($css) ?>">
  <?php endforeach; endif; ?>
</head>
<body>
  <a href="#main-content" class="skip-link">Skip to content</a>

  <!-- Top Bar -->
  <div class="top-bar">
    <div class="container top-bar-inner">
      <div class="date-box">
        <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
        <span><?= date('l, F j, Y') ?></span>
      </div>
      <div class="top-actions">
        <div class="social-icons">
          <?php if ($fb = get_setting('facebook_url')): ?><a href="<?= e($fb) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
          <?php if ($yt = get_setting('youtube_url')): ?><a href="<?= e($yt) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a><?php endif; ?>
          <?php if ($tw = get_setting('twitter_url')): ?><a href="<?= e($tw) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
        </div>
        <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
          <i class="fa-solid fa-sun icon-light"></i>
          <i class="fa-solid fa-moon icon-dark"></i>
        </button>
        <?php if ($user): ?>
          <a href="<?= url('profile') ?>" class="auth-link"><i class="fa-solid fa-user"></i> <?= e($user['name']) ?></a>
          <?php if (is_admin()): ?>
            <a href="<?= url('admin') ?>" class="auth-link"><i class="fa-solid fa-gauge"></i> Dashboard</a>
          <?php elseif (is_author()): ?>
            <a href="<?= url('author') ?>" class="auth-link"><i class="fa-solid fa-newspaper"></i> Dashboard</a>
          <?php endif; ?>
          <a href="<?= url('logout') ?>" class="auth-link auth-link-primary">Logout</a>
        <?php else: ?>
          <a href="<?= url('login') ?>" class="auth-link">Login</a>
          <a href="<?= url('register') ?>" class="auth-link auth-link-primary">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Logo -->
  <header class="logo-section">
    <a href="<?= url('/') ?>" class="logo-text" aria-label="<?= e($siteName) ?> home">
      <span class="logo-text-brand"><?= e($siteName) ?></span>
    </a>
    <?php if ($tagline): ?><p class="tagline"><?= e($tagline) ?></p><?php endif; ?>
  </header>

  <!-- Main Navigation -->
  <nav class="main-nav" aria-label="Main navigation">
    <div class="container nav-container">
      <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
        <span class="bar bar-top"></span>
        <span class="bar bar-bottom"></span>
      </button>
      <a href="<?= url('/') ?>" class="nav-brand-mobile" aria-label="<?= e($siteName) ?> home"><?= e($siteName) ?></a>
      <button type="button" class="nav-search-btn" id="navSearchBtn" aria-label="Open search">
        <i class="fa-solid fa-magnifying-glass"></i>
      </button>
      <ul class="menu" id="mainMenu">
        <li><a href="<?= url('/') ?>" class="<?= $activeNav === 'home' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Home</a></li>
        <li><a href="<?= url('nepse') ?>" class="<?= $activeNav === 'nepse' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> NEPSE</a></li>
        <?php foreach ($categories as $cat): ?>
          <li><a href="<?= url('category/' . e($cat['slug'])) ?>" class="<?= $activeNav === $cat['slug'] ? 'active' : '' ?>"><i class="fa-solid <?= category_icon($cat['slug']) ?>"></i> <?= e($cat['name']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?= url('contact') ?>" class="<?= $activeNav === 'contact' ? 'active' : '' ?>"><i class="fa-solid fa-envelope"></i> Contact</a></li>
      </ul>
      <div class="search-wrapper" id="liveSearchRoot" data-api="<?= api_url('search') ?>"></div>
    </div>
  </nav>

  <!-- Mobile Drawer -->
  <div class="mobile-drawer-overlay" id="mobileOverlay"></div>
  <aside class="mobile-drawer" id="mobileDrawer" aria-hidden="true">
    <div class="mobile-drawer-head">
      <span class="mobile-drawer-logo"><?= e($siteName) ?></span>
      <button type="button" class="mobile-drawer-close" id="mobileClose" aria-label="Close menu">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <nav class="mobile-drawer-nav" aria-label="Mobile navigation">
      <a href="<?= url('/') ?>" class="<?= $activeNav === 'home' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Home</a>
      <a href="<?= url('nepse') ?>" class="<?= $activeNav === 'nepse' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> NEPSE</a>
      <?php foreach ($categories as $cat): ?>
        <a href="<?= url('category/' . e($cat['slug'])) ?>" class="<?= $activeNav === $cat['slug'] ? 'active' : '' ?>"><i class="fa-solid <?= category_icon($cat['slug']) ?>"></i> <?= e($cat['name']) ?></a>
      <?php endforeach; ?>
      <a href="<?= url('contact') ?>" class="<?= $activeNav === 'contact' ? 'active' : '' ?>"><i class="fa-solid fa-envelope"></i> Contact</a>
    </nav>
    <div class="mobile-drawer-foot">
      <?php if ($user): ?>
        <a href="<?= url('profile') ?>" class="mobile-drawer-btn"><i class="fa-solid fa-user"></i> <?= e($user['name']) ?></a>
        <?php if (is_admin()): ?>
          <a href="<?= url('admin') ?>" class="mobile-drawer-btn"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <?php elseif (is_author()): ?>
          <a href="<?= url('author') ?>" class="mobile-drawer-btn"><i class="fa-solid fa-newspaper"></i> Dashboard</a>
        <?php endif; ?>
        <a href="<?= url('logout') ?>" class="mobile-drawer-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      <?php else: ?>
        <a href="<?= url('login') ?>" class="mobile-drawer-btn"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
        <a href="<?= url('register') ?>" class="mobile-drawer-btn mobile-drawer-btn-primary"><i class="fa-solid fa-user-plus"></i> Register</a>
      <?php endif; ?>
      <div class="mobile-drawer-social">
        <?php if ($fb = get_setting('facebook_url')): ?><a href="<?= e($fb) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
        <?php if ($yt = get_setting('youtube_url')): ?><a href="<?= e($yt) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a><?php endif; ?>
        <?php if ($tw = get_setting('twitter_url')): ?><a href="<?= e($tw) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
      </div>
    </div>
  </aside>

  <!-- Breaking Ticker -->
  <?php if (! empty($breaking)): ?>
  <div class="breaking-ticker" role="note" aria-label="Breaking news">
    <div class="breaking-label"><i class="fa-solid fa-bolt"></i> Breaking</div>
    <div class="breaking-items">
      <div class="breaking-track" id="breakingTrack">
        <?php foreach ($breaking as $item): ?>
        <a href="<?= url('article/' . e($item['slug'])) ?>"><?= e($item['title']) ?></a>
        <?php endforeach; ?>
        <?php foreach ($breaking as $item): ?>
        <a href="<?= url('article/' . e($item['slug'])) ?>"><?= e($item['title']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <main id="main-content">
