<?= view('layout/header') ?>

<article class="article-page">
  <div class="container article-grid">
    <div class="article-main">
      <div class="article-hero" style="background-image:url('<?= e($article['cover_image']) ?>')">
        <div class="article-hero-content">
          <a href="<?= url('category/' . e($article['category_slug'])) ?>" class="category-badge"><?= e($article['category_name']) ?></a>
          <h1><?= e($article['title']) ?></h1>
          <div class="meta">
            <span class="author"><i class="fa-solid fa-user"></i> <?= e($article['author_name']) ?></span>
            <span><i class="fa-solid fa-calendar"></i> <?= format_date($article['published_at'], 'F j, Y') ?></span>
            <span><i class="fa-solid fa-eye"></i> <?= number_format($article['view_count'] + 1) ?> views</span>
          </div>
        </div>
      </div>

      <div class="article-content">
        <?= $article['content'] ?>

        <?php if ($tags): ?>
        <div class="article-tags">
          <?php foreach ($tags as $tag): ?>
          <a href="<?= url('search?q=' . urlencode($tag['name'])) ?>" class="tag-link">#<?= e($tag['name']) ?></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="share-buttons">
          <span>Share:</span>
          <?php $shareUrl = urlencode(url('article/' . $article['slug'])); ?>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener" class="share-btn facebook" aria-label="Share on Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= urlencode($article['title']) ?>" target="_blank" rel="noopener" class="share-btn twitter" aria-label="Share on X"><i class="fab fa-x-twitter"></i></a>
          <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>" target="_blank" rel="noopener" class="share-btn linkedin" aria-label="Share on LinkedIn"><i class="fab fa-linkedin-in"></i></a>
          <?php if ($user): ?>
          <button type="button" class="bookmark-btn <?= $isBookmarked ? 'active' : '' ?>" id="bookmarkBtn"
                  data-article-id="<?= (int) $article['id'] ?>"
                  data-bookmarked="<?= $isBookmarked ? '1' : '0' ?>">
            <i class="fa-<?= $isBookmarked ? 'solid' : 'regular' ?> fa-bookmark"></i>
            <?= $isBookmarked ? 'Saved' : 'Save Article' ?>
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <aside class="sidebar">
      <div class="sidebar-widget">
        <h3><i class="fa-solid fa-fire"></i> Most Read</h3>
        <?php if (! empty($trending)): foreach ($trending as $i => $item): ?>
        <div class="trending-item">
          <span class="trending-num"><?= $i + 1 ?></span>
          <div>
            <a href="<?= url('article/' . e($item['slug'])) ?>"><?= e($item['title']) ?></a>
            <small><?= number_format($item['view_count']) ?> views</small>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>

      <div class="sidebar-widget newsletter-widget">
        <h3><i class="fa-solid fa-envelope"></i> Newsletter</h3>
        <p class="mb-2">Get the day's top stories in your inbox.</p>
        <form class="newsletter-form" id="newsletterForm" action="<?= api_url('newsletter') ?>" method="post">
          <?= csrf_field() ?>
          <input type="email" name="email" placeholder="Your email" required aria-label="Email for newsletter">
          <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
      </div>
    </aside>
  </div>
</article>

<?php if ($related): ?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <h2><i class="fa-solid fa-link"></i> Related Articles</h2>
    </div>
    <div class="news-grid">
      <?php foreach ($related as $rel): ?>
      <article class="news-card">
        <a href="<?= url('article/' . e($rel['slug'])) ?>" class="thumb">
          <img src="<?= e($rel['cover_image']) ?>" alt="<?= e($rel['title']) ?>" loading="lazy">
        </a>
        <div class="news-card-body">
          <h3><a href="<?= url('article/' . e($rel['slug'])) ?>"><?= e($rel['title']) ?></a></h3>
          <div class="meta"><span><i class="fa-solid fa-clock"></i> <?= format_date($rel['published_at']) ?></span></div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?= view('layout/footer') ?>

<script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js" defer></script>
<script>
  window.addEventListener('load', function () {
    if (window.katex) {
      document.querySelectorAll('.ql-formula').forEach(function (el) {
        var src = el.getAttribute('data-value') || el.textContent;
        try {
          window.katex.render(src, el, { throwOnError: false, displayMode: el.classList.contains('ql-formula-display') });
        } catch (e) { /* leave as-is */ }
      });
    }
  });
</script>
