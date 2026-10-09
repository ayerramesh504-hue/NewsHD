  </main>

  <footer class="site-footer">
    <div class="footer-grid">
      <div class="footer-col">
        <h3><?= e(get_setting('site_name', APP_NAME)) ?></h3>
        <p><?= e(get_setting('site_description', 'Trusted digital news portal.')) ?></p>
        <div class="social-icons">
          <?php if ($fb = get_setting('facebook_url')): ?><a href="<?= e($fb) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
          <?php if ($yt = get_setting('youtube_url')): ?><a href="<?= e($yt) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a><?php endif; ?>
          <?php if ($tw = get_setting('twitter_url')): ?><a href="<?= e($tw) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a><?php endif; ?>
        </div>
      </div>
      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="<?= url('') ?>">Home</a></li>
          <li><a href="<?= url('search.php') ?>">Search</a></li>
          <li><a href="<?= url('contact.php') ?>">Contact Us</a></li>
          <?php if (!is_logged_in()): ?>
            <li><a href="<?= url('login.php') ?>">Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Categories</h4>
        <ul>
          <?php foreach (array_slice(get_categories(), 0, 6) as $cat): ?>
            <li><a href="<?= url('category.php?slug=' . e($cat['slug'])) ?>"><?= e($cat['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Newsletter</h4>
        <p>Subscribe for daily headlines.</p>
        <form class="newsletter-form" id="newsletterForm" action="<?= api_url('newsletter.php') ?>" method="post">
          <?= csrf_field() ?>
          <input type="email" name="email" placeholder="Your email" required aria-label="Email for newsletter">
          <button type="submit">Subscribe</button>
        </form>
        <p class="contact-info">
          <i class="fa-solid fa-envelope"></i> <?= e(get_setting('contact_email', '')) ?><br>
          <i class="fa-solid fa-phone"></i> <?= e(get_setting('contact_phone', '')) ?>
        </p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> <?= e(get_setting('site_name', APP_NAME)) ?>. All Rights Reserved.</p>
    </div>
  </footer>

  <script>window.APP_URL = <?= json_encode(APP_URL) ?>; window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;</script>
  <script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin></script>
  <script src="<?= asset('js/theme.js') ?>"></script>
  <script src="<?= asset('js/main.js') ?>"></script>
  <script src="<?= asset('js/components/live-search.js') ?>"></script>
  <?php if (!empty($extraScripts)): foreach ($extraScripts as $script): ?>
    <script src="<?= asset('js/' . $script) ?>"></script>
  <?php endforeach; endif; ?>
</body>
</html>
