<?= view('layout/header') ?>

<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="<?= url('/') ?>">Home</a> &raquo; <span><?= e($category['name']) ?></span></div>
    <h1><?= e($category['name']) ?></h1>
    <?php if ($category['description']): ?>
    <p><?= e($category['description']) ?></p>
    <?php endif; ?>
  </div>
</div>

<section class="section">
  <div class="container">
    <?php if (empty($articles)): ?>
    <div class="empty-state">
      <i class="fa-regular fa-folder-open"></i>
      <h2>No articles in this category yet</h2>
      <p><a href="<?= url('/') ?>">Back to home</a></p>
    </div>
    <?php else: ?>
    <div class="list-rows">
      <?php foreach ($articles as $article): ?>
      <article class="row-card">
        <a href="<?= url('article/' . e($article['slug'])) ?>" class="thumb">
          <img src="<?= e($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" loading="lazy">
        </a>
        <div class="news-card-body">
          <h3><a href="<?= url('article/' . e($article['slug'])) ?>"><?= e($article['title']) ?></a></h3>
          <div class="meta">
            <span class="author"><i class="fa-solid fa-user"></i> <?= e($article['author_name']) ?></span>
            <span><i class="fa-solid fa-clock"></i> <?= format_date($article['published_at']) ?></span>
          </div>
          <p><?= e(truncate(strip_tags($article['excerpt'] ?? ''), 140)) ?></p>
          <a href="<?= url('article/' . e($article['slug'])) ?>" class="read-more">Read More <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?= view('layout/footer') ?>
