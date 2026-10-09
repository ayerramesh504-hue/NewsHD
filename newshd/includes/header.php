<?php
/** @var array $meta */
/** @var string $activeNav */
$meta = $meta ?? page_meta(get_setting('site_name', APP_NAME));
$user = current_user();
$categories = get_categories();
$siteName = get_setting('site_name', APP_NAME);
$tagline = get_setting('site_tagline', 'देशको खबर, जनताको आवाज');
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body>
  <a href="#main-content" class="skip-link">Skip to content</a>

  <div class="top-bar">
    <div class="date-box">
      <div><i class="fa-solid fa-calendar-days" aria-hidden="true"></i><?= date('l, F j, Y') ?></div>
    </div>
    <div class="top-actions">
      <div class="social-icons">
        <?php if ($fb = get_setting('facebook_url')): ?><a href="<?= e($fb) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?> 
        <?php if ($yt = get_setting('youtube_url')): ?><a href="<?= e($yt) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a><?php endif; ?>
        <?php if ($tw = get_setting('twitter_url')): ?><a href="<?= e($tw) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a><?php endif; ?>
      </div>
      <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
        <i class="fa-solid fa-sun icon-light"></i>
        <i class="fa-solid fa-moon icon-dark"></i>
      </button>
      <?php if ($user): ?>
        <a href="<?= url('profile.php') ?>" class="auth-link"><i class="fa-solid fa-user"></i> <?= e($user['name']) ?></a>
        <a href="<?= url('logout.php') ?>" class="auth-link">Logout</a>
      <?php else: ?>
        <a href="<?= url('login.php') ?>" class="auth-link">Login</a>
        <a href="<?= url('register.php') ?>" class="auth-link auth-link-primary">Register</a>
      <?php endif; ?>
    </div>
  </div>

  <section class="logo-section">
    <!-- <a href="<?= url('') ?>">
      <img src="<?= asset('images/news-logo.jpg') ?>" alt="<?= e($siteName) ?> Logo" class="logo-img" onerror="this.src='<?= asset('images/logo-placeholder.svg') ?>'">
    </a> -->
    <div class="tagline"><?= e($tagline) ?></div>
  </section>

  <nav class="main-nav" aria-label="Main navigation">
    <div class="nav-container">
      <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
        <i class="fa-solid fa-bars"></i>
      </button>
      <ul class="menu" id="mainMenu">
        <li><a href="<?= url('') ?>" class="<?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>">Home</a></li>
        <?php foreach ($categories as $cat): ?>
          <li><a href="<?= url('category.php?slug=' . e($cat['slug'])) ?>" class="<?= ($activeNav ?? '') === $cat['slug'] ? 'active' : '' ?>"><?= e($cat['name']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?= url('contact.php') ?>" class="<?= ($activeNav ?? '') === 'contact' ? 'active' : '' ?>">Contact</a></li>
      </ul>
      <div class="search-wrapper" id="liveSearchRoot" data-api="<?= api_url('search.php') ?>"></div>
    </div>
  </nav>

  <main id="main-content">
