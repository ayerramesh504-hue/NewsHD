  </main>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col footer-brand">
          <a class="footer-logo" href="<?= url('/') ?>" aria-label="<?= e(get_setting('site_name', APP_NAME)) ?> home">
            <span class="footer-logo-mark"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span>
            <?= e(get_setting('site_name', APP_NAME)) ?>
          </a>
          <p><?= e(get_setting('site_description', 'Trusted digital news portal.')) ?></p>
          <div class="footer-social">
            <?php if ($fb = get_setting('facebook_url')): ?><a href="<?= e($fb) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
            <?php if ($yt = get_setting('youtube_url')): ?><a href="<?= e($yt) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a><?php endif; ?>
            <?php if ($tw = get_setting('twitter_url')): ?><a href="<?= e($tw) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
          </div>
        </div>
        <nav class="footer-col" aria-label="Quick links">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="<?= url('/') ?>">Home</a></li>
            <li><a href="<?= url('search') ?>">Search</a></li>
            <li><a href="<?= url('contact') ?>">Contact Us</a></li>
            <?php if (is_logged_in()): ?>
              <li><a href="<?= url('profile') ?>">My Account</a></li>
            <?php else: ?>
              <li><a href="<?= url('login') ?>">Login</a></li>
              <li><a href="<?= url('register') ?>">Register</a></li>
            <?php endif; ?>
            <?php if (is_admin()): ?>
              <li><a href="<?= url('admin') ?>">Admin Dashboard</a></li>
            <?php endif; ?>
          </ul>
        </nav>
        <nav class="footer-col" aria-label="Categories">
          <h4>Categories</h4>
          <ul>
            <?php foreach (array_slice(get_categories(), 0, 6) as $cat): ?>
              <li><a href="<?= url('category/' . e($cat['slug'])) ?>"><?= e($cat['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </nav>
        <div class="footer-col footer-newsletter">
          <h4>Newsletter</h4>
          <p>Subscribe for daily headlines straight to your inbox.</p>
          <form class="newsletter-form" id="newsletterForm" action="<?= api_url('newsletter') ?>" method="post">
            <?= csrf_field() ?>
            <input type="email" name="email" placeholder="Your email" required aria-label="Email for newsletter">
            <button type="submit"><span>Subscribe</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
          </form>
          <div class="contact-info">
            <?php if ($email = get_setting('contact_email')): ?><div><i class="fa-solid fa-envelope"></i><?= e($email) ?></div><?php endif; ?>
            <?php if ($phone = get_setting('contact_phone')): ?><div><i class="fa-solid fa-phone"></i><?= e($phone) ?></div><?php endif; ?>
            <?php if ($address = get_setting('contact_address')): ?><div><i class="fa-solid fa-location-dot"></i><?= e($address) ?></div><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-bottom-wrap">
      <div class="container footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= e(get_setting('site_name', APP_NAME)) ?>. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Search Overlay -->
  <div class="search-overlay" id="searchOverlay" aria-hidden="true">
    <div class="search-overlay-backdrop" data-search-close></div>
    <button type="button" class="search-overlay-close" data-search-close aria-label="Close search">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <div class="search-overlay-panel" role="dialog" aria-modal="true" aria-label="Search">
      <div class="search-overlay-body">
        <div class="search-wrapper search-overlay-search" id="liveSearchOverlayRoot" data-api="<?= e(api_url('search')) ?>"></div>
      </div>
    </div>
  </div>

  <script>window.APP_URL = <?= json_encode(base_url()) ?>; window.CSRF_TOKEN = <?= json_encode(csrf_hash()) ?>;</script>
  <script src="<?= asset('js/theme.js') ?>"></script>
  <script src="<?= asset('js/main.js') ?>"></script>
  <script src="<?= asset('js/components/live-search.js') ?>"></script>
  <?php if (! empty($extraScripts)): foreach ($extraScripts as $script): ?>
    <script src="<?= asset('js/' . $script) ?>"></script>
  <?php endforeach; endif; ?>
</body>
</html>
