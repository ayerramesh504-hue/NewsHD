<?= view('layout/header') ?>

<!-- Hero -->
<section class="home-hero">
  <div class="container hero-grid">
    <div class="hero-main">
      <div class="hero-slider" aria-label="Top stories">
        <?php foreach ($breaking as $i => $slide): ?>
        <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>"
             style="background-image:url('<?= e($slide['cover_image']) ?>')">
          <div class="hero-slide-content">
            <?php if ($slide['is_breaking']): ?><span class="hero-badge"><i class="fa-solid fa-bolt"></i> Breaking</span><?php endif; ?>
            <a href="<?= url('category/' . e($slide['category_slug'])) ?>" class="category-badge"><?= e($slide['category_name']) ?></a>
            <h2><a href="<?= url('article/' . e($slide['slug'])) ?>"><?= e($slide['title']) ?></a></h2>
            <p><?= e(truncate(strip_tags($slide['excerpt'] ?? ''), 170)) ?></p>
            <div class="hero-slide-meta">
              <span><i class="fa-solid fa-user"></i> <?= e($slide['author_name']) ?></span>
              <span><i class="fa-solid fa-clock"></i> <?= format_date($slide['published_at']) ?></span>
              <span><i class="fa-solid fa-eye"></i> <?= number_format($slide['view_count'] + 1) ?> views</span>
            </div>
            <a href="<?= url('article/' . e($slide['slug'])) ?>" class="btn btn-primary">Read Full Story <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
        <?php endforeach; ?>
        <div class="hero-dots" id="heroDots">
          <?php foreach ($breaking as $i => $slide): ?>
          <button class="hero-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <aside class="hero-side">
      <div class="hero-side-head">
        <span class="hero-side-kicker">Editor's Picks</span>
      </div>
      <?php foreach ($heroSide as $side): ?>
      <a class="hero-side-item" href="<?= url('article/' . e($side['slug'])) ?>">
        <div class="side-thumb">
          <img src="<?= e($side['cover_image']) ?>" alt="<?= e($side['title']) ?>" loading="lazy">
          <span class="side-cat"><?= e($side['category_name']) ?></span>
        </div>
        <div class="side-body">
          <h3><?= e($side['title']) ?></h3>
          <div class="meta">
            <span><i class="fa-solid fa-user"></i> <?= e($side['author_name']) ?></span>
            <span><i class="fa-solid fa-clock"></i> <?= format_date($side['published_at']) ?></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </aside>
  </div>
</section>

<!-- Category strip -->
<?php if (! empty($categoriesWidget)): ?>
<section class="home-cats-bar">
  <div class="container">
    <div class="cats-bar">
      <span class="cats-bar-label"><i class="fa-solid fa-layer-group"></i> Explore</span>
      <div class="cats-bar-list">
        <?php foreach ($categoriesWidget as $cat): ?>
        <a class="cats-chip" href="<?= url('category/' . e($cat['slug'])) ?>">
          <span class="cats-chip-name"><?= e($cat['name']) ?></span>
          <span class="cats-chip-count"><?= (int) $cat['count'] ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Main + Sidebar -->
<div class="container home-layout">
  <main class="home-main">
    <div class="section-header">
      <h2><i class="fa-solid fa-newspaper"></i> Latest News</h2>
      <a href="<?= url('search') ?>" class="view-all">View All <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <?php if (empty($articles)): ?>
    <div class="empty-state">
      <i class="fa-regular fa-newspaper"></i>
      <h2>No articles yet</h2>
      <p>Check back soon for the latest stories.</p>
    </div>
    <?php else: ?>
    <div class="news-grid" id="newsGrid" data-next-page="<?= (int) $nextPage ?>" data-has-more="<?= $hasMore ? '1' : '0' ?>">
      <?php foreach ($articles as $article): ?>
      <?= view('site/partials/article_card', ['article' => $article]) ?>
      <?php endforeach; ?>
    </div>
    <div id="loadMoreObserver" class="load-more-sentinel" aria-hidden="true"></div>
    <?php endif; ?>
  </main>

  <aside class="sidebar home-side">
    <div class="sidebar-widget">
      <h3><i class="fa-solid fa-fire"></i> Most Read</h3>
      <?php if (empty($trending)): ?>
      <p class="empty-state" style="padding:12px 0;">No trending articles yet.</p>
      <?php else: foreach ($trending as $i => $item): ?>
      <div class="trending-item">
        <span class="trending-num"><?= $i + 1 ?></span>
        <div>
          <a href="<?= url('article/' . e($item['slug'])) ?>"><?= e($item['title']) ?></a>
          <small><?= e($item['category_name']) ?> &middot; <?= number_format($item['view_count']) ?> views</small>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="sidebar-widget">
      <h3><i class="fa-solid fa-tags"></i> Categories</h3>
      <?php if (empty($categoriesWidget)): ?>
      <p class="empty-state" style="padding:12px 0;">No categories yet.</p>
      <?php else: foreach ($categoriesWidget as $cat): ?>
      <a class="cats-row" href="<?= url('category/' . e($cat['slug'])) ?>">
        <span class="cats-row-name"><?= e($cat['name']) ?></span>
        <span class="cats-row-count"><?= (int) $cat['count'] ?></span>
      </a>
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

<?= view('layout/footer') ?>
